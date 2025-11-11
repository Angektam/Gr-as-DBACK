<?php
/**
 * Clase para manejar la auto-asignación de grúas
 * Sistema inteligente de asignación basado en ubicación, tipo de servicio y disponibilidad
 */

class AutoAsignacionGruas {
    private $conn;
    private $configuracion;
    
    public function __construct($conexion) {
        $this->conn = $conexion;
        $this->cargarConfiguracion();
    }
    
    /**
     * Cargar configuración desde la base de datos
     */
    private function cargarConfiguracion() {
        $query = "SELECT parametro, valor FROM configuracion_auto_asignacion WHERE activo = 1";
        $result = $this->conn->query($query);
        
        $this->configuracion = [];
        while ($row = $result->fetch_assoc()) {
            $this->configuracion[$row['parametro']] = $row['valor'];
        }
    }
    
    /**
     * Verificar si la auto-asignación está habilitada
     */
    public function estaHabilitada() {
        return isset($this->configuracion['auto_asignacion_habilitada']) && 
               $this->configuracion['auto_asignacion_habilitada'] == '1';
    }
    
    /**
     * Verificar si el tipo de servicio requiere equipo de ayuda en lugar de grúa
     */
    private function requiereEquipoAyuda($tipo_servicio) {
        $servicios_equipo_ayuda = ['gasolina', 'pila', 'bateria'];
        return in_array(strtolower($tipo_servicio), $servicios_equipo_ayuda);
    }
    
    /**
     * Asignar grúa o equipo de ayuda automáticamente a una solicitud
     */
    public function asignarGrua($solicitud_id) {
        if (!$this->estaHabilitada()) {
            $this->enviarNotificacionUsuario($solicitud_id, 'Sistema de auto-asignación deshabilitado', 'warning');
            return ['success' => false, 'message' => 'Auto-asignación deshabilitada', 'notificacion' => 'Sistema deshabilitado'];
        }
        
        // Forzar ignorar clima (modo siempre activo)
        // $clima_apto = $this->verificarCondicionesClimaticas();
        // if (!$clima_apto['apto']) { ... }
        
        $inicio_tiempo = microtime(true);
        
        // Obtener datos de la solicitud
        $solicitud = $this->obtenerSolicitud($solicitud_id);
        if (!$solicitud) {
            $this->enviarNotificacionUsuario($solicitud_id, 'Solicitud no encontrada en el sistema', 'error');
            return ['success' => false, 'message' => 'Solicitud no encontrada', 'notificacion' => 'Solicitud no encontrada'];
        }
        
        // Verificar si requiere equipo de ayuda
        if ($this->requiereEquipoAyuda($solicitud['tipo_servicio'])) {
            return $this->asignarEquipoAyuda($solicitud_id, $solicitud);
        }
        
        // Buscar grúas disponibles
        $gruas_disponibles = $this->buscarGruasDisponibles($solicitud);
        
        if (empty($gruas_disponibles)) {
            // Notificar que no hay grúas disponibles
            $this->enviarNotificacionUsuario($solicitud_id, 'No hay grúas disponibles en este momento. Su solicitud quedará en espera.', 'warning');
            $this->registrarEventoSistema($solicitud_id, 'sin_gruas', 'No hay grúas disponibles');
            $this->notificarAdministradores('No hay grúas disponibles', "Solicitud #$solicitud_id sin grúas disponibles");
            
            return [
                'success' => false, 
                'message' => 'No hay grúas disponibles en este momento',
                'notificacion' => 'Sin grúas disponibles',
                'accion_sugerida' => 'Su solicitud quedará en espera hasta que haya una grúa disponible'
            ];
        }
        
        // Seleccionar la mejor grúa
        $mejor_grua = $this->seleccionarMejorGrua($solicitud, $gruas_disponibles);
        
        if (!$mejor_grua) {
            $this->enviarNotificacionUsuario($solicitud_id, 'No se pudo encontrar una grúa apropiada para su solicitud', 'warning');
            return ['success' => false, 'message' => 'No se pudo seleccionar una grúa apropiada', 'notificacion' => 'Sin grúa apropiada'];
        }
        
        // Realizar la asignación
        $resultado = $this->realizarAsignacion($solicitud_id, $mejor_grua['ID']);
        
        $tiempo_asignacion = round((microtime(true) - $inicio_tiempo) * 1000);
        
        if ($resultado['success']) {
            // Registrar en historial
            $this->registrarHistorial($solicitud_id, $mejor_grua['ID'], 'automatica', $mejor_grua['criterios'], $mejor_grua['distancia'], $tiempo_asignacion);
            
            // Notificar al usuario de la asignación exitosa
            $this->enviarNotificacionUsuario($solicitud_id, 
                "¡Grúa asignada exitosamente! Placa: {$mejor_grua['Placa']}, Distancia aproximada: " . round($mejor_grua['distancia'] ?? 0, 2) . " km", 
                'success');
            
            return [
                'success' => true, 
                'message' => 'Grúa asignada automáticamente',
                'grua' => $mejor_grua,
                'tiempo_asignacion_ms' => $tiempo_asignacion,
                'notificacion' => 'Grúa asignada exitosamente'
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Asignar equipo de ayuda para servicios de gasolina o pila
     */
    private function asignarEquipoAyuda($solicitud_id, $solicitud) {
        // Buscar equipos de ayuda disponibles
        $equipos_disponibles = $this->buscarEquiposAyudaDisponibles($solicitud);
        
        if (empty($equipos_disponibles)) {
            $this->enviarNotificacionUsuario($solicitud_id, 'No hay equipos de ayuda disponibles en este momento. Su solicitud quedará en espera.', 'warning');
            $this->registrarEventoSistema($solicitud_id, 'sin_equipos_ayuda', 'No hay equipos de ayuda disponibles');
            $this->notificarAdministradores('No hay equipos de ayuda disponibles', "Solicitud #$solicitud_id sin equipos de ayuda disponibles");
            
            return [
                'success' => false, 
                'message' => 'No hay equipos de ayuda disponibles en este momento',
                'notificacion' => 'Sin equipos de ayuda disponibles',
                'accion_sugerida' => 'Su solicitud quedará en espera hasta que haya un equipo de ayuda disponible'
            ];
        }
        
        // Seleccionar el mejor equipo de ayuda
        $mejor_equipo = $this->seleccionarMejorEquipoAyuda($solicitud, $equipos_disponibles);
        
        if (!$mejor_equipo) {
            $this->enviarNotificacionUsuario($solicitud_id, 'No se pudo encontrar un equipo de ayuda apropiado para su solicitud', 'warning');
            return ['success' => false, 'message' => 'No se pudo seleccionar un equipo de ayuda apropiado', 'notificacion' => 'Sin equipo apropiado'];
        }
        
        // Realizar la asignación del equipo de ayuda
        $resultado = $this->realizarAsignacionEquipoAyuda($solicitud_id, $mejor_equipo['ID']);
        
        if ($resultado['success']) {
            // Registrar en historial
            $this->registrarHistorial($solicitud_id, $mejor_equipo['ID'], 'automatica', $mejor_equipo['criterios'], $mejor_equipo['distancia'], 0);
            
            // Notificar al usuario de la asignación exitosa
            $this->enviarNotificacionUsuario($solicitud_id, 
                "¡Equipo de ayuda asignado exitosamente! Equipo: {$mejor_equipo['Nombre']}, Distancia aproximada: " . round($mejor_equipo['distancia'] ?? 0, 2) . " km", 
                'success');
            
            return [
                'success' => true, 
                'message' => 'Equipo de ayuda asignado automáticamente',
                'equipo' => $mejor_equipo,
                'notificacion' => 'Equipo de ayuda asignado exitosamente'
            ];
        }
        
        return $resultado;
    }
    
    /**
     * Obtener datos de una solicitud (usando la misma lógica que procesar-solicitud.php)
     */
    private function obtenerSolicitud($solicitud_id) {
        $query = "SELECT * FROM solicitudes WHERE id = ? AND IFNULL(estado, 'pendiente') = 'pendiente'";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $solicitud_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $solicitud = $result->fetch_assoc();
        
        // Mapear campos para compatibilidad
        if ($solicitud) {
            $solicitud['nombre'] = $solicitud['nombre_completo'];
            $solicitud['ubicacion_origen'] = $solicitud['ubicacion'];
            $solicitud['vehiculo'] = $solicitud['tipo_vehiculo'];
            $solicitud['marca'] = $solicitud['marca_vehiculo'];
            $solicitud['modelo'] = $solicitud['modelo_vehiculo'];
            $solicitud['descripcion'] = $solicitud['descripcion_problema'];
        }
        
        return $solicitud;
    }
    
    /**
     * Buscar grúas disponibles según criterios
     */
    private function buscarGruasDisponibles($solicitud) {
        $radio_busqueda = $this->configuracion['radio_busqueda_km'] ?? 50;
        $considerar_tipo = $this->configuracion['considerar_tipo_servicio'] ?? '1';
        
        $query = "SELECT g.*, gd.tiene_coordenadas FROM gruas_disponibles gd 
                  JOIN gruas g ON gd.ID = g.ID 
                  WHERE 1=1";
        
        $params = [];
        $types = '';
        
        // Filtrar por tipo de servicio si está habilitado
        if ($considerar_tipo == '1') {
            $tipo_grua_preferido = $this->obtenerTipoGruaPreferido($solicitud['tipo_servicio']);
            if ($tipo_grua_preferido) {
                $query .= " AND g.Tipo = ?";
                $params[] = $tipo_grua_preferido;
                $types .= 's';
            }
        }
        
        $query .= " ORDER BY g.ultima_actualizacion_ubicacion DESC";
        
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        $gruas = [];
        while ($row = $result->fetch_assoc()) {
            // Calcular distancia si ambas tienen coordenadas
            $distancia = null;
            if ($solicitud['coordenadas'] && $row['coordenadas_actuales']) {
                $distancia = $this->calcularDistancia(
                    $solicitud['coordenadas'], 
                    $row['coordenadas_actuales']
                );
                
                // Filtrar por radio de búsqueda
                if ($distancia > $radio_busqueda) {
                    continue;
                }
            }
            
            $row['distancia'] = $distancia;
            $gruas[] = $row;
        }
        
        // Fallback: si no hay registros en gruas_disponibles, usar tabla gruas directamente
        if (empty($gruas)) {
            // Detectar columna de estado ('Estado' o 'estado')
            $col_estado = 'estado';
            $chk = $this->conn->query("SHOW COLUMNS FROM gruas LIKE 'Estado'");
            if ($chk && $chk->num_rows > 0) {
                $col_estado = 'Estado';
            }
            $q2 = "SELECT g.* , 0 as tiene_coordenadas FROM gruas g 
                   WHERE LOWER($col_estado) IN ('activa','disponible','activo','libre','available')
                   ORDER BY g.ID DESC";
            $r2 = $this->conn->query($q2);
            while ($row = $r2 && $r2->num_rows ? $r2->fetch_assoc() : []) {
                if (!$row) break;
                $row['distancia'] = null;
                $gruas[] = $row;
            }
        }
        
        return $gruas;
    }
    
    /**
     * Seleccionar la mejor grúa según criterios
     */
    private function seleccionarMejorGrua($solicitud, $gruas_disponibles) {
        $mejor_grua = null;
        $mejor_puntuacion = -1;
        
        foreach ($gruas_disponibles as $grua) {
            $puntuacion = $this->calcularPuntuacion($solicitud, $grua);
            
            if ($puntuacion > $mejor_puntuacion) {
                $mejor_puntuacion = $puntuacion;
                $mejor_grua = $grua;
                $mejor_grua['puntuacion'] = $puntuacion;
                $mejor_grua['criterios'] = $this->generarCriterios($solicitud, $grua, $puntuacion);
            }
        }
        
        return $mejor_grua;
    }
    
    /**
     * Calcular puntuación para una grúa
     */
    private function calcularPuntuacion($solicitud, $grua) {
        $puntuacion = 0;
        
        // Puntuación por urgencia
        $urgencia_scores = ['emergencia' => 100, 'urgente' => 75, 'normal' => 50];
        $puntuacion += $urgencia_scores[$solicitud['urgencia']] ?? 50;
        
        // Puntuación por distancia (menor distancia = mayor puntuación)
        if ($grua['distancia'] !== null) {
            $puntuacion += max(0, 50 - ($grua['distancia'] * 0.5));
        } else {
            $puntuacion += 25; // Puntuación base si no hay coordenadas
        }
        
        // Puntuación por tipo de grúa apropiado
        $tipo_preferido = $this->obtenerTipoGruaPreferido($solicitud['tipo_servicio']);
        if ($tipo_preferido && $grua['Tipo'] == $tipo_preferido) {
            $puntuacion += 30;
        }
        
        // Puntuación por disponibilidad reciente
        if ($grua['ultima_actualizacion_ubicacion']) {
            $minutos_desde_actualizacion = (time() - strtotime($grua['ultima_actualizacion_ubicacion'])) / 60;
            if ($minutos_desde_actualizacion < 30) {
                $puntuacion += 20;
            } elseif ($minutos_desde_actualizacion < 60) {
                $puntuacion += 10;
            }
        }
        
        // Puntuación por tener coordenadas actuales
        if ($grua['tiene_coordenadas']) {
            $puntuacion += 15;
        }
        
        return $puntuacion;
    }
    
    /**
     * Obtener tipo de grúa preferido para un tipo de servicio
     */
    private function obtenerTipoGruaPreferido($tipo_servicio) {
        // La columna 'activo' puede no existir; validar y construir query acorde
        $has_activo = $this->conn->query("SHOW COLUMNS FROM configuracion_tipos_servicio LIKE 'activo'");
        if ($has_activo && $has_activo->num_rows > 0) {
            $query = "SELECT tipo_grua_preferido FROM configuracion_tipos_servicio 
                      WHERE tipo_servicio = ? AND activo = 1";
        } else {
            $query = "SELECT tipo_grua_preferido FROM configuracion_tipos_servicio 
                      WHERE tipo_servicio = ?";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $tipo_servicio);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            return $row['tipo_grua_preferido'];
        }
        
        return null;
    }
    
    /**
     * Calcular distancia entre dos coordenadas usando fórmula de Haversine
     */
    private function calcularDistancia($coordenadas1, $coordenadas2) {
        // Formato esperado: "lat,lng"
        $coords1 = explode(',', $coordenadas1);
        $coords2 = explode(',', $coordenadas2);
        
        if (count($coords1) != 2 || count($coords2) != 2) {
            return null;
        }
        
        $lat1 = floatval($coords1[0]);
        $lng1 = floatval($coords1[1]);
        $lat2 = floatval($coords2[0]);
        $lng2 = floatval($coords2[1]);
        
        $earthRadius = 6371; // Radio de la Tierra en km
        
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLng/2) * sin($dLng/2);
        
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        
        return $earthRadius * $c;
    }
    
    /**
     * Realizar la asignación en la base de datos
     */
    private function realizarAsignacion($solicitud_id, $grua_id) {
        $this->conn->begin_transaction();
        
        try {
            // Actualizar solicitud
            // Adaptar si no existe la columna grua_asignada_id
            $has_col = $this->conn->query("SHOW COLUMNS FROM solicitudes LIKE 'grua_asignada_id'");
            if ($has_col && $has_col->num_rows > 0) {
                $query = "UPDATE solicitudes SET 
                          estado = 'asignada',
                          grua_asignada_id = ?,
                          fecha_asignacion = NOW(),
                          metodo_asignacion = 'automatica'
                          WHERE id = ? AND IFNULL(estado,'pendiente') = 'pendiente'";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ii", $grua_id, $solicitud_id);
            } else {
                $query = "UPDATE solicitudes SET 
                          estado = 'asignada',
                          fecha_asignacion = NOW(),
                          metodo_asignacion = 'automatica'
                          WHERE id = ? AND IFNULL(estado,'pendiente') = 'pendiente'";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("i", $solicitud_id);
            }
            
            if (!$stmt->execute()) {
                throw new Exception("Error al actualizar solicitud: " . $stmt->error);
            }
            
            if ($stmt->affected_rows == 0) {
                throw new Exception("La solicitud ya fue asignada o no existe");
            }
            
            // Actualizar estado de la grúa
            $query2 = "UPDATE gruas SET disponible_desde = NULL WHERE ID = ?";
            $stmt2 = $this->conn->prepare($query2);
            $stmt2->bind_param("i", $grua_id);
            
            if (!$stmt2->execute()) {
                throw new Exception("Error al actualizar grúa: " . $stmt2->error);
            }
            
            $this->conn->commit();
            
            return ['success' => true, 'message' => 'Asignación realizada correctamente'];
            
        } catch (Exception $e) {
            $this->conn->rollback();
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }
    
    /**
     * Registrar asignación en el historial
     */
    private function registrarHistorial($solicitud_id, $grua_id, $metodo, $criterios, $distancia, $tiempo_asignacion) {
        $query = "INSERT INTO historial_asignaciones 
                  (solicitud_id, grua_id, metodo_asignacion, criterios_usados, distancia_km, tiempo_asignacion_segundos, usuario_asignador) 
                  VALUES (?, ?, ?, ?, ?, ?, 'Sistema')";
        
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("iissdi", $solicitud_id, $grua_id, $metodo, $criterios, $distancia, $tiempo_asignacion);
        $stmt->execute();
    }
    
    /**
     * Generar descripción de criterios usados
     */
    private function generarCriterios($solicitud, $grua, $puntuacion) {
        $criterios = [];
        
        $criterios[] = "Urgencia: " . $solicitud['urgencia'];
        $criterios[] = "Tipo servicio: " . $solicitud['tipo_servicio'];
        $criterios[] = "Tipo grúa: " . $grua['Tipo'];
        
        if ($grua['distancia'] !== null) {
            $criterios[] = "Distancia: " . round($grua['distancia'], 2) . " km";
        }
        
        $criterios[] = "Puntuación: " . round($puntuacion, 2);
        
        return implode(", ", $criterios);
    }
    
    
    /**
     * Obtener estadísticas de auto-asignación
     */
    public function obtenerEstadisticas($fecha_inicio = null, $fecha_fin = null) {
        $where_clause = "";
        $params = [];
        $types = "";
        
        if ($fecha_inicio && $fecha_fin) {
            $where_clause = "WHERE ha.fecha_asignacion BETWEEN ? AND ?";
            $params = [$fecha_inicio, $fecha_fin];
            $types = "ss";
        }
        
        $query = "SELECT 
                    COUNT(*) as total_asignaciones,
                    SUM(CASE WHEN ha.metodo_asignacion = 'automatica' THEN 1 ELSE 0 END) as asignaciones_automaticas,
                    SUM(CASE WHEN ha.metodo_asignacion = 'manual' THEN 1 ELSE 0 END) as asignaciones_manuales,
                    AVG(ha.tiempo_asignacion_segundos) as tiempo_promedio_segundos,
                    AVG(ha.distancia_km) as distancia_promedio_km
                  FROM historial_asignaciones ha $where_clause";
        
        $stmt = $this->conn->prepare($query);
        if ($types) {
            $stmt->bind_param($types, ...$params);
        }
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result->fetch_assoc();
    }
    
    /**
     * Actualizar configuración
     */
    public function actualizarConfiguracion($parametro, $valor) {
        $query = "UPDATE configuracion_auto_asignacion SET valor = ? WHERE parametro = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $valor, $parametro);
        
        if ($stmt->execute()) {
            $this->cargarConfiguracion(); // Recargar configuración
            return true;
        }
        
        return false;
    }
    
    /**
     * Obtener configuración actual
     */
    public function obtenerConfiguracion() {
        return $this->configuracion;
    }
    
    /**
     * Resetear configuración a valores por defecto
     */
    public function resetearConfiguracion() {
        $valores_defecto = [
            'auto_asignacion_habilitada' => '1',
            'radio_busqueda_km' => '50',
            'tiempo_max_asignacion_minutos' => '30',
            'considerar_tipo_servicio' => '1',
            'notificar_asignacion' => '1',
            'prioridad_distancia' => '40',
            'prioridad_tipo_grua' => '30',
            'prioridad_disponibilidad' => '30',
            'max_solicitudes_simultaneas' => '5',
            'servicio_suspendido_clima' => '0'
        ];
        
        try {
            foreach ($valores_defecto as $parametro => $valor) {
                $query = "UPDATE configuracion_auto_asignacion SET valor = ? WHERE parametro = ?";
                $stmt = $this->conn->prepare($query);
                $stmt->bind_param("ss", $valor, $parametro);
                $stmt->execute();
            }
            
            $this->cargarConfiguracion(); // Recargar configuración
            $this->log("Configuración restablecida a valores por defecto", 'INFO');
            return true;
        } catch (Exception $e) {
            $this->log("Error al resetear configuración: " . $e->getMessage(), 'ERROR');
            return false;
        }
    }
    
    /**
     * Procesar solicitudes pendientes de asignación (usando la misma lógica que procesar-solicitud.php)
     */
    public function procesarSolicitudesPendientes($limite = 10) {
        $resultados = [];
        
        if (!$this->estaHabilitada()) {
            $this->log("Auto-asignación deshabilitada. No se procesarán solicitudes pendientes.", 'WARNING');
            return $resultados;
        }
        
        // Usar la misma lógica que procesar-solicitud.php para obtener solicitudes pendientes
        // Adaptar si no existe grua_asignada_id
        $has_col = $this->conn->query("SHOW COLUMNS FROM solicitudes LIKE 'grua_asignada_id'");
        if ($has_col && $has_col->num_rows > 0) {
            $query = "SELECT id FROM solicitudes WHERE IFNULL(estado, 'pendiente') = 'pendiente' AND (grua_asignada_id IS NULL OR grua_asignada_id = 0) ORDER BY id DESC LIMIT ?";
        } else {
            $query = "SELECT id FROM solicitudes WHERE IFNULL(estado, 'pendiente') = 'pendiente' ORDER BY id DESC LIMIT ?";
        }
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("i", $limite);
        $stmt->execute();
        $result = $stmt->get_result();
        
        while ($row = $result->fetch_assoc()) {
            $solicitud_id = $row['id'];
            $this->log("Procesando solicitud pendiente ID: $solicitud_id");
            
            $resultado = $this->asignarGrua($solicitud_id);
            $resultados[] = [
                'solicitud_id' => $solicitud_id,
                'resultado' => $resultado
            ];
        }
        
        $this->log("Procesamiento completado. " . count($resultados) . " solicitudes procesadas.");
        return $resultados;
    }
    
    /**
     * Verificar condiciones climáticas
     */
    public function verificarCondicionesClimaticas() {
        // Verificar si el servicio está suspendido manualmente
        $query = "SELECT valor FROM configuracion_auto_asignacion WHERE parametro = 'servicio_suspendido_clima' AND activo = 1";
        $result = $this->conn->query($query);
        
        if ($result && $row = $result->fetch_assoc()) {
            if ($row['valor'] == '1') {
                // Obtener razón de suspensión
                $query_razon = "SELECT razon, fecha_suspension FROM suspension_servicio WHERE activo = 1 ORDER BY fecha_suspension DESC LIMIT 1";
                $result_razon = $this->conn->query($query_razon);
                
                $razon = 'Servicio suspendido por condiciones climáticas adversas';
                if ($result_razon && $row_razon = $result_razon->fetch_assoc()) {
                    $razon = $row_razon['razon'];
                }
                
                return [
                    'apto' => false,
                    'razon' => $razon
                ];
            }
        }
        
        // Verificar condiciones climáticas automáticas (si está configurado)
        $verificar_clima_auto = $this->configuracion['verificar_clima_automatico'] ?? '0';
        if ($verificar_clima_auto == '1') {
            $clima_actual = $this->obtenerClimaActual();
            if ($clima_actual && !$clima_actual['apto']) {
                return [
                    'apto' => false,
                    'razon' => $clima_actual['razon']
                ];
            }
        }
        
        return [
            'apto' => true,
            'razon' => ''
        ];
    }
    
    /**
     * Obtener clima actual (puede integrarse con API de clima)
     */
    private function obtenerClimaActual() {
        // Por ahora, verificar configuración manual
        // En el futuro, puede integrarse con API de clima como OpenWeatherMap
        
        $condiciones_peligrosas = [
            'lluvia_fuerte' => $this->configuracion['bloquear_lluvia_fuerte'] ?? '0',
            'vientos_fuertes' => $this->configuracion['bloquear_vientos_fuertes'] ?? '0',
            'niebla_densa' => $this->configuracion['bloquear_niebla_densa'] ?? '0',
            'tormenta' => $this->configuracion['bloquear_tormenta'] ?? '1'
        ];
        
        // Aquí se podría integrar con una API de clima
        // Por ahora retornamos null para indicar que no hay verificación automática
        return null;
    }
    
    /**
     * Enviar notificación al usuario
     */
    private function enviarNotificacionUsuario($solicitud_id, $mensaje, $tipo = 'info') {
        try {
            // Obtener información del usuario de la solicitud
            $query = "SELECT usuario_id, telefono, email FROM solicitudes WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("i", $solicitud_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($row = $result->fetch_assoc()) {
                $usuario_id = $row['usuario_id'];
                
                // Guardar notificación en la base de datos
                $query_notif = "INSERT INTO notificaciones_usuarios 
                               (usuario_id, solicitud_id, tipo, mensaje, fecha_creacion, leido) 
                               VALUES (?, ?, ?, ?, NOW(), 0)";
                $stmt_notif = $this->conn->prepare($query_notif);
                $stmt_notif->bind_param("iiss", $usuario_id, $solicitud_id, $tipo, $mensaje);
                $stmt_notif->execute();
                
                // Aquí se puede agregar envío de SMS/Email/Push notification
                if ($this->configuracion['enviar_sms_notificaciones'] == '1' && !empty($row['telefono'])) {
                    // $this->enviarSMS($row['telefono'], $mensaje);
                }
                
                if ($this->configuracion['enviar_email_notificaciones'] == '1' && !empty($row['email'])) {
                    // $this->enviarEmail($row['email'], $mensaje);
                }
            }
        } catch (Exception $e) {
            error_log("Error al enviar notificación: " . $e->getMessage());
        }
    }
    
    /**
     * Registrar evento del sistema
     */
    private function registrarEventoSistema($solicitud_id, $tipo_evento, $descripcion) {
        try {
            $query = "INSERT INTO eventos_sistema 
                     (solicitud_id, tipo_evento, descripcion, fecha_evento) 
                     VALUES (?, ?, ?, NOW())";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("iss", $solicitud_id, $tipo_evento, $descripcion);
            $stmt->execute();
        } catch (Exception $e) {
            error_log("Error al registrar evento del sistema: " . $e->getMessage());
        }
    }
    
    /**
     * Notificar a los administradores
     */
    private function notificarAdministradores($asunto, $mensaje) {
        try {
            $query = "SELECT id, email FROM usuarios WHERE cargo = 'Administrador' AND activo = 1";
            $result = $this->conn->query($query);
            
            while ($row = $result->fetch_assoc()) {
                $query_notif = "INSERT INTO notificaciones_usuarios 
                               (usuario_id, tipo, mensaje, fecha_creacion, leido) 
                               VALUES (?, 'admin', ?, NOW(), 0)";
                $stmt_notif = $this->conn->prepare($query_notif);
                $stmt_notif->bind_param("is", $row['id'], $mensaje);
                $stmt_notif->execute();
                
                // Enviar email si está configurado
                if ($this->configuracion['enviar_email_admin'] == '1' && !empty($row['email'])) {
                    // $this->enviarEmail($row['email'], $asunto, $mensaje);
                }
            }
        } catch (Exception $e) {
            error_log("Error al notificar administradores: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener estado actual del servicio
     */
    public function obtenerEstadoServicio() {
        $estado = [
            'servicio_activo' => false,
            'razon_inactivo' => '',
            'gruas_disponibles' => 0,
            'solicitudes_pendientes' => 0,
            'clima_apto' => true,
            'mensaje_usuario' => ''
        ];
        
        // Verificar si está habilitado
        if (!$this->estaHabilitada()) {
            $estado['razon_inactivo'] = 'Sistema de auto-asignación deshabilitado';
            $estado['mensaje_usuario'] = 'El servicio de asignación automática está temporalmente deshabilitado. Por favor, contacte con atención al cliente.';
            return $estado;
        }
        
        // Verificar clima
        $clima = $this->verificarCondicionesClimaticas();
        if (!$clima['apto']) {
            $estado['clima_apto'] = false;
            $estado['razon_inactivo'] = $clima['razon'];
            $estado['mensaje_usuario'] = 'El servicio está suspendido debido a condiciones climáticas adversas: ' . $clima['razon'];
            return $estado;
        }
        
        // Verificar grúas disponibles
        $query_gruas = "SELECT COUNT(*) as total FROM gruas WHERE disponible_desde IS NOT NULL";
        $result_gruas = $this->conn->query($query_gruas);
        if ($result_gruas && $row = $result_gruas->fetch_assoc()) {
            $estado['gruas_disponibles'] = $row['total'];
        }
        
        if ($estado['gruas_disponibles'] == 0) {
            $estado['razon_inactivo'] = 'No hay grúas disponibles en este momento';
            $estado['mensaje_usuario'] = 'Actualmente no hay grúas disponibles. Su solicitud será atendida tan pronto como una grúa esté disponible.';
            return $estado;
        }
        
        // Verificar solicitudes pendientes
        $query_pend = "SELECT COUNT(*) as total FROM solicitudes WHERE estado = 'pendiente'";
        $result_pend = $this->conn->query($query_pend);
        if ($result_pend && $row = $result_pend->fetch_assoc()) {
            $estado['solicitudes_pendientes'] = $row['total'];
        }
        
        // Servicio activo
        $estado['servicio_activo'] = true;
        $estado['mensaje_usuario'] = '¡El servicio está operativo! Hay ' . $estado['gruas_disponibles'] . ' grúa(s) disponible(s).';
        
        return $estado;
    }
    
    /**
     * Buscar equipos de ayuda disponibles
     */
    private function buscarEquiposAyudaDisponibles($solicitud) {
        $radio_busqueda = $this->configuracion['radio_busqueda_km'] ?? 50;
        
        // Buscar equipos reales en la base de datos
        $query = "SELECT * FROM equipos_ayuda WHERE Disponible = 1 AND Tipo_Servicio = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("s", $solicitud['tipo_servicio']);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $equipos = [];
        while ($row = $result->fetch_assoc()) {
            // Calcular distancia si ambas tienen coordenadas
            $distancia = null;
            if ($solicitud['coordenadas'] && $row['Coordenadas']) {
                $distancia = $this->calcularDistancia(
                    $solicitud['coordenadas'], 
                    $row['Coordenadas']
                );
            }
            
            $equipos[] = [
                'ID' => (int)$row['ID'],
                'Nombre' => $row['Nombre'],
                'Tipo' => $row['Tipo_Servicio'],
                'coordenadas_actuales' => $row['Coordenadas'],
                'disponible' => (bool)$row['Disponible'],
                'distancia' => $distancia,
                'Telefono' => $row['Telefono'],
                'Ubicacion' => $row['Ubicacion']
            ];
        }
        
        // Si no hay equipos específicos para el tipo de servicio, buscar equipos generales
        if (empty($equipos)) {
            $query_general = "SELECT * FROM equipos_ayuda WHERE Disponible = 1 AND Tipo_Servicio = 'general'";
            $result_general = $this->conn->query($query_general);
            
            while ($row = $result_general->fetch_assoc()) {
                $distancia = null;
                if ($solicitud['coordenadas'] && $row['Coordenadas']) {
                    $distancia = $this->calcularDistancia(
                        $solicitud['coordenadas'], 
                        $row['Coordenadas']
                    );
                }
                
                $equipos[] = [
                    'ID' => (int)$row['ID'],
                    'Nombre' => $row['Nombre'],
                    'Tipo' => $row['Tipo_Servicio'],
                    'coordenadas_actuales' => $row['Coordenadas'],
                    'disponible' => (bool)$row['Disponible'],
                    'distancia' => $distancia,
                    'Telefono' => $row['Telefono'],
                    'Ubicacion' => $row['Ubicacion']
                ];
            }
        }
        
        return $equipos;
    }
    
    /**
     * Seleccionar el mejor equipo de ayuda
     */
    private function seleccionarMejorEquipoAyuda($solicitud, $equipos_disponibles) {
        if (empty($equipos_disponibles)) {
            return null;
        }
        
        // Ordenar por distancia (más cercano primero)
        usort($equipos_disponibles, function($a, $b) {
            return $a['distancia'] <=> $b['distancia'];
        });
        
        $mejor_equipo = $equipos_disponibles[0];
        $mejor_equipo['criterios'] = 'Distancia más cercana';
        
        return $mejor_equipo;
    }
    
    /**
     * Realizar asignación de equipo de ayuda
     */
    private function realizarAsignacionEquipoAyuda($solicitud_id, $equipo_id) {
        try {
            // Actualizar estado de la solicitud
            $query = "UPDATE solicitudes SET estado = 'asignada', equipo_asignado_id = ?, metodo_asignacion = 'automatica', fecha_asignacion = NOW() WHERE id = ?";
            $stmt = $this->conn->prepare($query);
            $stmt->bind_param("ii", $equipo_id, $solicitud_id);
            
            if ($stmt->execute()) {
                return ['success' => true, 'message' => 'Equipo de ayuda asignado correctamente'];
            } else {
                return ['success' => false, 'message' => 'Error al asignar equipo de ayuda: ' . $stmt->error];
            }
        } catch (Exception $e) {
            return ['success' => false, 'message' => 'Error al asignar equipo de ayuda: ' . $e->getMessage()];
        }
    }
    
    /**
     * Función de logging (añadida para compatibilidad)
     */
    private function log($mensaje, $nivel = 'INFO') {
        error_log("[$nivel] [AutoAsignacion] $mensaje");
    }
}

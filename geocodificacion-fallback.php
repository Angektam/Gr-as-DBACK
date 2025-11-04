<?php
/**
 * Sistema de Fallback para Geocodificación
 * Múltiples servicios de geocodificación con fallbacks
 */

class GeocodificacionFallback {
    private $servicios = [];
    private $cache = [];
    
    public function __construct() {
        $this->servicios = [
            "nominatim_principal" => [
                "url" => "https://nominatim.openstreetmap.org/search",
                "params" => ["format" => "json", "limit" => "1", "countrycodes" => "mx"],
                "timeout" => 10
            ],
            "nominatim_alternativo" => [
                "url" => "https://nominatim.openstreetmap.org/search",
                "params" => ["format" => "json", "limit" => "1"],
                "timeout" => 15
            ]
        ];
    }
    
    public function geocodificar($direccion) {
        // Verificar cache primero
        $cache_key = md5($direccion);
        if (isset($this->cache[$cache_key])) {
            return $this->cache[$cache_key];
        }
        
        foreach ($this->servicios as $nombre => $servicio) {
            try {
                $resultado = $this->llamarServicio($servicio, $direccion);
                if ($resultado) {
                    $this->cache[$cache_key] = $resultado;
                    return $resultado;
                }
            } catch (Exception $e) {
                error_log("Error en servicio $nombre: " . $e->getMessage());
                continue;
            }
        }
        
        // Fallback a coordenadas por defecto de Los Mochis
        return $this->coordenadasPorDefecto($direccion);
    }
    
    private function llamarServicio($servicio, $direccion) {
        $params = array_merge($servicio["params"], ["q" => $direccion]);
        $url = $servicio["url"] . "?" . http_build_query($params);
        
        $context = stream_context_create([
            "http" => [
                "timeout" => $servicio["timeout"],
                "header" => "User-Agent: DBACK-Sistema/1.0\r\n"
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return false;
        }
        
        $data = json_decode($response, true);
        
        if (empty($data) || !isset($data[0])) {
            return false;
        }
        
        $resultado = $data[0];
        
        return [
            "lat" => floatval($resultado["lat"]),
            "lng" => floatval($resultado["lon"]),
            "nombre" => $resultado["display_name"],
            "servicio" => $servicio["url"]
        ];
    }
    
    private function coordenadasPorDefecto($direccion) {
        // Coordenadas base de Los Mochis
        $coordenadas_base = [
            "lat" => 25.7945,
            "lng" => -109.0000
        ];
        
        // Pequeña variación aleatoria para evitar superposición
        $lat = $coordenadas_base["lat"] + (rand(-50, 50) / 10000);
        $lng = $coordenadas_base["lng"] + (rand(-50, 50) / 10000);
        
        return [
            "lat" => $lat,
            "lng" => $lng,
            "nombre" => $direccion . " (Aproximado - Los Mochis, Sinaloa)",
            "servicio" => "fallback_local"
        ];
    }
    
    public function geocodificarInversa($lat, $lng) {
        $url = "https://nominatim.openstreetmap.org/reverse?format=json&lat=$lat&lon=$lng&zoom=18&addressdetails=1";
        
        $context = stream_context_create([
            "http" => [
                "timeout" => 10,
                "header" => "User-Agent: DBACK-Sistema/1.0\r\n"
            ]
        ]);
        
        $response = @file_get_contents($url, false, $context);
        
        if ($response === false) {
            return "Ubicación desconocida";
        }
        
        $data = json_decode($response, true);
        
        if (empty($data) || !isset($data["display_name"])) {
            return "Ubicación desconocida";
        }
        
        return $data["display_name"];
    }
}

// Función de conveniencia para uso directo
function geocodificarDireccion($direccion) {
    static $geocoder = null;
    if ($geocoder === null) {
        $geocoder = new GeocodificacionFallback();
    }
    return $geocoder->geocodificar($direccion);
}

function geocodificarInversa($lat, $lng) {
    static $geocoder = null;
    if ($geocoder === null) {
        $geocoder = new GeocodificacionFallback();
    }
    return $geocoder->geocodificarInversa($lat, $lng);
}
?>
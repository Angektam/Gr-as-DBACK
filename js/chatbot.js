/**
 * Chatbot orientado a solicitantes de grúa
 * Flujo guiado para recopilar datos básicos y dirigir al formulario o contacto
 */

document.addEventListener('DOMContentLoaded', () => {
    const toggleButton = document.querySelector('.chatbot-toggle');
    const chatbotWrapper = document.querySelector('.chatbot-wrapper');
    const closeButton = document.querySelector('.chatbot-close');
    const messagesContainer = document.querySelector('.chatbot-body');
    const inputField = document.querySelector('.chatbot-input');
    const sendButton = document.querySelector('.chatbot-send');
    const optionsContainer = document.querySelector('.chatbot-dynamic-options');

    if (!toggleButton || !chatbotWrapper || !messagesContainer || !inputField || !sendButton) {
        return;
    }

    const state = {
        currentStep: 'welcome',
        data: {
            nombre: '',
            telefono: '',
            ubicacion: '',
            servicio: ''
        }
    };

    const services = [
        'Arrastre de vehículo',
        'Cambio de llanta',
        'Paso de corriente',
        'Suministro de gasolina',
        'Rescate en accidente',
        'Otro servicio'
    ];

    function digitsOnly(value) {
        return (value || '').toString().replace(/\D/g, '');
    }

    function formatPhone(number) {
        const digits = digitsOnly(number);
        const withCountry = digits.startsWith('52') && digits.length === 12 ? digits.slice(2) : (digits.length === 11 && digits.startsWith('52') ? digits.slice(2) : digits);
        const local = withCountry.length === 10 ? withCountry : digits.slice(-10);
        return `+52 ${local.slice(0, 2)} ${local.slice(2, 6)} ${local.slice(6)}`;
    }

    function formatPhoneLink(number) {
        const digits = digitsOnly(number);
        if (digits.startsWith('52')) {
            return `+${digits}`;
        }
        return `+52${digits}`;
    }

    const steps = {
        welcome: () => {
            addBotMessage('👋 ¡Hola! Soy el asistente virtual de Grúas DBACK.');
            addBotMessage('Estoy aquí para ayudarte a solicitar el servicio adecuado. ¿Qué te gustaría hacer?');
            showOptions([
                { label: 'Solicitar un servicio de grúa', value: 'solicitar' },
                { label: 'Conocer los tipos de servicio', value: 'tipos' },
                { label: 'Hablar con un asesor', value: 'asesor' }
            ]);
        },
        solicitar: () => {
            addBotMessage('Perfecto, vamos a recopilar algunos datos básicos para agilizar tu servicio.');
            addBotMessage('¿Cómo te llamas?');
            state.currentStep = 'collect_nombre';
        },
        tipos: () => {
            addBotMessage('Ofrecemos los siguientes servicios:');
            const list = services.map(servicio => `• ${servicio}`).join('<br>');
            addBotMessage(`<div class="chatbot-summary">${list}</div>`);
            addBotMessage('¿Quieres solicitar uno de estos servicios ahora mismo?');
            showOptions([
                { label: 'Sí, solicitar servicio', value: 'solicitar' },
                { label: 'No, gracias', value: 'despedida' }
            ]);
        },
        asesor: () => {
            addBotMessage('Puedes comunicarte con un asesor llamando al <strong>999 259 2882</strong> o por WhatsApp al <strong>668 825 3351</strong>.');
            addBotMessage('¿Te gustaría que recopilemos tus datos para que un asesor te contacte?');
            showOptions([
                { label: 'Sí, compartir mis datos', value: 'solicitar' },
                { label: 'No, gracias', value: 'despedida' }
            ]);
        },
        collect_nombre: (value) => {
            if (!value || value.trim().length < 2) {
                addBotMessage('Por favor, indícame tu nombre (mínimo 2 caracteres).');
                return;
            }
            state.data.nombre = value.trim();
            addUserMessage(value.trim());
            addBotMessage(`Mucho gusto, ${state.data.nombre}. ¿Cuál es tu número de teléfono?`);
            state.currentStep = 'collect_telefono';
            inputField.value = '';
        },
        collect_telefono: (value) => {
            const sanitized = (value || '').replace(/\D/g, '');
            if (sanitized.length < 10) {
                addBotMessage('Necesito un número de contacto válido (10 dígitos). Intenta nuevamente, por favor.');
                return;
            }
            state.data.telefono = sanitized;
            addUserMessage(state.data.telefono);
            addBotMessage('¿Desde dónde necesitas el servicio? Indica colonia, calle o referencia cercana.');
            state.currentStep = 'collect_ubicacion';
            inputField.value = '';
        },
        collect_ubicacion: (value) => {
            if (!value || value.trim().length < 5) {
                addBotMessage('Para ubicarte mejor, descríbenos un poco más tu ubicación.');
                return;
            }
            state.data.ubicacion = value.trim();
            addUserMessage(state.data.ubicacion);
            addBotMessage('¿Qué tipo de servicio necesitas?');
            showOptions(services.map(servicio => ({ label: servicio, value: `servicio:${servicio}` })));
            state.currentStep = 'collect_servicio';
            inputField.value = '';
        },
        collect_servicio: (value) => {
            const servicio = value.replace('servicio:', '');
            state.data.servicio = servicio;
            addUserMessage(servicio);
            showSummary();
            state.currentStep = 'resumen';
        },
        resumen: () => {
            showSummary();
        },
        despedida: () => {
            addBotMessage('Entendido. Si necesitas algo más, aquí estaré disponible. ¡Gracias por visitar Grúas DBACK!');
            showOptions([
                { label: 'Volver al inicio', value: 'welcome' },
                { label: 'Salir del chat', value: 'close' }
            ]);
        }
    };

    function addBotMessage(text) {
        const message = document.createElement('div');
        message.className = 'chatbot-message bot';
        message.innerHTML = `<span>${text}</span>`;
        messagesContainer.appendChild(message);
        scrollToBottom();
    }

    function addUserMessage(text) {
        const message = document.createElement('div');
        message.className = 'chatbot-message user';
        message.innerHTML = `<span>${text}</span>`;
        messagesContainer.appendChild(message);
        scrollToBottom();
    }

    function showOptions(options) {
        optionsContainer.innerHTML = '';
        options.forEach(option => {
            const button = document.createElement('button');
            button.className = 'chatbot-option';
            button.type = 'button';
            button.textContent = option.label;
            button.dataset.value = option.value;
            button.addEventListener('click', () => handleOption(option.value, option.label));
            optionsContainer.appendChild(button);
        });
        optionsContainer.parentElement.style.display = 'block';
    }

    function hideOptions() {
        optionsContainer.innerHTML = '';
        optionsContainer.parentElement.style.display = 'none';
    }

    function handleOption(value, label) {
        hideOptions();
        if (value === 'close') {
            closeChat();
            return;
        }
        if (label) {
            addUserMessage(label);
        }
        if (value.startsWith('servicio:')) {
            steps.collect_servicio(value);
            return;
        }
        if (steps[value]) {
            state.currentStep = value;
            steps[value]();
        }
    }

    function showSummary() {
        const { nombre, telefono, ubicacion, servicio } = state.data;
        const summary = `
            <div class="chatbot-summary">
                <strong>Esto es lo que tengo hasta ahora:</strong>
                <ul>
                    <li><strong>Nombre:</strong> ${nombre}</li>
                    <li><strong>Teléfono:</strong> ${telefono}</li>
                    <li><strong>Ubicación:</strong> ${ubicacion}</li>
                    <li><strong>Servicio requerido:</strong> ${servicio}</li>
                </ul>
            </div>
        `;
        addBotMessage(summary);
        addBotMessage('Con estos datos podemos iniciar tu solicitud. ¿Cómo prefieres continuar?');

        const formUrl = document.body.dataset.chatbotFormUrl || 'solicitud.php';
        const phone = document.body.dataset.chatbotPhone || '529992592882';
        const whatsapp = document.body.dataset.chatbotWhatsapp || '526688253351';

        const phoneLink = formatPhoneLink(phone);
        const whatsappDigits = digitsOnly(whatsapp);
        const whatsappLink = whatsappDigits.startsWith('52') ? whatsappDigits : `52${whatsappDigits}`;

        const readablePhone = formatPhone(phone);
        const readableWhatsapp = formatPhone(whatsappDigits);

        const actions = document.createElement('div');
        actions.className = 'chatbot-actions';
        actions.innerHTML = `
            <a class="primary" href="${formUrl}" target="_blank" rel="noopener">
                <i class="fas fa-clipboard-check"></i> Completar formulario en línea
            </a>
            <a class="secondary" href="tel:${phoneLink}">
                <i class="fas fa-phone-alt"></i> Llamar ahora al ${readablePhone}
            </a>
            <a class="secondary" href="https://wa.me/${whatsappLink}" target="_blank" rel="noopener">
                <i class="fab fa-whatsapp"></i> WhatsApp ${readableWhatsapp}
            </a>
        `;
        messagesContainer.appendChild(actions);
        scrollToBottom();

        addBotMessage('¿Quieres iniciar una nueva solicitud o finalizar por ahora?');
        showOptions([
            { label: 'Nueva solicitud', value: 'solicitar' },
            { label: 'Finalizar chat', value: 'despedida' }
        ]);
    }

    function scrollToBottom() {
        messagesContainer.scrollTo({
            top: messagesContainer.scrollHeight,
            behavior: 'smooth'
        });
    }

    function openChat() {
        chatbotWrapper.classList.add('open');
        toggleButton.setAttribute('aria-expanded', 'true');
        if (messagesContainer.childElementCount === 0) {
            steps.welcome();
        }
    }

    function closeChat() {
        chatbotWrapper.classList.remove('open');
        toggleButton.setAttribute('aria-expanded', 'false');
    }

    function handleInputSubmit() {
        const value = inputField.value.trim();
        if (!value) return;

        switch (state.currentStep) {
            case 'collect_nombre':
                steps.collect_nombre(value);
                break;
            case 'collect_telefono':
                steps.collect_telefono(value);
                break;
            case 'collect_ubicacion':
                steps.collect_ubicacion(value);
                break;
            case 'collect_servicio':
                steps.collect_servicio(`servicio:${value}`);
                break;
            default:
                addUserMessage(value);
                addBotMessage('Estoy procesando tu información, por favor elige una de las opciones disponibles.');
        }
        inputField.value = '';
    }

    toggleButton.addEventListener('click', () => {
        if (chatbotWrapper.classList.contains('open')) {
            closeChat();
        } else {
            openChat();
        }
    });

    closeButton.addEventListener('click', closeChat);

    sendButton.addEventListener('click', (event) => {
        event.preventDefault();
        handleInputSubmit();
    });

    inputField.addEventListener('keydown', (event) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            handleInputSubmit();
        }
    });

    // Cerrar el chatbot al presionar ESC
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && chatbotWrapper.classList.contains('open')) {
            closeChat();
        }
    });
});


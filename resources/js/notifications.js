class NotificationManager {
    constructor() {
        this.socket = null;
        this.channel = 'notifications';
        this.defaultOptions = {
            duration: 3000,
            position: 'top-right',
            closeButton: true,
            progressBar: true,
        };
    }

    connect(url = 'ws://localhost:8080') {
        this.socket = new WebSocket(url);

        this.socket.onopen = () => {
            this.subscribe(this.channel);
            console.log('Connected to WebSocket server');
        };

        this.socket.onmessage = (event) => {
            const data = JSON.parse(event.data);
            this.showNotification(data.message, data.type, data.options);
        };

        this.socket.onerror = (error) => {
            console.error('WebSocket error:', error);
        };

        this.socket.onclose = () => {
            console.log('Disconnected from WebSocket server');
            // Tentative de reconnexion après 5 secondes
            setTimeout(() => this.connect(url), 5000);
        };
    }

    subscribe(channel) {
        if (this.socket && this.socket.readyState === WebSocket.OPEN) {
            this.socket.send(JSON.stringify({
                action: 'subscribe',
                channel: channel
            }));
        }
    }

    showNotification(message, type = 'info', options = {}) {
        const finalOptions = { ...this.defaultOptions, ...options };

        // Utilisation de Toastify
        Toastify({
            text: message,
            duration: finalOptions.duration,
            close: finalOptions.closeButton,
            gravity: finalOptions.position.split('-')[0],
            position: finalOptions.position.split('-')[1],
            backgroundColor: this.getBackgroundColor(type),
            progressBar: finalOptions.progressBar,
            stopOnFocus: true,
        }).showToast();
    }

    getBackgroundColor(type) {
        switch (type) {
            case 'success':
                return '#48BB78';
            case 'error':
                return '#F56565';
            case 'warning':
                return '#ED8936';
            default:
                return '#4299E1';
        }
    }
}

// Initialisation
window.notifications = new NotificationManager();
document.addEventListener('DOMContentLoaded', () => {
    window.notifications.connect();
});

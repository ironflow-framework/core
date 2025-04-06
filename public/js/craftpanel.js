// Système de notifications
class NotificationManager {
    static show(message, type = 'info') {
        Toastify({
            text: message,
            duration: 3000,
            gravity: "top",
            position: "right",
            backgroundColor: this.getColorForType(type),
        }).showToast();
    }

    static getColorForType(type) {
        const colors = {
            success: '#48BB78',
            error: '#F56565',
            info: '#4299E1',
            warning: '#ECC94B'
        };
        return colors[type] || colors.info;
    }
}

// Système de channels
class ChannelManager {
    constructor() {
        this.channels = new Map();
        this.initializeDefaultChannels();
    }

    initializeDefaultChannels() {
        // Canal de notifications générales
        this.subscribe('notifications', (data) => {
            NotificationManager.show(data.message, data.type);
        });

        // Canal des mises à jour en temps réel
        this.subscribe('updates', (data) => {
            if (data.type === 'refresh') {
                window.location.reload();
            }
        });
    }

    subscribe(channelName, callback) {
        const wsUrl = document.querySelector('meta[name="ws-url"]').content;
        const channel = new WebSocket(`${wsUrl}/${channelName}`);

        channel.onmessage = (event) => {
            const data = JSON.parse(event.data);
            callback(data);
        };

        channel.onclose = () => {
            console.log(`Channel ${channelName} closed. Attempting to reconnect...`);
            setTimeout(() => this.subscribe(channelName, callback), 3000);
        };

        this.channels.set(channelName, channel);
    }

    unsubscribe(channelName) {
        const channel = this.channels.get(channelName);
        if (channel) {
            channel.close();
            this.channels.delete(channelName);
        }
    }

    broadcast(channelName, data) {
        const channel = this.channels.get(channelName);
        if (channel && channel.readyState === WebSocket.OPEN) {
            channel.send(JSON.stringify(data));
        }
    }
}

// Initialisation
document.addEventListener('DOMContentLoaded', () => {
    window.channelManager = new ChannelManager();
    
    // Exemple d'utilisation
    document.querySelectorAll('[data-notification]').forEach(element => {
        element.addEventListener('click', () => {
            const message = element.dataset.notification;
            const type = element.dataset.notificationType || 'info';
            NotificationManager.show(message, type);
        });
    });
});

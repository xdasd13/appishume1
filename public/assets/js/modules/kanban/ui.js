const KanbanUI = {
    init() {
        this.initializeTooltips();
        this.showFlashMessages();
    },

    initializeTooltips() {
        const tooltips = document.querySelectorAll('[data-bs-toggle="tooltip"]');
        tooltips.forEach(el => new bootstrap.Tooltip(el));
    },

    showFlashMessages() {
        this.showSessionMessages();
        this.showServerMessages();
    },

    showSessionMessages() {
        const toastMessage = sessionStorage.getItem('toast_message');
        const toastIcon = sessionStorage.getItem('toast_icon');

        if (toastMessage) {
            this.showToast(toastMessage, toastIcon || 'success');
            sessionStorage.removeItem('toast_message');
            sessionStorage.removeItem('toast_icon');
        }
    },

    showServerMessages() {
        if (window.FLASH_SUCCESS) {
            this.showNotification(window.FLASH_SUCCESS, 'success');
        }
        if (window.FLASH_ERROR) {
            this.showNotification(window.FLASH_ERROR, 'error');
        }
    },

    showToast(message, icon = 'success') {
        const Toast = Swal.mixin({
            toast: true,
            position: "top-end",
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true,
            didOpen: (toast) => {
                toast.onmouseenter = Swal.stopTimer;
                toast.onmouseleave = Swal.resumeTimer;
            }
        });

        Toast.fire({ icon, title: message });
    },

    showNotification(message, type) {
        const config = {
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 4000,
            timerProgressBar: true,
            title: message,
            ...this.getNotificationConfig(type)
        };

        Swal.fire(config);
    },

    getNotificationConfig(type) {
        const configs = {
            success: { icon: 'success', background: '#d4edda' },
            error: { icon: 'error', background: '#f8d7da' },
            info: { icon: 'info', background: '#d1ecf1' }
        };
        return configs[type] || {};
    },

    updateColumnCounters() {
        document.querySelectorAll('.kanban-column').forEach(column => {
            const header = column.querySelector('.kanban-column-header h5');
            const body = column.querySelector('.kanban-column-body');
            const cards = body.querySelectorAll('.kanban-card').length;
            const counter = header.querySelector('.badge');

            if (counter) {
                counter.textContent = cards;
            }
        });
    },

    getStatusColor(status) {
        const colors = {
            'Planificación': '#7c3aed',
            'Producción': '#3b82f6',
            'Postproducción': '#f59e0b',
            'Finalizado': '#10b981'
        };
        return colors[status] || '#757575';
    },

    getStatusIcon(status) {
        const icons = {
            'Planificación': 'info',
            'Producción': 'info',
            'Postproducción': 'warning',
            'Finalizado': 'success'
        };
        return icons[status] || 'question';
    },

    getStatusFontAwesome(status) {
        const icons = {
            'Planificación': 'fas fa-calendar-alt',
            'Producción': 'fas fa-video',
            'Postproducción': 'fas fa-magic',
            'Finalizado': 'fas fa-check-circle'
        };
        return icons[status] || 'fas fa-question-circle';
    },

    toggleDescription(button) {
        const container = button.closest('.description-container');
        const text = container.querySelector('.description-text');
        const isExpanded = text.style.webkitLineClamp === 'unset';

        if (isExpanded) {
            text.style.webkitLineClamp = '2';
            button.innerHTML = '<small>Ver más</small>';
        } else {
            text.style.webkitLineClamp = 'unset';
            button.innerHTML = '<small>Ver menos</small>';
        }
    }
};

window.toggleDescription = (button) => KanbanUI.toggleDescription(button);

const NOTIFICATION_TYPE = {
    "success": "success",
    "error": "error"
}


class Notification_displayer {
    /** HTMLElement */
    container;

    constructor(containerElement) {
        this.container = containerElement;
    }

    /**
     * Add a new notification
     */
    notify(type, message) {
        if (!Object.values(NOTIFICATION_TYPE).includes(type)) throw new Error(`Cannot print notification: Type '${type}' is not supported.`)

        const notification = document.createElement("div");
        notification.classList.add("notification");
        notification.classList.add(NOTIFICATION_TYPE[type]);
        //TODO: THIS ALLOWS XSS INJECTIONS.
        notification.innerHTML = `<h3>${message}</h3>`;

        this.container.appendChild(notification);
    }

    /**
     * Removes all notifications and clear the container.
     */
    clearAll() {
        this.container.innerHTML = "";
    }

    /**
     * Removes all error notifications.
     */
    clearAllError() {
        this.container.querySelectorAll(".notification.error").forEach(e => e.remove());
    }
}

const notification_displayer = new Notification_displayer(document.getElementById('main_notification_container'));
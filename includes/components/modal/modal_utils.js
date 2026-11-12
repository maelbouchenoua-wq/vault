/**
 * Represents a dynamic modal
 */
class Modal {
    id;
    element = null;

    constructor(name, content) {
        this.id = name;
        this.insert(content);
    }

    insert(html) {
        const element = document.createElement("div");
        element.id = this.id;
        element.classList.add("modal");
        element.innerHTML = html;

        document.body.appendChild(element);
        this.element = element;
    }

    delete() {
        this.element.remove();
        delete this;
    }

    static delete(id) {
        document.querySelector(`#${id}`).remove();
    }
}
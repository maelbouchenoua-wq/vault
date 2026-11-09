/**
 * This file is intended to send form actions asynchronously.
 * It will replace all default form action by custom AJAX async requests.
 */

/**
 * Build a request from a form `event`, run it and parse result.
 * @param event SubmitEvent The Submit event from the Form.
 * All requests parameters will be inferred from the form properties.
 * If not `method` is set on the HTML form, GET will be used.
 */
function sendForm(event) {
    // Infer request data
    const formElement = event.target;
    if (formElement === undefined) throw new Error("Cannot send the form: Cannot infer request data from the form: Cannot locate the Form HTML element from the provided event.");

    const targetPath = formElement.action;
    if (targetPath === undefined || targetPath.length === 0) throw new Error("Cannot send the form: Cannot infer the request taget path from the form element. \n Set an 'action' attribute to the form element.");

    const method = formElement.method ?? "GET";

    // Get form data
    const data = new FormData(formElement);
    if (data === undefined || data.keys().toArray().length === 0) throw new Error("Cannot send the form: Cannot get form data.")

    const request = {body: data, method: method};

    notification_displayer?.clearAllError();
    try {
        // Send
        fetch(targetPath, request)
            .then(async response => {
                console.log(response)
                const data = await response.json();
                const {message} = data;

                if (!response.ok) display_error(new Error(message));
                else display_success(message);
            })
            .catch(error => {
                display_error(error);
            })
    } catch (error) {
        display_error(error);
    }
}

try {
    // Attach event listener to all form that are is the file witch the script is loaded in.
    forms = document.querySelectorAll("form");
    forms.forEach(form => form.addEventListener("submit", (event) => {
        event.preventDefault();
        sendForm(event);
    }));
    console.info(`Bound ${forms.length} forms.`);
} catch (error) {
    console.error(error)
    throw new Error("Cannot bind forms.")
}

const display_error = (error) => {
    const message = (error instanceof Error) ? error.message : error;
    notification_displayer.notify(NOTIFICATION_TYPE.error, message)
    console.error(message);
}

const display_success = (message) => {
    message = message ?? "L'action a été transmise correctement.";
    notification_displayer.notify(NOTIFICATION_TYPE.success, message);
    console.info(message);
}

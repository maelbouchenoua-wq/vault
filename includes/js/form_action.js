/**
 * This file is intended to send form actions asynchronously.
 * It will replace all default form action by custom AJAX async requests.
 */

/**
 * Build a request from a form `event`, run it and parse result.
 * @param event SubmitEvent The Submit event from the Form.
 * All requests parameters will be inferred from the form properties.
 * If not `method` is set on the HTML form, POST will be used.
 */
function sendForm(event) {
    return new Promise((resolve) => {

        // Infer request data
        const formElement = event.target;
        if (formElement === undefined) throw new Error("Cannot send the form: Cannot infer request data from the form: Cannot locate the Form HTML element from the provided event.");

        const targetPath = formElement.action;
        if (targetPath === undefined || targetPath.length === 0) throw new Error("Cannot send the form: Cannot infer the request taget path from the form element. \n Set an 'action' attribute to the form element.");

        const method = formElement.method ?? "POST";

        // Get form data
        const data = new FormData(formElement);

        const request = {body: data, method: method};

        notification_displayer?.clearAllError();
        try {
            // Send
            fetch(targetPath, request)
                .then(async response => {
                    if (!response.ok) {
                        const data = await parse_response_data(response);
                        const message = data?.message;
                        display_error(new Error(message));
                    } else {
                        const data = await parse_response_data(response);
                        let message = data?.message ?? "L'action a été transmise correctement.";

                        // If request was redirected, redirect user to the specified location.
                        if (response.redirected) {
                            setTimeout(async () => {
                                const location_path = response.url;
                                window.location.assign(location_path);
                            }, 3000);
                            message += " Redirection...";
                        }
                        display_success(message);
                        resolve(data);
                    }
                })
                .catch(error => {
                    display_error(error);
                })
        } catch (error) {
            display_error(error);
        }
    });
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

async function parse_response_data(response) {
    try {
        return await response.json();
    } catch (e) {
        return null;
    }
}

/*
document.addEventListener('submit', (event) => {
    event.preventDefault();

    const finishedCallback = event.target.getAttribute("whenFinished");

    sendForm(event, finishedCallback);
});*/

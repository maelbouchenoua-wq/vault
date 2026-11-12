/**
 * This is the dedicated script for the admin page.
 */

function sendDeleteMedia(id, name) {
    const modal = new Modal(`delete-confirmation-${id}`, `
    <div class="container">
        <h1>Êtes-vous sûr de vouloir supprimer ${name} ?</h1>
        <p>Cette action est irreversible. <br>
            Elle supprimera le média de toutes les collections qui le contiennent.</p>
        <div class="button-container">
            <form id="delete" action="/includes/actions/delete_media_action.php?media_id=${id}" method="POST">
                <input type="submit" id="submit">
                <label for="submit">
                    <a class="danger">Supprimer</a>
                </label>
            </form>
            <a onclick="Modal.delete('${`delete-confirmation-${id}`}')">Fermer</a>
        </div>
    </div>
    `);

    // Bind event
    modal.element.querySelector("form#delete").addEventListener("submit", async (event) => {
        event.preventDefault()
        await sendForm(event);
        modal.delete();
        remove_row(id);
    });
}

// Initialization

// Bind the add_media form.
const addMediaForm = document.querySelector("form#add_media_form");
if (addMediaForm === null) throw new Error("Cannot bind the add media form: Cannot locate HTML element with id 'add_media_form'.");
addMediaForm.addEventListener("submit", async (event) => {
    event.preventDefault()
    const result = await sendForm(event);
    console.log(result)
    if (result.new_row) add_row(result.new_row);
});

// --- DOM Update functions

const table_container = document.querySelector("body .records_table tbody");
if (table_container === null) throw new Error("Cannot find the table container.");


/** Delete a row from the rendered table */
function remove_row(id) {
    table_container.querySelector(`#media-${id}`).remove()
}

/** Insert a new row from the `content` at the end of the table.
 * WARNING: This function could introduce XSS injections. */
function add_row(content) {
    const parser = document.createElement("tbody");
    parser.innerHTML = content;
    table_container.append(parser.firstChild);
}
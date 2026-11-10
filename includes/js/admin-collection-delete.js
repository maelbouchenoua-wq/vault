function sendDeleteMedia(id, name) {
    const modal = new Modal(`delete-confirmation-${id}`, `
    <div class="container">
        <h1>Êtes-vous sûr de vouloir supprimer ${name} ?</h1>
        <p>Cette action est irreversible. <br>
            Elle supprimera le média de toutes les collections qui le contiennent.</p>
        <div class="button-container">
            <form onsubmit="Modal.delete('delete-confirmation-${id}'); remove_row('${id}');" action="/includes/actions/delete_media_action.php?media_id=${id}" method="POST">
                <input type="submit" id="submit">
                <label for="submit">
                    <a class="danger">Supprimer</a>
                </label>
            </form>
            <a onclick="Modal.delete('${`delete-confirmation-${id}`}')">Fermer</a>
        </div>
    </div>
    `);
}

const table_container = document.querySelector("body .records_table");
if (table_container === null) throw new Error("Cannot find the table container.");


function remove_row(id) {
    console.log(id)
    table_container.querySelector(`#media-${id}`).remove()
}

function add_row(content) {
    table_container.append(content)
}
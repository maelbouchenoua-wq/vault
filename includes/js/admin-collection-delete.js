function sendDeleteMedia(id, name) {
    const modal = new Modal(`delete-confirmation-${id}`, `
    <div class="container">
        <h1>Êtes-vous sûr de vouloir supprimer ${name} ?</h1>
        <p>Cette action est irreversible. <br>
            Elle supprimera le média de toutes les collections qui le contiennent.</p>
        <div class="button-container">
            <form action="/includes/actions/delete_media_action.php?media_id=${id}" method="POST">
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
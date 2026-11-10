const mediaSearch1 = document.querySelector(".search-bar1")

mediaSearch1?.addEventListener('keyup', function(event) {
    event.preventDefault();
    const medias = document.querySelectorAll('.card');



    medias.forEach(function (media, i) {
        const title = media.querySelector('h2');
        const titleText = title.innerText.toLowerCase();
        const searchText = mediaSearch1.value.toLowerCase();

        if(mediaSearch1.value == '') {
            media.style.display = "block";
        }else {
            if(titleText.includes(searchText)) {
                media.style.display = "block";
            }else {
                media.style.display = 'none';
            }
        }
    })
});
//
const searchBar = document.querySelector(".search-bar1");

document.addEventListener("keydown", function(event) {


    // si on appuie sur controle (pour windows) et sur k:
    if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === "k") {
        event.preventDefault();

        if (searchBar) {
            searchBar.focus();
        }
    }
});




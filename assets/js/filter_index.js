const mediaSearch = document.querySelector(".search-bar2")

mediaSearch?.addEventListener('keyup', function(event) {
    event.preventDefault();
    const medias = document.querySelectorAll('.card');



    medias.forEach(function (media, i) {
        const title = media.querySelector('h2');
        const titleText = title.innerText.toLowerCase();
        const searchText = mediaSearch.value.toLowerCase();

        if(mediaSearch.value == '') {
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
const mediaFilters = document.querySelectorAll("[name='sort_cards']")

// Applique la fonction handle media filter sur addEventListener pour chacune des card
mediaFilters.forEach(media => media.addEventListener('change', handleMediaFilter));

function handleMediaFilter(event){
    event.preventDefault();

    const medias = document.querySelectorAll('.card');
    const selected = event.target.value;

    medias.forEach(function (media, i) {

        // type du media (film, séries, jeu)
        const tag = media.querySelector('.tag');
        const card = tag.closest('.card');

        if(!tag.classList.contains(selected)) {
            card.style.display = "none";
        }

        if(tag.classList.contains(selected) || selected === "all"){
            card.style.display = "block"
        }
    })
}


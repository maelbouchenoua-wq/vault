const mediaSearch = document.querySelector(".search-bar2")
let buttonSelected = 'all';
let searchedText = ''
mediaSearch?.addEventListener('keyup', function(event) {
    event.preventDefault();
    const medias = document.querySelectorAll('.card');

    const searchText = mediaSearch.value.toLowerCase();
    searchedText = searchText;

    medias.forEach(function (media, i) {
        const title = media.querySelector('h2');
        const titleText = title.innerText.toLowerCase();
         const tag = media.querySelector('.tag');

        if(searchText == ''  && (tag.classList.contains(buttonSelected) || buttonSelected === "all")) {
            media.style.display = "block";
        }else {
            if(titleText.includes(searchText) && (tag.classList.contains(buttonSelected) || buttonSelected === "all")) {
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
    buttonSelected = selected;
    medias.forEach(function (media, i) {

        // type du media (film, séries, jeu)
        const tag = media.querySelector('.tag');

       // const display = selected === "all" || tag.classList.contains(selected);

        if(selected === "all" || tag.classList.contains(selected)){

            media.style.display = "block";

        } else {
            media.style.display = 'none';
        }


     /*   if(!tag.classList.contains(selected)) {
            media.style.display = 'none';
        }
        else{
            if(tag.classList.contains(selected) || selected === "all"){

                media.style.display = "block";

            }
        }*/
    })
}


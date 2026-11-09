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
            if(titleText === searchText) {
                media.style.display = "block";
            }else {
                media.style.display = 'none';
            }
        }
    })
});
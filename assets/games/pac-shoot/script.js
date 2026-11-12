let container = document.querySelector('.container');
let btn = document.querySelector('.start-button');
let scoreContainer = document.querySelector('.score');
let timerContainer = document.querySelector('.timer');

//Start the game

btn.onclick = function() {
    let score = 0;
    let time = 31;
    container.innerHTML = '';

    //Create target (pacman ghost) at random position every second
    let interval = setInterval(function showTarget(){
        let target = document.createElement('img');
        target.className = 'target';
        target.src = 'target.png';
        container.appendChild(target);
        target.style.top = 500 - target.offsetHeight + 'px';
        target.style.left = Math.random() * (600 - target.offsetWidth) + 'px';

        //Diseaper target
        setTimeout(function() {
           target.remove();

        }, 4000);

        //Click on target
        target.onclick = function(){
            score += 1;
            target.style.display = 'none';
            scoreContainer.innerHTML = `Score : ${score} `

        }

        time -=1;

        //Score and time
        timerContainer.innerHTML = `Temps : ${time} `

        //End of the game at end of time
        if(time == 0) {
            clearInterval(interval);
            container.innerHTML = "Le jeu est terminé."
        }
        
    }, Math.floor(Math.random() * (2000 - 600 + 1)) + 600);
}








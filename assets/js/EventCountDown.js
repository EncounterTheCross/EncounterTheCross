// any CSS you import will output into a single css file (app.scss in this case)
import '../scss/styles.scss';
// start the Stimulus application
import '../bootstrap.js';

// FontAwesome
import '@fortawesome/fontawesome-free/js/all.min';
//Bootstrap JS
// to customize look at documentation here https://github.com/twbs/bootstrap-npm-starter/blob/main/assets/js/starter.js
import * as bootstrap from 'bootstrap'
import Counter from './components/counter.js';

window.addEventListener('DOMContentLoaded', event => {
    let counter = Counter('#flip','nextEncounter');//.play();
    // counter.buildCounter();
    counter.play();

    // Style the Counter
    let tickElement = document.querySelector('#flip');
    let numbersElement = tickElement.querySelectorAll('.countdown__num > span');
    let labelElement = tickElement.querySelectorAll('.countdown__label');

    numbersElement.forEach((element) => {
        console.log(element);
        element.classList.add('display-2');
    });

    labelElement.forEach((element) => {
        console.log(element)
        element.classList.add('lead');
    });
})
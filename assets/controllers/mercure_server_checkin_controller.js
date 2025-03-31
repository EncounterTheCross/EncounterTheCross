import { Controller } from '@hotwired/stimulus';
import { getComponent } from '@symfony/ux-live-component';
import { useDispatch } from 'stimulus-use';

/*
 * This is an example Stimulus controller!
 *
 * Any element with a data-controller="hello" attribute will cause
 * this controller to be executed. The name "hello" comes from the filename:
 * hello_controller.js -> "hello"
 *
 * Delete this file or adapt it for your use!
 */
export default class extends Controller {
    // Declare the values you want to receive from the template
    static values = {
        mercureUrl: String
    }

    // async initialize() {
    //     this.component = await getComponent(this.element);
    // }

    connect() {
        this.eventSource = new EventSource(this.mercureUrlValue);
        this.eventSource.onmessage = event => {
            let data = JSON.parse(event.data);
            console.log('TOLD ABOUT:', data);

            // Dispatch a custom event that other controllers can listen to
            let btnElm = document.getElementById('participant-'+data.participant_id+'-checkin-button');
            let statusElm = document.getElementById('participant-'+data.participant_id+'-checkin-status');
            let prayerTeamElm = document.getElementById('participant-'+data.participant_id+'-checkin-prayerteam');
            let paymentElm = document.getElementById('participant-'+data.participant_id+'-checkin-ispaid');

            if (data.checkedIn) {
                btnElm.innerHTML = '<button\n' +
                    '                        class="js_check-in-button px-6 py-3 text-white flex items-center space-x-1 bg-blue-400 cursor-not-allowed font-bold rounded text-sm"\n' +
                    '                        disabled\n' +
                    '                >Check In\n' +
                    '                </button>';

                statusElm.innerHTML = '<div class="flex items-center text-green-600">\n' +
                    '                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">\n' +
                    '                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>\n' +
                    '                    </svg>\n' +
                    '                    Checked In\n' +
                    '                </div>';
            }

            if (data.prayerTeam !== null) {
                prayerTeamElm.innerHTML = '<div class="text-base font-medium text-gray-500 mt-0.5">\n' +
                    data.prayerTeam +
                    '                </div>';
            }

            if (data.isPaid) {
                paymentElm.innerHTML = '<div class="text-green-600 flex items-center">\n' +
                    '                            <span class="w-1.5 h-1.5 bg-green-600 rounded-full mr-1"></span>\n' +
                    '                            Paid\n' +
                    '                        </div>';
            }
        }
    }

    disconnect() {
        // Clean up the EventSource when the controller disconnects
        if (this.eventSource) {
            this.eventSource.close();
        }
    }

}

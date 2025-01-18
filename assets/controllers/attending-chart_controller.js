import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static values = {
        totalAttendees: Number,
        totalServers: Number
    }

    connect() {

        // Bind the methods to maintain correct 'this' context
        // this._onPreConnect = this._onPreConnect.bind(this);
        // this._onConnect = this._onConnect.bind(this);

        this.element.addEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.addEventListener('chartjs:connect', this._onConnect);
    }

    disconnect() {
        this.element.removeEventListener('chartjs:pre-connect', this._onPreConnect);
        this.element.removeEventListener('chartjs:connect', this._onConnect);
    }

    _onPreConnect(event) {
        const totalAttendees = event.detail.config.data.datasets[0].data[0];
        const totalServers = event.detail.config.data.datasets[0].data[1];

        if (!event.detail.config.options) {
            event.detail.config.options = {};
        }

        if (!event.detail.config.options.plugins) {
            event.detail.config.options.plugins = {};
        }

        event.detail.config.options.plugins.tooltip = {
            callbacks: {
                label: (context) => {  // Changed to arrow function
                    let label = context.dataset.labels[context.dataIndex]
                    const value = context.raw;
                    let percentage = 0;

                    try {
                        if (context.datasetIndex === 0) {
                            const total = (totalAttendees + totalServers);
                            percentage = ((value / total) * 100).toFixed(1);
                        } else {
                            label += context.datasetIndex === 1 ? ' (Attendees)' : ' (Servers)'
                            const total = context.datasetIndex === 1 ? totalAttendees : totalServers;
                            percentage = ((value / total) * 100).toFixed(1);
                        }
                    } catch (error) {
                        console.error('Error calculating percentage:', error);
                        percentage = 0;
                    }

                    return `${label}: ${value} (${percentage}%)`;
                }
            }
        };

    }

    _onConnect(event) {
        console.log('Chart connected', {
            totalAttendees: this.totalAttendeesValue,
            totalServers: this.totalServersValue
        });
    }
}
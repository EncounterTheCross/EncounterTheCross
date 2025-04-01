import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ['dialog', 'dynamicContent'];
    static values = {
        participantId: String
    }

    observer = null;
    connect() {
        if (this.hasDynamicContentTarget) {
            // when the content changes, call this.open()
            this.observer = new MutationObserver(() => {
                const shouldOpen = this.dynamicContentTarget.innerHTML.trim().length > 0;
                if (shouldOpen && !this.dialogTarget.open) {
                    this.open();
                } else if (!shouldOpen && this.dialogTarget.open) {
                    this.close();
                }
            });
            this.observer.observe(this.dynamicContentTarget, {
                childList: true,
                characterData: true,
                subtree: true
            });
        }

        // Use a bound method to properly handle event filtering
        this.boundCloseHandler = this.handleCloseModal.bind(this);
        document.addEventListener('close-modal', this.boundCloseHandler);
    }

    handleCloseModal(event) {
        // console.log('close-modal event received', event.detail);

        // Only close if this event is meant for this modal
        // or if no specific participantId was provided
        if (!event.detail.participantId ||
            (this.hasParticipantIdValue && event.detail.participantId == this.participantIdValue)) {
            console.log('Closing modal for participant', this.participantIdValue);
            this.close();
        }
    }

    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
        document.removeEventListener('close-modal', this.boundCloseHandler);

        if (this.dialogTarget.open) {
            this.close();
        }
    }

    open() {
        this.dialogTarget.showModal();
    }

    close() {
        if (this.hasDialogTarget) {
            this.dialogTarget.close();
        }
    }

    clickOutside(event) {
        if (event.target === this.dialogTarget) {
            this.dialogTarget.close();
        }
    }
}
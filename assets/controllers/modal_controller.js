import { Controller } from '@hotwired/stimulus';
export default class extends Controller {
    static targets = ['dialog', 'dynamicContent'];

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

        this.element.addEventListener('close-modal', () => {
            console.log('CLOSE WINDOW CALLED')
            this.close();
        });
    }
    disconnect() {
        if (this.observer) {
            this.observer.disconnect();
        }
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
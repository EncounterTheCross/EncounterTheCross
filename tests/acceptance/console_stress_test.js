// Simultaneously click multiple check-in buttons
function stressTest(count = 5) {
    const buttons = document.querySelectorAll('.js_check-in-button:not([disabled])');
    if (buttons.length === 0) {
        console.log('No check-in buttons found');
        return;
    }

    console.log(`Found ${buttons.length} check-in buttons, clicking ${Math.min(count, buttons.length)}`);

    for (let i = 0; i < Math.min(count, buttons.length); i++) {
        buttons[i].click();

        // After modal opens, find and click the paid button
        setTimeout(() => {
            const paidButtons = document.querySelectorAll('dialog button[data-live-action-param="paid"]');
            if (paidButtons[i]) paidButtons[i].click();
        }, 300);
    }
}

// Run the test with 10 simultaneous check-ins
stressTest(10);
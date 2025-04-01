const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');
const yargs = require('yargs/yargs');
const { hideBin } = require('yargs/helpers');

// Parse command line arguments
const argv = yargs(hideBin(process.argv))
    .option('token', {
        alias: 't',
        description: 'Event token for the check-in page',
        type: 'string',
        demandOption: true
    })
    .option('browsers', {
        alias: 'b',
        description: 'Number of concurrent browsers to run',
        type: 'number',
        default: 5
    })
    .option('checkinsPerBrowser', {
        alias: 'c',
        description: 'Number of check-ins per browser (1-3)',
        type: 'number',
        default: 2,
        choices: [1, 2, 3]
    })
    .option('delay', {
        alias: 'd',
        description: 'Delay between operations in ms to simulate real user behavior',
        type: 'number',
        default: 500
    })
    .option('timeout', {
        description: 'Timeout for operations in ms',
        type: 'number',
        default: 30000
    })
    .option('headless', {
        description: 'Run in headless mode',
        type: 'boolean',
        default: true
    })
    .option('output', {
        alias: 'o',
        description: 'Output file for results',
        type: 'string',
        default: 'stress-test-results.json'
    })
    .option('debug', {
        description: 'Enable debug mode with extra logging',
        type: 'boolean',
        default: false
    })
    .help()
    .alias('help', 'h')
    .argv;

// Configuration from command line arguments
const config = {
    eventToken: argv.token,
    concurrentBrowsers: argv.browsers,
    checkinsPerBrowser: argv.checkinsPerBrowser,
    operationDelay: argv.delay,
    operationTimeout: argv.timeout,
    headless: argv.headless,
    outputFile: argv.output,
    baseUrl: process.env.BASE_URL || 'http://localhost:8000',
    debug: argv.debug
};

// Debug logging function
function debug(message) {
    if (config.debug) {
        console.log(`[DEBUG] ${message}`);
    }
}

// Log environment information
debug(`Node version: ${process.version}`);
debug(`Working directory: ${process.cwd()}`);
debug(`Puppeteer executable path: ${process.env.PUPPETEER_EXECUTABLE_PATH || 'default'}`);
debug(`Environment variables: ${JSON.stringify(process.env)}`);

// Utility function to add random delay to simulate real user behavior
const randomDelay = async (minMs = config.operationDelay * 0.5, maxMs = config.operationDelay * 1.5) => {
    const delay = Math.floor(Math.random() * (maxMs - minMs + 1)) + minMs;
    await new Promise(resolve => setTimeout(resolve, delay));
};

// Utility function to format time in seconds with 2 decimal places
const formatTime = (timeMs) => (timeMs / 1000).toFixed(2);

// Main function to run a single browser instance for check-ins
async function runBrowserInstance(instanceId) {
    const results = {
        instanceId,
        checkIns: [],
        startTime: Date.now(),
        endTime: null,
        totalDuration: null,
        success: false,
        error: null
    };

    debug(`Instance ${instanceId}: Starting browser launch`);
    let browser;

    try {
        // Simplified browser launch configuration that works with Alpine Linux
        const launchOptions = {
            headless: "new",
            executablePath: process.env.PUPPETEER_EXECUTABLE_PATH,
            args: [
                '--no-sandbox',
                '--disable-setuid-sandbox',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                "--ignore-certificate-errors"
            ]
        };

        debug(`Launch options: ${JSON.stringify(launchOptions)}`);
        browser = await puppeteer.launch(launchOptions);
        debug(`Instance ${instanceId}: Browser launched successfully`);

        const page = await browser.newPage();
        debug(`Instance ${instanceId}: New page created`);

        // Navigate to the check-in page
        console.log(`Instance ${instanceId}: Navigating to check-in page...`);
        const checkInUrl = `${config.baseUrl}/checkin/${config.eventToken}`;
        debug(`Instance ${instanceId}: Navigating to ${checkInUrl}`);

        const navigationStart = Date.now();
        await page.goto(checkInUrl, { waitUntil: 'networkidle2', timeout: config.operationTimeout });
        const navigationTime = Date.now() - navigationStart;

        console.log(`Instance ${instanceId}: Page loaded in ${formatTime(navigationTime)}s`);

        // Get page title for debugging
        const title = await page.title();
        debug(`Instance ${instanceId}: Page title: "${title}"`);

        // Take a screenshot if in debug mode
        if (config.debug) {
            const screenshotPath = `./results/debug-instance-${instanceId}-initial.png`;
            await page.screenshot({ path: screenshotPath, fullPage: true });
            debug(`Instance ${instanceId}: Screenshot saved to ${screenshotPath}`);
        }

        // Perform check-ins
        for (let i = 0; i < config.checkinsPerBrowser; i++) {
            const checkInResult = {
                checkInId: i + 1,
                startTime: Date.now(),
                endTime: null,
                duration: null,
                success: false,
                participantName: null,
                paymentOption: null,
                error: null
            };

            try {
                console.log(`Instance ${instanceId}: Starting check-in #${i + 1}...`);

                // Find a participant who hasn't checked in yet
                await randomDelay();
                debug(`Instance ${instanceId}: Looking for check-in buttons`);
                const checkInButtons = await page.$$('.js_check-in-button');
                debug(`Instance ${instanceId}: Found ${checkInButtons.length} check-in buttons`);

                if (checkInButtons.length === 0) {
                    throw new Error('No check-in buttons found. All participants might be checked in already.');
                }

                // Get a random check-in button
                const buttonIndex = Math.floor(Math.random() * checkInButtons.length);
                const checkInButton = checkInButtons[buttonIndex];
                debug(`Instance ${instanceId}: Selected button index ${buttonIndex}`);

                // Try to get participant name for logging
                // try {
                //     // Get the participant row that contains the check-in button
                //     const participantRow = await checkInButton.evaluateHandle(el => el.closest('tr'));
                //
                //     // Use page.evaluate to work with the row element and extract the name
                //     const participantName = await page.evaluate(row => {
                //         const nameCell = row.querySelector('td'); // Get the first cell (td)
                //         return nameCell ? nameCell.textContent.trim() : null;
                //     }, participantRow);
                //
                //     if (participantName) {
                //         checkInResult.participantName = participantName;
                //     } else {
                //         checkInResult.participantName = `Unknown-${instanceId}-${i}`;
                //     }
                // } catch (error) {
                //     // Continue even if we can't get the name
                //     debug(`Instance ${instanceId}: Could not get participant name: ${error.message}`);
                //     checkInResult.participantName = `Unknown-${instanceId}-${i}`;
                // }
                checkInResult.participantName = `Unknown-${instanceId}-${i}`;

                console.log(`Instance ${instanceId}: Checking in participant: ${checkInResult.participantName}`);

                // Click the check-in button to open the modal
                debug(`Instance ${instanceId}: Clicking check-in button`);
                await checkInButton.click();

                // Wait for the dialog to appear
                debug(`Instance ${instanceId}: Waiting for dialog to appear`);
                await page.waitForSelector('dialog[open]', { visible: true, timeout: config.operationTimeout });
                debug(`Instance ${instanceId}: Dialog appeared`);

// Take screenshot of dialog if in debug mode
                if (config.debug) {
                    const screenshotPath = `./results/debug-instance-${instanceId}-modal-${i+1}.png`;
                    await page.screenshot({ path: screenshotPath });
                    debug(`Instance ${instanceId}: Dialog screenshot saved to ${screenshotPath}`);
                }

                await randomDelay();

// Select a payment option randomly
                // List all buttons in the dialog for debugging
                // const dialogButtons = await page.$$('dialog[open] button');
                // debug(`Instance ${instanceId}: Found ${dialogButtons.length} buttons in dialog`);
                //
                // for (let i = 0; i < dialogButtons.length; i++) {
                //     const buttonText = await page.evaluate(el => el.textContent.trim(), dialogButtons[i]);
                //     const buttonAttrs = await page.evaluate(el => {
                //         const attrs = {};
                //         for (const attr of el.attributes) {
                //             attrs[attr.name] = attr.value;
                //         }
                //         return attrs;
                //     }, dialogButtons[i]);
                //     debug(`Instance ${instanceId}: Button ${i+1}: Text="${buttonText}", Attributes=${JSON.stringify(buttonAttrs)}`);
                // }

                const paymentOptions = [
                    '.js_button-at-encounter',
                    '.js_button-paid'
                ];
                const selectedOption = paymentOptions[Math.floor(Math.random() * paymentOptions.length)];
                checkInResult.paymentOption = selectedOption.includes('paid') ? 'Already Paid' : 'Pay at Encounter';

                console.log(`Instance ${instanceId}: Selecting payment option: ${checkInResult.paymentOption}`);
                debug(`Instance ${instanceId}: Looking for payment button with selector: button${selectedOption}`);

                const paymentButton = await page.$(`button${selectedOption}`);
                if (!paymentButton) {
                    throw new Error(`Payment option button with ${selectedOption} not found`);
                }

                debug(`Instance ${instanceId}: Payment button found, clicking it`);
                await paymentButton.click();

// Wait for the dialog to close (confirmation)
                debug(`Instance ${instanceId}: Waiting for dialog to close`);
                await page.waitForFunction(
                    () => !document.querySelector('dialog[open]'),
                    { timeout: config.operationTimeout }
                );
                debug(`Instance ${instanceId}: Dialog closed`);

                // Verify the check-in was successful (button should be disabled or removed)
                await randomDelay();

                debug(`Instance ${instanceId}: Verifying check-in success`);
                const buttonStillActive = await page.evaluate((buttonIndex) => {
                    const buttons = document.querySelectorAll('.js_check-in-button');
                    return buttons[buttonIndex] && !buttons[buttonIndex].disabled;
                }, buttonIndex);

                if (buttonStillActive) {
                    throw new Error('Check-in might not have been successful, button is still active');
                }

                if (config.debug) {
                    const screenshotPath = `./results/debug-instance-success-${instanceId}-modal-${i+1}.png`;
                    await page.screenshot({ path: screenshotPath });
                    debug(`Instance ${instanceId}: Dialog screenshot saved to ${screenshotPath}`);
                }

                checkInResult.success = true;
                console.log(`Instance ${instanceId}: Check-in #${i + 1} successful`);

            } catch (error) {
                checkInResult.error = error.message;
                console.error(`Instance ${instanceId}: Error during check-in #${i + 1}:`, error.message);

                // Take error screenshot if in debug mode
                if (config.debug) {
                    try {
                        const screenshotPath = `./results/debug-instance-${instanceId}-error-${i+1}.png`;
                        await page.screenshot({ path: screenshotPath, fullPage: true });
                        debug(`Instance ${instanceId}: Error screenshot saved to ${screenshotPath}`);
                    } catch (screenshotError) {
                        debug(`Instance ${instanceId}: Could not take error screenshot: ${screenshotError.message}`);
                    }
                }
            } finally {
                checkInResult.endTime = Date.now();
                checkInResult.duration = checkInResult.endTime - checkInResult.startTime;
                results.checkIns.push(checkInResult);

                console.log(`Instance ${instanceId}: Check-in #${i + 1} completed in ${formatTime(checkInResult.duration)}s`);

                // Add delay between check-ins
                if (i < config.checkinsPerBrowser - 1) {
                    await randomDelay(config.operationDelay, config.operationDelay * 2);
                }
            }
        }

        results.success = results.checkIns.every(ci => ci.success);

    } catch (error) {
        results.error = error.message;
        console.error(`Instance ${instanceId}: Fatal error:`, error.message);
        debug(`Instance ${instanceId}: Stack trace: ${error.stack}`);
    } finally {
        if (browser) {
            debug(`Instance ${instanceId}: Closing browser`);
            await browser.close();
            debug(`Instance ${instanceId}: Browser closed`);
        }
        results.endTime = Date.now();
        results.totalDuration = results.endTime - results.startTime;
        console.log(`Instance ${instanceId}: Completed in ${formatTime(results.totalDuration)}s`);
        return results;
    }
}

// Create results directory if it doesn't exist
function ensureResultsDirectory() {
    const outputDir = path.dirname(config.outputFile);
    if (outputDir !== '.' && !fs.existsSync(outputDir)) {
        debug(`Creating output directory: ${outputDir}`);
        fs.mkdirSync(outputDir, { recursive: true });
    }
}

// Main function to run the stress test
async function runStressTest() {
    console.log(`
=================================
   SYMFONY CHECK-IN STRESS TEST
=================================
Configuration:
- Event Token: ${config.eventToken}
- Concurrent Browsers: ${config.concurrentBrowsers}
- Check-ins Per Browser: ${config.checkinsPerBrowser}
- Operation Delay: ${config.operationDelay}ms
- Base URL: ${config.baseUrl}
- Headless Mode: ${config.headless ? 'Yes' : 'No'}
- Debug Mode: ${config.debug ? 'Yes' : 'No'}
`);

    // Ensure results directory exists
    ensureResultsDirectory();

    const startTime = Date.now();
    const totalCheckIns = config.concurrentBrowsers * config.checkinsPerBrowser;

    console.log(`Starting stress test with ${config.concurrentBrowsers} browsers for a total of ${totalCheckIns} check-ins...`);

    // Create array of browser instance IDs
    const instanceIds = Array.from({ length: config.concurrentBrowsers }, (_, i) => i + 1);

    // Run browser instances concurrently
    const results = await Promise.all(instanceIds.map(id => runBrowserInstance(id)));

    const endTime = Date.now();
    const totalDuration = endTime - startTime;

    // Aggregate results
    const allCheckIns = results.flatMap(r => r.checkIns);
    const successfulCheckIns = allCheckIns.filter(ci => ci.success);
    const failedCheckIns = allCheckIns.filter(ci => !ci.success);
    const successRate = (successfulCheckIns.length / allCheckIns.length) * 100;

    const checkInTimes = successfulCheckIns.map(ci => ci.duration);
    const avgCheckInTime = checkInTimes.reduce((sum, time) => sum + time, 0) / (checkInTimes.length || 1);
    const minCheckInTime = Math.min(...(checkInTimes.length ? checkInTimes : [0]));
    const maxCheckInTime = Math.max(...(checkInTimes.length ? checkInTimes : [0]));

    const summary = {
        config,
        testDate: new Date().toISOString(),
        totalDuration,
        totalBrowsers: config.concurrentBrowsers,
        totalCheckIns: allCheckIns.length,
        successfulCheckIns: successfulCheckIns.length,
        failedCheckIns: failedCheckIns.length,
        successRate: successRate.toFixed(2),
        checkInTimes: {
            average: formatTime(avgCheckInTime),
            min: formatTime(minCheckInTime),
            max: formatTime(maxCheckInTime)
        },
        detailedResults: results
    };

    // Print summary
    console.log(`
=================================
   STRESS TEST RESULTS SUMMARY
=================================
Test completed in: ${formatTime(totalDuration)}s
Total check-ins: ${allCheckIns.length}
Successful check-ins: ${successfulCheckIns.length}
Failed check-ins: ${failedCheckIns.length}
Success rate: ${successRate.toFixed(2)}%

Check-in times (seconds):
- Average: ${formatTime(avgCheckInTime)}s
- Minimum: ${formatTime(minCheckInTime)}s
- Maximum: ${formatTime(maxCheckInTime)}s
`);

    // Save detailed results to file
    debug(`Saving results to ${config.outputFile}`);
    fs.writeFileSync(
        config.outputFile,
        JSON.stringify(summary, null, 2)
    );

    console.log(`Detailed results saved to ${config.outputFile}`);
}

// Run the stress test
runStressTest()
    .then(() => {
        console.log('Stress test completed successfully');
        process.exit(0);
    })
    .catch(error => {
        console.error('Stress test failed:', error);
        console.error(error.stack);
        process.exit(1);
    });
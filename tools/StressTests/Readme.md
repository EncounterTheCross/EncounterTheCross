# Symfony Check-in Stress Test Docker Setup

This guide provides a simple and reliable way to run the Puppeteer-based stress test for your Symfony Live Component check-in functionality using Docker containers.

## Quick Setup

1. Create a new directory for the project:
```bash
mkdir symfony-stress-test && cd symfony-stress-test
```

2. Create the following files (copy them from the artifacts in this conversation):
    - `Dockerfile` - Uses Alpine Linux with pre-installed Chromium
    - `docker-compose.yml` - Configuration for the Docker service
    - `package.json` - Node.js dependencies with specific Puppeteer version
    - `stress-test.js` - The test script itself (use the complete version provided)
    - `run-test.sh` - Helper script to run the tests

3. Make the run script executable:
```bash
chmod +x run-test.sh
```

4. Create a results directory:
```bash
mkdir -p results
```

5. Build the Docker image:
```bash
docker-compose build
```

## Running the Test

Run the test using the provided script:

```bash
./run-test.sh YOUR_EVENT_TOKEN [number_of_browsers]
```

For example:
```bash
./run-test.sh abc123 5
```

This will:
- Run the test with 5 concurrent browsers
- Save the results to the `results` directory with a timestamp
- Enable debug mode for detailed logging

## Configuration

You can modify the following settings in `docker-compose.yml`:

- `BASE_URL`: The URL of your Symfony application
- `browsers`: Number of concurrent browsers (default: 5)
- `checkinsPerBrowser`: Check-ins per browser (default: 2)
- `delay`: Delay between operations in ms (default: 500)

## Troubleshooting

If you encounter any issues:

1. **Check debug logs**: The test runs with debug mode enabled by default, which provides detailed logs about each step.

2. **Review screenshots**: When running in debug mode, the test takes screenshots at key points (initial page load, modal open, errors) and saves them to the results directory.

3. **Memory issues**: If you encounter memory errors, try reducing the number of concurrent browsers by specifying a smaller number in the run script.

4. **SSL/TLS errors**: If your application uses HTTPS with a self-signed certificate, you may need to add the `--ignore-certificate-errors` flag to the browser launch options in `stress-test.js`.

## Understanding Results

The test produces a JSON file with detailed metrics including:

- Overall success rate
- Time taken for each check-in
- Average, minimum, and maximum check-in times
- Detailed logs of any errors encountered

You can use this data to identify performance bottlenecks in your application when handling concurrent check-ins.
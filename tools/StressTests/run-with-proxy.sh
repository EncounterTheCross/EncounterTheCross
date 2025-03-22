#!/bin/bash

# Check if token is provided
if [ -z "$1" ]; then
  echo "Usage: ./run-with-proxy.sh YOUR_EVENT_TOKEN [number_of_browsers]"
  exit 1
fi

TOKEN=$1
BROWSERS=${2:-2}  # Default to 2 browsers if not specified

# Create results directory if it doesn't exist
mkdir -p results

# Ensure the host entry is in /etc/hosts inside the container
echo "Setting up for reverse proxy access..."

# Build the Docker image if needed
if [[ "$(docker images -q stresstests-stress-test 2> /dev/null)" == "" ]]; then
  echo "Building Docker image..."
  docker-compose build
fi

# Run with host network and DNS setup
echo "Running stress test with $BROWSERS browsers..."
docker run --rm \
  --network=host \
  --add-host=encounterthecross.test:host-gateway \
  -v $(pwd)/results:/app/results \
  -e BASE_URL=https://encounterthecross.test \
  stresstests-stress-test \
  --token $TOKEN \
  --browsers $BROWSERS \
  --checkinsPerBrowser 1 \
  --delay 500 \
  --output /app/results/stress-test-results-$(date +%Y%m%d-%H%M%S).json \
  --debug true

echo "Test complete! Check the results directory for output."
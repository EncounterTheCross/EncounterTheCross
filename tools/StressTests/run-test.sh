#!/bin/bash

# Check if token is provided
if [ -z "$1" ]; then
  echo "Usage: ./run-test.sh YOUR_EVENT_TOKEN [number_of_browsers]"
  exit 1
fi

TOKEN=$1
BROWSERS=${2:-5}  # Default to 5 browsers if not specified

# Create results directory if it doesn't exist
mkdir -p results

# Run the stress test with the provided token
docker-compose run --rm stress-test \
  --token $TOKEN \
  --browsers $BROWSERS \
  --output /app/results/stress-test-results-$(date +%Y%m%d-%H%M%S).json
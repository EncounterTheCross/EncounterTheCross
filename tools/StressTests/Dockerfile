FROM alpine:3.17

# Install Node.js, npm and Chromium
RUN apk add --no-cache \
    nodejs \
    npm \
    chromium \
    nss \
    freetype \
    harfbuzz \
    ca-certificates \
    ttf-freefont \
    fontconfig

# Set up environment variables for Puppeteer
ENV PUPPETEER_SKIP_CHROMIUM_DOWNLOAD=true
ENV PUPPETEER_EXECUTABLE_PATH=/usr/bin/chromium-browser

# Set working directory
WORKDIR /app

# Copy package.json and install dependencies
COPY package.json ./
RUN npm install

# Copy stress test script
COPY stress-test.js ./

# Create results directory with proper permissions
RUN mkdir -p /app/results && chmod 777 /app/results

# Set default command
ENTRYPOINT ["node", "stress-test.js"]
CMD ["--help"]
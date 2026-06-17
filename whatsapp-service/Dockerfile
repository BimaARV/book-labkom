FROM node:20-alpine

# Install system dependencies required for some native Node modules
RUN apk add --no-cache git tzdata

# Set timezone
ENV TZ=Asia/Jakarta
RUN cp /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

WORKDIR /app

# Copy package files
COPY package*.json ./

# Install dependencies
RUN npm install --omit=dev

# Copy source code
COPY . .

# Set up directory for session data and set permissions
RUN mkdir -p baileys_auth_info && chown -R node:node /app

# Switch to non-root user
USER node

EXPOSE 3000

CMD ["node", "index.js"]

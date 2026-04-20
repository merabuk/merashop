##################################
# BASE STAGE (OS Packages)
##################################
FROM node:20-alpine3.21 AS base

WORKDIR /app

# Install the basic system dependencies if they are required by all
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache \
        bash

##################################
# BUILD STAGE (NPM Dependencies)
##################################
FROM base AS build

COPY package.json package-lock.json* ./

# Install all dependencies (including devDependencies for linters)
RUN --mount=type=cache,target=/root/.npm \
    npm install

##################################
# LOCAL ENVIRONMENT (Development)
##################################
FROM build AS local

# Installing Git ONLY for local development
RUN --mount=type=cache,target=/var/cache/apk \
    apk add --no-cache \
        git

# Копіюємо весь код (хоча для локалки він зазвичай прокидається через volumes)
COPY ./ ./

CMD ["npm", "run", "dev", "--", "--host", "0.0.0.0"]

##################################
# PRODUCTION ENVIRONMENT
##################################
FROM base AS prod

COPY package.json package-lock.json* ./

# Install ONLY production dependencies
RUN --mount=type=cache,target=/root/.npm \
    npm install --omit=dev

COPY ./ ./
RUN npm run build

CMD ["npm", "run", "preview"]

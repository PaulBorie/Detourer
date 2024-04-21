#!/bin/bash

shopt -s expand_aliases

alias docker-compose="docker compose"

# Check if the script is run as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run this script as root or with sudo."
    exit 1
fi

read -p "Are you uninstalling locally or in production? (local/prod): " ENVIRONMENT

if [ "$ENVIRONMENT" != "local" ] && [ "$ENVIRONMENT" != "prod" ]; then
    echo "Invalid environment. Exiting."
    exit 1
fi

if [ "$ENVIRONMENT" != "local" ]; then
    # Prompt the user
    read -p "Uninstall will remove all data and it will not be recoverable. Save data before. Are you sure to uninstall? Y/n: " yn
    case $yn in
        [Yy]* ) ;;
        * ) echo "Uninstall cancelled."; exit;;
    esac
fi

if [ "$ENVIRONMENT" = "prod" ]; then
    ENV_FILE="/var/lib/rembg/prod.env"
else
    ENV_FILE="/var/lib/rembg/local.env"
fi

docker-compose --env-file "$ENV_FILE" -f "${ENVIRONMENT}.docker-compose.yaml" down -v --remove-orphans
rm $ENV_FILE


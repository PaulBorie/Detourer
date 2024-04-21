#!/bin/bash

shopt -s expand_aliases

alias docker-compose="docker compose"

# utils functions

random_string() {
    local size="$1"
    tr -dc 'a-zA-Z0-9' < /dev/urandom | head -c "$size"
}

random_int() {
    local min=100000
    local max=999999
    echo $(($RANDOM%($max-$min+1)+$min))
}

# Check if the script is run as root
if [ "$EUID" -ne 0 ]; then
    echo "Please run this script as root or with sudo."
    exit 1
fi

docker login

read -p "Are you installing locally or in production? (local/prod): " ENVIRONMENT

if [ "$ENVIRONMENT" != "local" ] && [ "$ENVIRONMENT" != "prod" ]; then
    echo "Invalid environment. Exiting."
    exit 1
fi

# Set VITE_REVERB_SCHEME based on the deployment environment
if [ "$ENVIRONMENT" = "local" ]; then
    VITE_REVERB_SCHEME="http"
elif [ "$ENVIRONMENT" = "prod" ]; then
    VITE_REVERB_SCHEME="https"
fi

# Check if the file /var/lib/rembg/$ENVIRONMENT.env exists
ENV_FILE="/var/lib/rembg/$ENVIRONMENT.env"
OVERRIDE=N
if [ -f "$ENV_FILE" ]; then
    # File exists
    echo "The file $ENV_FILE already exists:"
    cat "$ENV_FILE"
    read -p "Do you want to override these settings? (y/N): " OVERRIDE
    OVERRIDE=$(echo "$OVERRIDE" | tr '[:upper:]' '[:lower:]') # Convert to lowercase
    while [ "$OVERRIDE" != "y" ] && [ "$OVERRIDE" != "n" ]; do
        read -p "Please enter 'Y' for yes or 'N' for no: " OVERRIDE
        OVERRIDE=$(echo "$OVERRIDE" | tr '[:upper:]' '[:lower:]') 
    done
fi

if [ "$OVERRIDE" = "y" ] || [ ! -f "$ENV_FILE" ]; then
    # Prompt user to enter DOMAIN_NAME and NB_WORKERS
    read -p "Enter the domain name: " DOMAIN_NAME

    if [ -z "$DOMAIN_NAME" ]; then
        echo "Domain name cannot be empty. Exiting."
        exit 1 
    fi

    if [ "$ENVIRONMENT" = "local" ]; then
        read -p "Enter the number of workers (NB_WORKERS) [default is 1]: " NB_WORKERS
        NB_WORKERS=${NB_WORKERS:-1}  # Set default value to 1 if NB_WORKERS is not set
    fi

    if [ "$ENVIRONMENT" = "prod" ]; then
        NB_WORKERS=$(nproc)
    fi

    read -p "Enter the application user: " APP_USER

    if [ -z "$APP_USER" ]; then
        echo "Application user cannot be empty. Exiting."
        exit 1 
    fi

    read -s -p "Enter the application password: " APP_PASSWORD

    if [ -z "$APP_PASSWORD" ]; then
        echo "Application password cannot be empty. Exiting."
        exit 1 
    fi

    MYSQL_ROOT_PASSWORD=$(random_string 48)
    # APP_KEY=$(random_b64_string 44)
    APP_KEY="base64:$(openssl rand -base64 32)"
    AWS_ACCESS_KEY_ID=$(random_string 20)
    AWS_SECRET_ACCESS_KEY=$(random_string 20)
    REVERB_APP_ID=$(random_int 6)
    REVERB_APP_KEY=$(random_string 20)
    VITE_REVERB_APP_KEY=$REVERB_APP_KEY
    REVERB_APP_SECRET=$(random_string 20)

    # Write DOMAIN_NAME and NB_WORKERS to the file
    mkdir -p /var/lib/rembg
    echo "DOMAIN_NAME=$DOMAIN_NAME" > "$ENV_FILE"
    echo "NB_WORKERS=$NB_WORKERS" >> "$ENV_FILE"
    echo "APP_USER=$APP_USER" >> "$ENV_FILE"
    echo "APP_PASSWORD=$APP_PASSWORD" >> "$ENV_FILE" 
    echo "MYSQL_ROOT_PASSWORD=$MYSQL_ROOT_PASSWORD" >> "$ENV_FILE"
    echo "APP_KEY=$APP_KEY" >> "$ENV_FILE"
    echo "AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID" >> "$ENV_FILE"
    echo "AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY" >> "$ENV_FILE"
    echo "REVERB_APP_ID=$REVERB_APP_ID" >> "$ENV_FILE"
    echo "REVERB_APP_KEY=$REVERB_APP_KEY" >> "$ENV_FILE"
    echo "VITE_REVERB_APP_KEY=$VITE_REVERB_APP_KEY" >> "$ENV_FILE"
    echo "REVERB_APP_SECRET=$REVERB_APP_SECRET" >> "$ENV_FILE"

    if [ "$ENVIRONMENT" = "local" ]; then
        # Check if entries are present in /etc/hosts, if not, add them
        if ! grep -q "$DOMAIN_NAME" /etc/hosts; then
            echo "127.0.0.1       $DOMAIN_NAME" >> /etc/hosts
        fi

        if ! grep -q "minio.$DOMAIN_NAME" /etc/hosts; then
            echo "127.0.0.1       minio.$DOMAIN_NAME" >> /etc/hosts
        fi

        if ! grep -q "reverb.$DOMAIN_NAME" /etc/hosts; then
            echo "127.0.0.1       reverb.$DOMAIN_NAME" >> /etc/hosts
        fi

        if ! grep -q "adminio.$DOMAIN_NAME" /etc/hosts; then
            echo "127.0.0.1       adminio.$DOMAIN_NAME" >> /etc/hosts
        fi
    fi
else
    DOMAIN_NAME=$(grep "DOMAIN_NAME=" "$ENV_FILE" | cut -d '=' -f2)
    VITE_REVERB_APP_KEY=$(grep "VITE_REVERB_APP_KEY=" "$ENV_FILE" | cut -d '=' -f2)
fi

read -p "Do you want to rebuild the worker image? (y/N): " REBUILD_WORKER

REBUILD_WORKER=$(echo "$REBUILD_WORKER" | tr '[:upper:]' '[:lower:]') # Convert to lowercase
while [ "$REBUILD_WORKER" != "y" ] && [ "$REBUILD_WORKER" != "n" ]; do
    read -p "Please enter 'Y' for yes or 'N' for no: " REBUILD_WORKER
    REBUILD_WORKER=$(echo "$REBUILD_WORKER" | tr '[:upper:]' '[:lower:]') # Convert to lowercase
done

docker buildx build --build-arg VITE_REVERB_HOST="reverb.$DOMAIN_NAME" --build-arg VITE_REVERB_SCHEME="$VITE_REVERB_SCHEME" --build-arg VITE_REVERB_APP_KEY="$VITE_REVERB_APP_KEY" -t paulborielabs/rembg:$ENVIRONMENT -f FrankenPHP.Dockerfile .

docker push "paulborielabs/rembg:$ENVIRONMENT"

docker buildx build --build-arg ENVIRONMENT="$ENVIRONMENT" -t "paulborielabs/webserver:$ENVIRONMENT" -f Nginx.Dockerfile .

docker push "paulborielabs/webserver:$ENVIRONMENT"

if [ "$REBUILD_WORKER" = "y" ]; then
    # Build and push the worker
    docker buildx build -t paulborielabs/worker:latest -f Worker.Dockerfile .
    docker push paulborielabs/worker:latest
fi

docker-compose --env-file "$ENV_FILE" -f "$ENVIRONMENT.docker-compose.yaml" pull
docker-compose --env-file "$ENV_FILE" -f "$ENVIRONMENT.docker-compose.yaml" up -d

echo "App successfully installed for domain name $DOMAIN_NAME for $ENVIRONMENT environment."

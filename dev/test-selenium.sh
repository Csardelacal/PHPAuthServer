#!/bin/bash

export COMPOSE_PROJECT_NAME="phpas"

if (which docker-compose) then
	DC="docker-compose"
else
	DC="docker compose"
fi

DC="${DC} -f docker-compose.yml -f docker-compose-test-runner.yml"

# Reset all the containers. This allows us to start with a clean
# instance. Deletes all the databases and the config from the containers
${DC} down

WEB_TARGET=integration ${DC} up -d --build

# Todo: This needs to be replaced with proper migrations
# Start by collecting stats so the system creates all the tables it needs
${DC} run web php console admin stats

# Execute the test runner to start testing the application
COMPOSE_PROFILES=tools ${DC} run runner php main.php $1

# Stop the runner once we are done with it. Please note that I don't call down, this
# is so the state the application is left in can be used to continue testing.
${DC} stop

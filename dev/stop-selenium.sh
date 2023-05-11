#!/bin/bash

if (which docker-compose) then
	DC="docker-compose"
else
	DC="docker compose"
fi

DC="${DC} -f docker-compose.yml"
COMPOSE_PROFILES=tools ${DC} stop

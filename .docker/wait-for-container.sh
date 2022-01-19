#!/bin/bash

if [ $# -ne 1 ]; then
    echo "Usage: $0 <container-id>"
    exit 1
fi

function getContainerHealth {
    docker inspect --format "{{json .State.Health.Status }}" $1
}

while STATUS=$(getContainerHealth $1); [ "$STATUS" != '"healthy"' ]; do
    if [ -z "$STATUS" ]; then
        echo "Failed to retrieve status of docker container $1"
        exit 1
    fi
    if [ "$STATUS" == '"unhealthy"' ]; then
        echo "Failed to start container $1. See docker logs for details."
        exit 1
    fi
    printf '.'
    sleep 1
done
printf $'\n'

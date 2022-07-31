#!/bin/bash
# Use this script, if you are using docker compose v2
# and the images do not build.
#
# Heinrich Stamerjohanns
#
docker compose >/dev/null 2>/dev/null
if [[ "$?" != "0" ]]; then
	echo "docker compose v2 has not been setup. ";
    echo "Either just run "
    echo
	echo "docker-compose -f docker-compose.yml -f docker-compose-build.yml build"
	echo 
    echo "or read how to install docker compose v2 on"
    echo "https://github.com/docker/compose/tree/v2#linux"
	echo 
    exit 1
fi
docker pull alpine:3.16
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_base
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_git
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_dmake
docker compose -f docker-compose.yml -f docker-compose-build.yml build texmlbus

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
	echo "texmlbus-edge -f texmlbus-edge.yml -f texmlbus-edge-build.yml build"
	echo 
    echo "or read how to install docker compose v2 on"
    echo "https://github.com/docker/compose/tree/v2#linux"
	echo 
    exit 1
fi
docker pull alpine:edge
docker compose -f docker-compose.yml -f texmlbus-edge.yml -f texmlbus-edge-build.yml build latexml_base_edge
docker compose -f docker-compose.yml -f texmlbus-edge.yml -f texmlbus-edge-build.yml build latexml_git_edge
docker compose -f docker-compose.yml -f texmlbus-edge.yml -f texmlbus-edge-build.yml build latexml_edge
docker compose -f docker-compose.yml -f texmlbus-edge.yml -f texmlbus-edge-build.yml build latexml_dmake_edge
docker compose -f docker-compose.yml -f texmlbus-edge.yml -f texmlbus-edge-build.yml build texmlbus

#!/bin/bash
#
# Heinrich Stamerjohanns, 2020
#

echo "This will delete texmlbus docker containers, volumes!"
echo -n "Continue [Y/N] [n]: "
read n
if [[ ${n} != 'Y' && $n != 'y' ]]
then
    echo "Nothing has been deleted.";
    exit 0
fi

#
# https://stackoverflow.com/questions/45798076/how-to-clean-up-docker/45798680#45798680
#

echo "Killing running containers..."
docker kill $(docker ps | grep texmlbus_texmlbus | cut -f 1 -d " ")
docker kill $(docker ps | grep texmlbus_mariadb | cut -f 1 -d " ")
docker kill $(docker ps | grep texmlbus_latexml_dmake | cut -f 1 -d " ")

echo "Deleting all stopped containers..."
docker rm $(docker ps -a | grep texmlbus_texmlbus | cut -f 1 -d " ")
docker rm $(docker ps -a | grep texmlbus_mariadb | cut -f 1 -d " ")
docker rm $(docker ps -a | grep texmlbus_latexml_dmake | cut -f 1 -d " ")

echo "Deleting all images..."
docker rmi $(docker images | grep texmlbus_texmlbus | cut -f 1 -d " ")
docker rmi $(docker images | grep texmlbus_mariadb | cut -f 1 -d " ")
docker rmi $(docker images | grep texmlbus_latexml_dmake | cut -f 1 -d " ")
docker rmi $(docker images | grep texmlbus_latexml | cut -f 1 -d " ")

echo "Removing unused data..."
docker system prune

echo "And some more..."
docker system prune -af

# empty input buffer
read -d '' -t 0.1 -n 10000

echo -n "Remove dangling volumes [Y/N] [n]: "
read dn
if [[ ${dn} != 'Y' && $dn != 'y' ]]
then
    echo "No volume has been deleted.";
    exit 0
fi
echo "Removing dangling volumes..."
docker volume rm $(docker volume ls -f dangling=true -q)

echo "Done."


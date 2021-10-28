#!/bin/bash
#
# Heinrich Stamerjohanns, 2020
#
echo "This will delete ALL docker containers, volumes!"
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

echo "Killing all running containers..."
docker kill $(docker ps -q)

echo "Deleting all stopped containers..."
docker rm $(docker ps -a -q)

echo "Deleting all images..."
docker rmi $(docker images -q)

echo "Removing unused data..."
docker system prune

echo "And some more..."
docker system prune -af

# empty input buffer
read -d '' -t 0.1 -n 10000echo "And some more..."

echo -n "Remove dangling volumes [Y/N] [n]: "
read n
if [[ ${n} != 'Y' && $n != 'y' ]]
then
    echo "No volume has been deleted.";
    exit 0
fi
echo "Removing dangling volumes..."
docker volume rm $(docker volume ls -f dangling=true -q)

echo "Done."


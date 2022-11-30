#!/bin/bash
# Script to initialize the database for texbusml
# Container is removed, then volume
# Heinrich Stamerjohanns, 2020-05-17
#
SCRIPTDIR=$(dirname "$(readlink -f "$0")")

CONTAINER="mariadb"
# volume to be removed
VOLUME="texmlbus_data-mysql"

echo "This will reset your mysql db for buildsysdb!"
echo -n "Continue [Y/N] [n]: "
read n
if [[ ${n} != 'Y' && $n != 'y' ]]
then
    echo "Nothing has been deleted."
fi

echo "Deleting container ${CONTAINER}..."
docker-compose container rm "${CONTAINER}"

echo "Resetting database..."
docker volume rm -f "${VOLUME}"

echo "Done."

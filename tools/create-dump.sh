#!/bin/bash
SCRIPTDIR=`dirname "$(readlink -f "$0")"`
PWD=`pwd`
cd "${SCRIPTDIR}"
CONTAINER="mariadb"

DBVERSION=`docker-compose exec mariadb sh -c 'exec mysql buildsysdb -uroot -p"$MYSQL_ROOT_PASSWORD" --silent -e "select dbversion from dbversion"'`
echo "DB version: ${DBVERSION}"
LAST_UPGRADE=`ls ../volume/src/config/sql | tail -1`
echo "Last upgrade file: ${LAST_UPGRADE}"
echo "Both version should match..."
sleep 5

OUTFILE='../volume/db/init/buildsysdb.sql'
echo "Writing backup..."

# create all table, no data
docker-compose exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb --no-data -uroot -p"$MYSQL_ROOT_PASSWORD"' > ${OUTFILE}

#dump help contents
docker-compose exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb help --skip-extended-insert -uroot -p"$MYSQL_ROOT_PASSWORD"' >> ${OUTFILE}

# dump dbversion for current dbversion
docker-compose exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb dbversion -uroot -p"$MYSQL_ROOT_PASSWORD"' >> ${OUTFILE}

# fix AUTOINCREMENT_VALUES
sed -E -i -e 's/AUTO_INCREMENT=\w+/AUTO_INCREMENT=1/' ${OUTFILE}

echo 'Exported tables:'
grep "CREATE TABLE" ${OUTFILE} | sed -E -e 's/CREATE TABLE `(.*)` \(/\1/'

echo "Backup written to ${OUTFILE}"
echo "Done."
cd "${PWD}"

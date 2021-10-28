#!/bin/bash
SCRIPTDIR=`dirname "$(readlink -f "$0")"`
PWD=`pwd`
cd "${SCRIPTDIR}"
. ${SCRIPTDIR}/is-dc-v2.sh
is_dc_v2
if [[ "$?" == "1" ]]; then
    CONTAINER="texmlbus-mariadb-1"
else 
    CONTAINER="texmlbus_mariadb_1"
fi

DBVERSION=`docker exec texmlbus_mariadb_1 sh -c 'exec mysql buildsysdb -uroot -p"$MYSQL_ROOT_PASSWORD" --silent -e "select dbversion from dbversion"'`
echo "DB version: ${DBVERSION}"
LAST_UPGRADE=`ls ../volume/build/config/sql | tail -1`
echo "Last upgrade file: ${LAST_UPGRADE}"
echo "Both version should match..."
sleep 5

OUTFILE='../volume/db/init/buildsysdb.sql'
echo "Writing backup to ${OUTFILE}"

# create all table, no data
docker exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb --no-data -uroot -p"$MYSQL_ROOT_PASSWORD"' > ${OUTFILE}

#dump help contents
docker exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb help --skip-extended-insert -uroot -p"$MYSQL_ROOT_PASSWORD"' >> ${OUTFILE}

# dump dbversion for current dbversion
docker exec ${CONTAINER} sh -c 'exec mysqldump buildsysdb dbversion -uroot -p"$MYSQL_ROOT_PASSWORD"' >> ${OUTFILE}

# fix AUTOINCREMENT_VALUES
sed -E -i -e 's/AUTO_INCREMENT=\w+/AUTO_INCREMENT=1/' ${OUTFILE}

echo 'Exported tables:'
grep "CREATE TABLE" ${OUTFILE} | sed -E -e 's/CREATE TABLE `(.*)` \(/\1/'

cd "${PWD}"

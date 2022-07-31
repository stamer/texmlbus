#!/bin/bash
#
# Heinrich Stamerjohanns, 2020
#
# Enables the xdebug-extension on the server, which is disabled by default.
#
SCRIPTDIR=$(dirname "$(readlink -f "$0")")

if [[ $OS = "Windows_NT" ]]
then
    WINPTY="winpty"
else
    WINPTY=""
fi

. ${SCRIPTDIR}/is-dc-v2.sh

is_dc_v2
if [[ "$?" == "1" ]]; then
    CONTAINER="texmlbus-texmlbus-1"
else
    CONTAINER="texmlbus_texmlbus_1"
fi
#
# use double slashes so winpty does not convert paths
#
$WINPTY docker exec -it ${CONTAINER} //bin/sh -c 'sed -i "s/^;zend/zend/g" /etc/php81/conf.d/50_xdebug.ini; echo -e "xdebug.mode=debug\nxdebug.discover_client_host=true" >> /etc/php81/conf.d/50_xdebug.ini; killall -HUP httpd'

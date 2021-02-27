#!/bin/bash
#
# Heinrich Stamerjohanns, 2020
#
# Enables the xdebug-extension on the server, which is disabled by default.
#
if [[ $OS = "Windows_NT" ]]
then
    WINPTY="winpty"
else
    WINPTY=""
fi
#
# use double slashes so winpty does not convert paths
#
$WINPTY docker exec -it texmlbus_texmlbus_1 //bin/sh -c 'sed -i "s/^;zend/zend/g" /etc/php7/conf.d/50_xdebug.ini; echo -e "xdebug.mode=debug\nxdebug.discover_client_host=true" >> /etc/php7/conf.d/50_xdebug.ini; killall -HUP httpd'

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
$WINPTY docker exec -it texmlbus_texmlbus_1 //bin/sh -c 'sed -i "s/^;zend/zend/g" /etc/php7/conf.d/xdebug.ini; echo -e "xdebug.coverage_enable=0\nxdebug.remote_enable=1\nxdebug.remote_connect_back=1" >> /etc/php7/conf.d/xdebug.ini; killall -HUP httpd'

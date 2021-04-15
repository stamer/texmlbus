#!/bin/sh

#echo "Clearing any old processes..."
#rm -f /run/php-fpm/php-fpm.pid
if [[ ! -e /srv/texmlbus/build ]]; then
    ln -s /srv/texmlbus/src /srv/texmlbus/build
fi

echo "Starting php-fpm7..."
/usr/sbin/php-fpm7

echo "Clearing any old apache processes..."
rm -f /run/apache2/apache2.pid
rm -f /run/apache2/httpd.pid

echo "Starting apache..."
/usr/sbin/httpd -DFOREGROUND

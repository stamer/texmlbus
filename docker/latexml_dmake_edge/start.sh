#!/bin/sh

#echo "Clearing any old processes..."
#rm -f /run/php-fpm/php-fpm.pid
echo "Starting php-fpm7..."
/usr/sbin/php-fpm7

echo "Clearing any old apache processes..."
rm -f /run/apache2/apache2.pid
rm -f /run/apache2/httpd.pid

echo "Starting apache..."
/usr/sbin/httpd -DFOREGROUND

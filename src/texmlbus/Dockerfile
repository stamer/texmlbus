FROM alpine:3.11
#MAINTAINER Paul Smith <pa.ulsmith.net>
#See https://hub.docker.com/r/ulsmith/alpine-apache-php7

# modified to be used especially for texmlbus

#MAINTAINER Heinrich Stamerjohanns <heinrich@stamerjohanns.de>

# Add repos
#RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories

# Add basics first
RUN apk update && apk upgrade && apk add \
	bash apache2 apache2-ssl php7-apache2 curl ca-certificates openssl openssh git php7 php7-phar php7-json php7-iconv php7-openssl tzdata openntpd nano

# dmake specific stuff
RUN apk add \
    file \
    make \
    mysql-client \
    perl \
    sudo \
    unrar \
    zip

# Add Composer
RUN curl -sS https://getcomposer.org/installer | php && mv composer.phar /usr/local/bin/composer

# Setup apache and php
RUN apk add \
	php7-apcu \
	php7-bcmath \
	php7-bz2 \
	php7-ctype \
	php7-curl \
	php7-dom \
	php7-exif \
	php7-ftp \
	php7-gd \
	php7-gettext \
	php7-mbstring \
	php7-mysqli \
    php7-pcntl \
	php7-pdo \
	php7-pdo_mysql \
    php7-posix \
	php7-redis \
	php7-session \
	php7-soap \
	php7-tokenizer \
	php7-xdebug \
	php7-xml \
	php7-xmlreader \
	php7-xmlrpc \
	php7-xmlwriter \
	php7-zip

# only available in testing, but unstable
# php7-pecl-inotify
# therefore manually install via pecl

# needed for pecl
RUN apk add \
    php7-dev \
    php7-pear

RUN set -xe; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
      # build tools
      autoconf g++ gcc make \
      # lib tools
      #bzip2-dev freetype-dev gettext-dev icu-dev imagemagick-dev libintl libjpeg-turbo-dev \
      #libpng-dev libxslt-dev libzip-dev \
      ; \
    pecl channel-update pecl.php.net && \
    pecl install -o -f \
      inotify \
      ; \
    apk del .build-deps

RUN echo "extension=inotify.so" > /etc/php7/conf.d/inotify.ini

# Problems installing in above stack
RUN apk add php7-simplexml

RUN cp /usr/bin/php7 /usr/bin/php

RUN curl -L -s "http://mirrors.ctan.org/support/latexmk/latexmk.pl" -o /usr/bin/latexmk \
    && chmod ugo+rx /usr/bin/latexmk

# Add apache to run and configure
RUN sed -i "s/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ expires_module/LoadModule\ expires_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_module/LoadModule\ session_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_cookie_module/LoadModule\ session_cookie_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_crypto_module/LoadModule\ session_crypto_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ deflate_module/LoadModule\ deflate_module/" /etc/apache2/httpd.conf \
    && sed -i "s#^DocumentRoot \".*#DocumentRoot \"/srv/texmlbus/build/server/htdocs\"#g" /etc/apache2/httpd.conf \
    && sed -i "s#^DocumentRoot \".*#DocumentRoot \"/srv/texmlbus/build/server/htdocs\"#g" /etc/apache2/conf.d/ssl.conf \
    && sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/build/server/htdocs#" /etc/apache2/httpd.conf \
    && sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/build/server/htdocs#" /etc/apache2/conf.d/ssl.conf \
    && printf "\n<Directory \"/srv/texmlbus/build/server/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/httpd.conf \
    && printf "\n<Directory \"/srv/texmlbus/build/server/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf-8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions +Indexes -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/conf.d/ssl.conf \
    && printf "\nAlias \"/files\" \"/srv/texmlbus/articles\"\n" >> /etc/apache2/httpd.conf \
    && printf "\nAlias \"/files\" \"/srv/texmlbus/articles\"\n" >> /etc/apache2/ssl.conf \
    && printf "\n<Directory \"/srv/texmlbus/articles\">\nRequire all granted\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf-8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions +Indexes -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/httpd.conf \
    && printf "\n<Directory \"/srv/texmlbus/articles\">\nRequire all granted\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf-8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/conf.d/ssl.conf

COPY ssl/server.pem /etc/ssl/apache2/server.pem
COPY ssl/server.key /etc/ssl/apache2/server.key

ENV APACHE_SERVER_NAME=dmake


# setup dmake user
RUN addgroup dmake \
    && adduser -D -g "" -h "/home/dmake" -G dmake dmake \
    && passwd -u dmake

RUN mkdir /home/dmake/.ssh
COPY ssh/id_rsa.pub /home/dmake/.ssh/id_rsa.pub
COPY ssh/id_rsa /home/dmake/.ssh/id_rsa

RUN chown -R dmake:dmake /home/dmake/.ssh \
    && chmod go-rwx /home/dmake/.ssh/id_rsa \
    && chmod go-rwx /home/dmake/.ssh

RUN mkdir -p /srv/texmlbus/build \
    && chown -R dmake:dmake /srv \
    && chmod -R 755 /srv

VOLUME ["/srv/texmlbus/build"]

# installation finished
RUN rm -f /var/cache/apk/*

# workaround for message
# sudo: setrlimit(RLIMIT_CORE): Operation not permitted
RUN echo "Set disable_coredump false" >> /etc/sudo.conf

RUN mkdir /bootstrap
ADD start.sh /bootstrap/
RUN chmod +x /bootstrap/start.sh

EXPOSE 80
EXPOSE 443

ENTRYPOINT ["/bootstrap/start.sh"]




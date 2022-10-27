FROM alpine:3.16
#MAINTAINER Paul Smith <pa.ulsmith.net>
#See https://hub.docker.com/r/ulsmith/alpine-apache-php7

# modified to be used especially for texmlbus

#MAINTAINER Heinrich Stamerjohanns <heinrich@stamerjohanns.de>

# Add repos
#RUN echo "http://dl-cdn.alpinelinux.org/alpine/edge/testing" >> /etc/apk/repositories

# Enable community, if not enabled
RUN sed -e 's;^#http\(.*\)community;http\1community;g' \
      -i /etc/apk/repositories

# Add basics first
RUN apk update \
    && apk add --no-cache \
    apache2 \
    apache2-ssl \
    bash \
    ca-certificates \
    curl \
    git \
    nano \
    openssh-keygen \
    openssl \
    openntpd \
    php81 \
    php81-apache2 \
    php81-iconv \
    php81-json \
    php81-openssl \
    php81-phar \
    tzdata

# dmake specific stuff
RUN apk add --no-cache \
    file \
    make \
    mysql-client \
    perl \
    sudo \
    #unrar \
    zip

# Add Composer
RUN curl -sS https://getcomposer.org/installer | php81 && mv composer.phar /usr/local/bin/composer

# Setup apache and php
RUN apk add --no-cache \
    php81-pecl-apcu \
    php81-bcmath \
    php81-bz2 \
    php81-ctype \
    php81-curl \
    php81-dom \
    php81-exif \
    php81-ftp \
    php81-gd \
    php81-gettext \
    php81-mbstring \
    php81-mysqli \
    php81-pcntl \
    php81-pdo \
    php81-pdo_mysql \
    php81-posix \
    php81-pecl-redis \
    php81-session \
    php81-sysvshm \
    php81-soap \
    php81-tokenizer \
    php81-pecl-xdebug \
    php81-xml \
    php81-xmlreader \
    php81-xmlwriter \
    php81-zip

# only available in testing, but unstable
# php81-pecl-inotify
# therefore manually install via pecl

# needed for pecl
RUN apk add --no-cache \
    php81-dev \
    php81-pear

RUN set -xe; \
    apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
      # build tools
      autoconf g++ gcc make \
      # lib tools
      #bzip2-dev freetype-dev gettext-dev icu-dev imagemagick-dev libintl libjpeg-turbo-dev \
      #libpng-dev libxslt-dev libzip-dev \
      ; \
    pecl81 channel-update pecl.php.net \
    && pecl81 install -o -f \
      inotify \
      ; \
    apk del .build-deps

RUN echo "extension=inotify.so" > /etc/php81/conf.d/inotify.ini

# Problems installing in above stack
RUN apk add php81-simplexml

RUN ln -sf /usr/bin/php81 /usr/bin/php

RUN curl -L -s "http://mirrors.ctan.org/support/latexmk/latexmk.pl" -o /usr/bin/latexmk \
    && chmod ugo+rx /usr/bin/latexmk

# Add apache to run and configure
RUN sed -i "s/#LoadModule\ rewrite_module/LoadModule\ rewrite_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ expires_module/LoadModule\ expires_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_module/LoadModule\ session_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_cookie_module/LoadModule\ session_cookie_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ session_crypto_module/LoadModule\ session_crypto_module/" /etc/apache2/httpd.conf \
    && sed -i "s/#LoadModule\ deflate_module/LoadModule\ deflate_module/" /etc/apache2/httpd.conf \
    && sed -i "s#^DocumentRoot \".*#DocumentRoot \"/srv/texmlbus/src/server/htdocs\"#g" /etc/apache2/httpd.conf \
    && sed -i "s#^DocumentRoot \".*#DocumentRoot \"/srv/texmlbus/src/server/htdocs\"#g" /etc/apache2/conf.d/ssl.conf \
    && sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/src/server/htdocs#" /etc/apache2/httpd.conf \
    && sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/src/server/htdocs#" /etc/apache2/conf.d/ssl.conf \
    && printf "\n<Directory \"/srv/texmlbus/src/server/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/httpd.conf \
    && printf "\n<Directory \"/srv/texmlbus/src/server/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\nIndexOptions Charset=utf-8 FancyIndexing HTMLTable ScanHTMLTitles\nOptions +Indexes -ExecCGI -Includes\nIndexIgnore .gitignore .ht* upload\nAddType text/plain .log\n<IfModule mod_expires.c>\nExpiresActive On\nExpiresDefault \"access plus 1 second\"\n</IfModule>\n</Directory>\n" >> /etc/apache2/conf.d/ssl.conf \
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

RUN mkdir -p /srv/texmlbus/src \
    && chown -R dmake:dmake /srv \
    && chmod -R 755 /srv

VOLUME ["/srv/texmlbus/src"]

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

# This image is used as base image for LaTeXML.
# It installs ghostscript, imagemagick and
# the webserver and php to be run as worker.
FROM alpine:edge as latexml_base_edge

ARG TIMEOUT_SECONDS="1200"

RUN echo -e \
" ############################################################\n" \
"If the next command fails, you have not checked out LaTeXML_edge.\n" \
"Please enter: cd docker; git clone https://github.com/brucemiller/LaTeXML.git LaTeXML_edge\n" \
"############################################################\n"
COPY LaTeXML_edge/bin/latexml /dev/null

# Enable community, if not enabled
RUN sed -e 's;^#http\(.*\)community;http\1community;g' \
      -i /etc/apk/repositories

## base latexml start

RUN apk update \
 && apk add --no-cache \
    db-dev \
    gcc \
    libc-dev \
    libgcrypt \
    libgcrypt-dev \
    libxml2 \
    libxml2-dev \
    libxslt \
    libxslt-dev \
    make \
    perl \
    perl-dev \
    perl-utils \
    wget \
    zlib \
    zlib-dev

# Configure TeXLive Support
# Set to "no" to disable, "yes" to enable
ARG WITH_TEXLIVE="yes"

# Install TeXLive if not disabled
RUN [ "$WITH_TEXLIVE" == "no" ] || (\
           apk add --no-cache -U poppler harfbuzz-icu zziplib texlive-full \
        && ln -s /usr/bin/mktexlsr /usr/bin/mktexlsr.pl \
    )

# Install cpanminus
RUN apk add --no-cache -U perl-app-cpanminus

## base latexml end

# For some reason pdftex.map is not created (?)
RUN echo 'y' | /usr/bin/updmap-sys --syncwithtrees \
    && /usr/bin/updmap-sys

# Install the dependencies
RUN apk add --no-cache \
    ghostscript \
    git \
    imagemagick \
    imagemagick-perlmagick

# Install packages fotr worker: apache2, php, make
RUN apk add --no-cache \
    apache2 \
    apache2-proxy \
    make \
    openrc \
    php81-cli \
    php81-fpm \
    php81-json \
    php81-mysqli \
    php81-pcntl \
    php81-pdo \
    php81-pdo_mysql \
    php81-posix \
    php81-sysvshm

RUN ln -sf /usr/bin/php81 /usr/bin/php

# configure apache
RUN sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/src/worker/htdocs#" /etc/apache2/httpd.conf \
    && sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf \
    && sed -i "s#^Timeout.*#Timeout ${TIMEOUT_SECONDS}#" /etc/apache2/conf.d/default.conf \
    && printf "\n<Directory \"/srv/texmlbus/src/worker/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\n<FilesMatch \"\\.php\$\">\nSetHandler \"proxy:unix:/run/php-fpm.sock|fcgi://localhost\"\n" >> /etc/apache2/conf.d/default.conf \
    && printf "</FilesMatch>\n" >> /etc/apache2/conf.d/default.conf \
    && printf "</Directory>\n" >> /etc/apache2/conf.d/default.conf \
    && printf "# Never use 'enablereuse=on' with unix domain sockets.\n"  >> /etc/apache2/conf.d/default.conf \
    && printf "# php-fpm will get stuck with processes in finishing state.\n" >> /etc/apache2/conf.d/default.conf\
    && printf "<Proxy \"fcgi://localhost/\" flushpackets=on max=10>\n" >> /etc/apache2/conf.d/default.conf \
    && printf "</Proxy>\n" >> /etc/apache2/conf.d/default.conf \
    && printf "# Enable 'status' and 'ping' page\n"  >> /etc/apache2/conf.d/default.conf \
    && printf "<LocationMatch \"/(ping|status)\">\n" >> /etc/apache2/conf.d/default.conf \
    && printf "SetHandler \"proxy:unix:/run/php-fpm.sock|fcgi://localhost\"\n" >> /etc/apache2/conf.d/default.conf \
    && printf "</LocationMatch>\n" >> /etc/apache2/conf.d/default.conf \
    && printf "# Enable *real-time* 'status' page\n"  >> /etc/apache2/conf.d/default.conf \
    && printf "#<IfModule alias_module>\n"  >> /etc/apache2/conf.d/default.conf \
    && printf "#Alias /realtime-status \"/var/lib/php81/fpm/status.html\"\n" >> /etc/apache2/conf.d/default.conf \
    && printf "#</IfModule>\n" >> /etc/apache2/conf.d/default.conf


# configure php-fpm
RUN sed -i "s#^user\s*=\s*nobody#user = dmake#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#group\s*=\s*nobody#user = dmake#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#^listen\s*=.*#listen = /run/php-fpm.sock#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#^;listen.owner\s*=.*#listen.owner = dmake#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#^;listen.group\s*=.*#listen.group = dmake#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#^;listen.mode\s*=.*#listen.mode = 0666#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#^pm.max_children\s*=.*#pm.max_children = 15#" /etc/php81/php-fpm.d/www.conf \
    && sed -i "s#;catch_workers_output.*#catch_workers_output = yes#" /etc/php81/php-fpm.d/www.conf \
    && sed -i 's#;pm.status_path#pm.status_path#' /etc/php81/php-fpm.d/www.conf

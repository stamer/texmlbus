#FROM latexml_base AS latexml_dmake
# PUBLIC DOMAIN Bruce Miller https://github.com/brucemiller/LaTeXML.git
FROM texmlbus_latexml AS latexml_dmake

ARG TIMEOUT_SECONDS

RUN apk add \
    apache2 \
    apache2-proxy \
    imagemagick \
    imagemagick-perlmagick \
    ghostscript \
    make \
    openrc \
    php-cli \
    php-fpm \
    php-json \
    php-pcntl \
    php-posix \
    php-sysvshm

RUN addgroup dmake \
    && adduser -D -g "" -h "/home/dmake" -G dmake dmake \
    && passwd -u dmake

# so latexml does not emit a warning
RUN mkdir /css
COPY css/latexml-local.css /css/latexml-local.css

# remove annoying message
RUN rm /etc/motd

# do not use /opt/bin this not the installed version
RUN ln -s /usr/local/bin/latexml /bin/latexml
RUN ln -s /usr/local/bin/latexmlpost /bin/latexmlpost

# use latexpand
# BSD Matthieu Moy https://gitlab.com/latexpand/latexpand
COPY apps/latexpand/latexpand /usr/bin

ENV TIMEOUT_SECONDS=$TIMEOUT_SECONDS
# configure apache
RUN sed -i "s#/var/www/localhost/htdocs#/srv/texmlbus/build/worker/htdocs#" /etc/apache2/httpd.conf \
    && sed -i '/LoadModule rewrite_module/s/^#//g' /etc/apache2/httpd.conf \
    && sed -i "s#^Timeout.*#Timeout ${TIMEOUT_SECONDS}#" /etc/apache2/conf.d/default.conf \
    && printf "\n<Directory \"/srv/texmlbus/build/worker/htdocs\">\nAllowOverride All\nAddDefaultCharset utf-8\n<FilesMatch \"\\.php\$\">\nSetHandler \"proxy:unix:/run/php-fpm.sock|fcgi://localhost\"\n" >> /etc/apache2/conf.d/default.conf \
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
    && printf "#Alias /realtime-status \"/var/lib/php7/fpm/status.html\"\n" >> /etc/apache2/conf.d/default.conf \
    && printf "#</IfModule>\n" >> /etc/apache2/conf.d/default.conf


# configure php-fpm
RUN sed -i "s#^user\s*=\s*nobody#user = dmake#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#group\s*=\s*nobody#user = dmake#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#^listen\s*=.*#listen = /run/php-fpm.sock#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#^;listen.owner\s*=.*#listen.owner = dmake#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#^;listen.group\s*=.*#listen.group = dmake#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#^;listen.mode\s*=.*#listen.mode = 0666#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#^pm.max_children\s*=.*#pm.max_children = 15#" /etc/php7/php-fpm.d/www.conf \
    && sed -i "s#;catch_workers_output.*#catch_workers_output = yes#" /etc/php7/php-fpm.d/www.conf \
    && sed -i 's#;pm.status_path#pm.status_path#' /etc/php7/php-fpm.d/www.conf

RUN mkdir /bootstrap
ADD start.sh /bootstrap/
RUN chmod +x /bootstrap/start.sh

ENTRYPOINT ["/bootstrap/start.sh"]


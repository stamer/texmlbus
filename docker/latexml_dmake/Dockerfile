# This image is created after latexml has been built.
# Package installation has been moved to latexml_base.
FROM texmlbus_latexml AS latexml_dmake

# necessary modules should already have been installed
# in texmlbus_latexml_base, so it it is not necessary to reinstall
# them after latexml has changed

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

RUN mkdir /bootstrap
ADD start.sh /bootstrap/
RUN chmod +x /bootstrap/start.sh

ENTRYPOINT ["/bootstrap/start.sh"]


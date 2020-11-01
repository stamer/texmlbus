#FROM latexml_base AS latexml_dmake
# PUBLIC DOMAIN Bruce Miller https://github.com/brucemiller/LaTeXML.git
FROM texmlbus_latexml_edge AS latexml_dmake_edge

RUN apk add \
    imagemagick \
    imagemagick-perlmagick \
    ghostscript \
    make \
    openrc \
    openssh \
    php-cli

RUN (ls /etc/ssh/ssh_host_rsa_key) || ssh-keygen -f /etc/ssh/ssh_host_rsa_key -N '' -t rsa
RUN (ls /etc/ssh/ssh_host_dsa_key) || ssh-keygen -f /etc/ssh/ssh_host_dsa_key -N '' -t dsa

RUN addgroup dmake \
    && adduser -D -g "" -h "/home/dmake" -G dmake dmake \
    && passwd -u dmake

RUN mkdir /home/dmake/.ssh
COPY ssh/id_rsa.pub /home/dmake/.ssh/authorized_keys

# so latexml does not emit a warning
RUN mkdir /css
COPY css/latexml-local.css /css/latexml-local.css

RUN chown -R dmake:dmake /home/dmake/.ssh \
    && chmod go-rwx /home/dmake/.ssh/authorized_keys \
    && chmod go-rwx /home/dmake/.ssh

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

# do not detach
CMD ["/usr/sbin/sshd", "-D"]


# vim: syn=dockerfile
FROM debian:8
RUN apt-get update && apt-get -y install git ssh sudo wget make php5-fpm fcgiwrap nginx supervisor
VOLUME /home/git/repos
EXPOSE 9418 22 80
ARG WEB_TITLE="Git Repositories"
ARG ADMIN_USER=admin
ARG ADMIN_PASSWORD=admin
COPY ./ /root/simple-git-host
RUN cd /root/simple-git-host && \
    mkdir -p /etc/ssh && \
    cp compose/sshd_config /etc/ssh/sshd_config && \
    cp compose/supervisord.conf /etc/supervisord.conf && \
    mkdir -p /var/www/html /var/www/logs /var/run/sshd && \
    ./configure --webuser=www-data --prefix=/var/www/html --logsdir=/var/www/logs --webtitle="$WEB_TITLE" && \
    make clean all install && \
    chown -R git: /home/git && \
    sed -r 's|localhost|_|; s|/var/run/php-fpm.sock|/var/run/php5-fpm.sock|; s|libexec|lib|;' gen/nginx.conf > /etc/nginx/sites-available/git.conf && \
    rm /etc/nginx/sites-enabled/default && \
    ln -s /etc/nginx/sites-available/git.conf /etc/nginx/sites-enabled/
RUN gitpass=$(printf "$ADMIN_PASSWORD"|md5sum|cut -d" " -f1) && \
    echo '#!/bin/sh' > /usr/local/bin/gitrepo-sanity-check && \
    echo 'set -e' >> /usr/local/bin/gitrepo-sanity-check && \
    echo 'chown -R git:git /home/git/repos' >> /usr/local/bin/gitrepo-sanity-check && \
    echo "if [ ! -e /home/git/repos/.keys ] && [ ! -e /home/git/repos/.admins ]; then su -s /bin/bash -c '~/gitrepo.sh create-user \"$ADMIN_USER\" \"$gitpass\" && ~/gitrepo.sh user-set-admin \"$ADMIN_USER\" true' git; fi" >> /usr/local/bin/gitrepo-sanity-check && \
    echo "su -s /bin/bash -c '~/makekeys.sh' git" >> /usr/local/bin/gitrepo-sanity-check && \
    echo 'exec "$@"' >> /usr/local/bin/gitrepo-sanity-check && \
    chmod +x /usr/local/bin/gitrepo-sanity-check
ENTRYPOINT ["/usr/local/bin/gitrepo-sanity-check"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]

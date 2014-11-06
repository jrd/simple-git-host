#!/bin/sh
# vim: set et ai sw=2 ts=2 tw=0:
cd "$(dirname "$0")"

usage() {
  cat <<EOF
install.sh -h git_host -u git_user -d git_home_dir -t web_title -w web_user -g git_web_path
  git_host: hostname to connect to in ssh for git access
  git_user: user to connect to in ssh for git access
  git_home_dir: where is the home dir of the git user
  web_title: title of the web site
  web_user: user running web site, usually apache or www or nobody
  git_web_path: if specified, indicate the path (relative to this site) to the git web cgi files
EOF
}
GIT_HOST=''
GIT_USER=''
GIT_HOME_DIR=''
WEB_TITLE=''
WEB_USER=''
GIT_WEB_PATH=''
opts=$(getopt -n install.sh -o 'h:u:d:t:w:g:' -- "$@")
if [ $? -ne 0 ]; then
  usage
  exit 1
fi
eval set -- "$opts"
while [ -n "$1" ] && [ "$1" != "--" ]; do
  case "$1" in
    -h)
      GIT_HOST="$2"
      shift 2
      ;;
    -u)
      GIT_USER="$2"
      shift 2
      ;;
    -d)
      GIT_DIR="$2"
      shift 2
      ;;
    -t)
      WEB_TITLE="$2"
      shift 2
      ;;
    -w)
      WEB_USER="$2"
      shift 2
      ;;
    -g)
      GIT_WEB_PATH="$2"
      shift 2
      ;;
    *)
      echo "Error, unrecognized argument: $1" >&2
      shift
      exit 1
  esac
done
if [ -z "$GIT_HOST" ] || [ -z "$GIT_USER" ] || [ -z "$GIT_DIR" ] || [ -z "$WEB_TITLE" ] || [ -z "$WEB_USER" ]; then
  usage
  exit 1
fi

if [ $(id -u) -ne 0 ]; then
  echo "You need to be root." >&2
  exit 1
fi

if ! grep -q "^$GIT_USER:" /etc/passwd; then
  echo "Git user '$GIT_USER' does not exist" >&2
  exit 2
fi
if [ ! -d "$GIT_DIR" ]; then
  echo "Git directory '$GIT_DIR' does not exist" >&2
  exit 2
fi
if ! grep -q "^$WEB_USER:" /etc/passwd; then
  echo "Web user '$WEB_USER' does not exist" >&2
  exit 2
fi

usermod -s /usr/bin/git-shell $GIT_USER
sed -i "s,^$GIT_USER:.*:\(.*\),$GIT_USER:*:\1," /etc/shadow
GIT_GROUP=$(sed -n "/^$GIT_USER:/ s/^$GIT_USER:[^:]*:[^:]*:\([^:]*\):.*/\1/ p" /etc/passwd)
usermod -a -G $GIT_GROUP $WEB_USER

mkdir -p /etc/sudoers.d
sed "s,WEB_USER,$WEB_USER,; s,GIT_USER,$GIT_USER,; s,GIT_DIR,$GIT_DIR,;" sudoers.d/git > /etc/sudoers.d/git
chmod ug=r,o= /etc/sudoers.d/git

cp -r homegit/* $GIT_DIR/

cp -r src www
cat <<EOF > www/config.inc.php
<?php
\$title = '$WEB_TITLE';
\$githost = '$GIT_HOST';
\$gituser = '$GIT_USER';
\$gitdir = '$GIT_DIR';
\$gitwebpath = '$GIT_WEB_PATH';
EOF

cat <<EOF

** Installation complete. **"

Please copy the www folder to your website folder.

If you want to enable anonymous read-only on the repositories, run this:
  \$ git daemon --listen=0.0.0.0 --reuseaddr --base-path=$GIT_DIR --user=$WEB_USER --detach $GIT_DIR

If you want to install gitweb, run this:
  \$ wget 'http://git.kernel.org/?p=git/git.git;a=snapshot;h=HEAD;sf=tgz' -O gitweb.tar.gz
  \$ tar xf gitweb.tar.gz
  \$ cd git-HEAD-*/gitweb
  \$ su
  # make prefix=/usr GITWEB_PROJECTROOT=$GIT_DIR GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/ GITWEB_SITENAME="$WEB_TITLE" gitwebdir=/var/www/$GIT_WEB_PATH install
for example. Of course you need to adjust the path of the gitwebdir to where you install gitweb and this site.
Here is the configuration to add to your virtual host apache file:
  <Directory "/var/www/$GIT_WEB_PATH">
    Options ExecCGI +FollowSymlinks +SymLinksIfOwnerMatch
    AllowOverride All
    Order allow,deny
    Allow from all
    AddHandler cgi-script cgi
    DirectoryIndex gitweb.cgi
  </Directory>
 
For nginx, here is one configuration snippet:
  server {
    listen       localhost:80;
    server_name  $GIT_HOST;
    root   /var/www/$GIT_WEB_PATH;
    index  controller.php;
    access_log  /var/www/$GIT_WEB_PATH/../logs/access.log combined;
    error_log /var/www/$GIT_WEB_PATH/../logs/error.log;
    location / {
      try_files \$uri \$uri/ /controller.php?\$args;
    }
    location ~ \.php$ {
      # pass the PHP scripts to FastCGI server listening on 127.0.0.1:9001
      fastcgi_pass   unix:/var/run/php-fpm.sock;
      include        fastcgi_params;
      fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
    }
    location /gitweb/ {
      gzip off;
      index          gitweb.cgi;
      include        fastcgi_params;
      fastcgi_param  SCRIPT_NAME gitweb.cgi; 
      fastcgi_param  SCRIPT_FILENAME /var/www/$GIT_WEB_PATH/gitweb/gitweb.cgi;
      fastcgi_param  GITWEB_CONFIG /etc/gitweb.conf;
      if (\$uri ~ "/gitweb/gitweb.cgi") {
        fastcgi_pass   unix:/var/run/fcgiwrap.sock;
      }
    }
    location ~ /git(/.*) {
      gzip off;
      # Set chunks to unlimited, as the body's can be huge
      client_max_body_size 0;
      fastcgi_pass unix:/var/run/fcgiwrap.sock;
      include fastcgi_params;
      fastcgi_param SCRIPT_FILENAME /usr/libexec/git-core/git-http-backend;
      fastcgi_param GIT_HTTP_EXPORT_ALL "";
      fastcgi_param GIT_PROJECT_ROOT $GIT_DIR;
      fastcgi_param PATH_INFO \$1;
    }
  }

Have fun ;-)
EOF

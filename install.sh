#!/bin/sh
# vim: set et ai sw=2 ts=2 tw=0:
cd "$(dirname "$0")"

usage() {
  cat <<EOF
install.sh -h git_host -r git_web_root -u git_user -d git_home_dir -t web_title -w web_user -g gitweb_path -s [nginx|apache2]
  git_host: hostname to connect to in ssh for git access
  git_web_root: root path relative to git_host to access the webapp. Empty for virtual host.
                if not empty, the root should end with a slash.
  git_user: user to connect to in ssh for git access
  git_home_dir: where is the home dir of the git user
  web_title: title of the web site
  web_user: user running web site, usually apache or www or nobody
  web_path: unix absolute path where to install the web site
  gitweb_path: if specified, indicate the path (relative to this git_web_root) to the git web cgi files
  server_type: nginx or apache2
EOF
}
GIT_HOST=''
GIT_WEB_ROOT=''
GIT_USER=''
GIT_HOME_DIR=''
WEB_TITLE=''
WEB_USER=''
WEB_PATH=''
GITWEB_PATH=''
SERVER_TYPE=''
opts=$(getopt -n install.sh -o 'h:r:u:d:t:w:p:g:s:' -- "$@")
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
    -r)
      GIT_WEB_ROOT="$2"
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
    -p)
      WEB_PATH=$(readlink -f "$2")
      shift 2
      ;;
    -g)
      GITWEB_PATH="$2"
      shift 2
      ;;
    -s)
      SERVER_TYPE="$2"
      shift 2
      ;;
    *)
      echo "Error, unrecognized argument: $1" >&2
      shift
      exit 1
  esac
done
if [ -z "$GIT_HOST" ] || [ -z "$GIT_USER" ] || [ -z "$GIT_DIR" ] || [ -z "$WEB_TITLE" ] || [ -z "$WEB_USER" ] || (echo "$WEB_PATH"|grep -qv '^/') || ([ "$SERVER_TYPE" != "nginx" ] && [ "$SERVER_TYPE" != "apache2" ]); then
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
mkdir -p $WEB_PATH/${GIT_WEB_ROOT}
cp -r src/* src/.??* $WEB_PATH/${GIT_WEB_ROOT}
cat <<EOF > $WEB_PATH/${GIT_WEB_ROOT}config.inc.php
<?php
\$title = '$WEB_TITLE';
\$githost = '$GIT_HOST';
\$gitwebroot = '$GIT_WEB_ROOT';
\$gituser = '$GIT_USER';
\$gitdir = '$GIT_DIR';
\$gitwebpath = '$GITWEB_PATH';
EOF

if [ -n "$GITWEB_PATH" ]; then
  wget https://github.com/git/git/archive/master.tar.gz -O - | tar xzf -
  cd git-master/gitweb
  make prefix=/usr GITWEB_PROJECTROOT=$GIT_DIR GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/$GIT_WEB_ROOT GITWEB_SITENAME="$WEB_TITLE" gitwebdir=$WEB_PATH/${GIT_WEB_ROOT}${GITWEB_PATH} install
fi

chown -R $WEB_USER: $WEB_PATH/${GIT_WEB_ROOT}

cat <<EOF > git-daemon.example
# If you want to enable anonymous read-only git protocol on the repositories, run this:
git daemon --listen=0.0.0.0 --reuseaddr --base-path=$GIT_DIR --user=$WEB_USER --detach $GIT_DIR
EOF

if [ "$SERVER_TYPE" = "nginx" ]; then
  cat <<EOF > ${SERVER_TYPE}.conf
server {
  listen       localhost:80;
  server_name  $GIT_HOST;
  root   $WEB_PATH/$GIT_WEB_ROOT;
  access_log  $WEB_PATH/../logs/access.log combined;
  error_log $WEB_PATH/../logs/error.log;
  location /$GIT_WEB_ROOT {
    index controller.php;
    try_files \$uri \$uri/ /controller.php?\$args;
  }
  location ~ \.php$ {
    gzip off
    fastcgi_pass   unix:/var/run/php-fpm.sock;
    include        fastcgi_params;
    fastcgi_param  SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
  }
  location ~ ${GIT_WEB_ROOT}/readonly(/.*) {
    gzip off;
    # Set chunks to unlimited, as the body's can be huge
    client_max_body_size 0;
    fastcgi_pass unix:/var/run/fcgiwrap.sock;
    include fastcgi_params;
    fastcgi_param SCRIPT_FILENAME /usr/libexec/git-core/git-http-backend;
    fastcgi_param GIT_HTTP_EXPORT_ALL "";
    fastcgi_param GIT_PROJECT_ROOT $GIT_DIR;
    fastcgi_param HOME $GIT_DIR;
    fastcgi_param PATH_INFO \$1;
  }
EOF
  if [ -n "$GITWEB_PATH" ]; then
    cat <<EOF >> ${SERVER_TYPE}.conf
  location /${GIT_WEB_ROOT}${GITWEB_PATH}/ {
    gzip off;
    index          gitweb.cgi;
    include        fastcgi_params;
    fastcgi_param  SCRIPT_NAME gitweb.cgi; 
    fastcgi_param  SCRIPT_FILENAME $WEB_PATH/${GIT_WEB_ROOT}${GITWEB_PATH}/gitweb.cgi;
    fastcgi_param  GITWEB_CONFIG /etc/gitweb.conf;
    if (\$uri ~ "/${GIT_WEB_ROOT}${GITWEB_PATH}/gitweb.cgi") {
      fastcgi_pass   unix:/var/run/fcgiwrap.sock;
    }
  }
EOF
  fi
  cat <<EOF >> ${SERVER_TYPE}.conf
}
EOF
else
  cat <<EOF > ${SERVER_TYPE}.conf
<!-- Incomplete config sorry -->
<Directory "$WEB_PATH/${GIT_WEB_ROOT}${GITWEB_PATH}">
  Options ExecCGI +FollowSymlinks +SymLinksIfOwnerMatch
  AllowOverride All
  Order allow,deny
  Allow from all
  AddHandler cgi-script cgi
  DirectoryIndex gitweb.cgi
</Directory>
EOF
fi
cat <<EOF

** Installation complete in $WEB_PATH **"

A configuration file (${SERVER_TYPE}.conf) has been created for you.
A git-daemon example is also available in git-daemon.example file.

EOF

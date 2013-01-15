#!/bin/sh
# vim: set et ai sw=2 ts=2 tw=0:
cd "$(dirname "$0")"

usage() {
  cat <<EOF
install.sh -h git_host -u git_user -d git_home_dir -t web_title -w web_user
  git_host: hostname to connect to in ssh for git access
  git_user: user to connect to in ssh for git access
  git_home_dir: where is the home dir of the git user
  web_title: title of the web site
  web_user: user running web site, usually apache or www or nobody
EOF
}
GIT_HOST=''
GIT_USER=''
GIT_HOME_DIR=''
WEB_TITLE=''
WEB_USER=''
opts=$(getopt -n install.sh -o 'h:u:d:t:w:' -- "$@")
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
EOF

cat <<'EOF'

** Installation complete. **"

Please copy the www folder to your website folder.

If you want to enable anonymous read-only on the repositories, run this:
EOF
echo "git daemon --listen=0.0.0.0 --reuseaddr --base-path=$GIT_DIR --user=$WEB_USER --detach $GIT_DIR"

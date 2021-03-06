#!/bin/sh
# vim: et ai cin sw=2 ts=2 tw=0:
cd ~/repos

usage() {
  cat <<EOF
$0 Action Parameters
Action is one of:
 - list-users
 - create-user Username Password [Key]
   Password should be in md5
 - change-user Username Password
 - show-pwd Username
 - user-set-admin Username true|false
 - user-is-admin Username
 - list-keys Username
 - add-key Username Key
 - del-key Username Key
   del-key Username Position
   Position starts from 1 as listed by list-keys
 - destroy-user Username
 
 - create Name [Description]
 - delete Name
 - undelete Name
 - destroy Name
 - get Name Option
   Option can be
   * 'description'
   * a git configuration option
 - set Name Option Value

 - show-users Name
 - add-user Name Username [admin|user|readonly]
   Username should already exists
   Default right is user
 - user-right Name Username
   Return admin, user, readonly or an empty string
 - del-user Name Username
 
 - graph Name
   Show a graphical representation of the repository.
 - fetch Name Url
   Fetch all remotes if the Url is in Name.git/fetchurls file
 - export Name on|off
   Export (or not) the following repo as read-only for git-daemon and git-http-backend
 - sync Name from|to url
   Synchronize from or to the following URL.
   Sync to is done automatically at each commit. Be careful to add git user ssl keys to remote repo.
   Sync from is done automatically after a POST request to post-update.php script with a json variable named 'payload' containing:
     {'repository': {'name', 'url'}
 - unsync Name from|to url
   remove sync previously set by 'sync' command.
 - deploy-key Name
 - listsync Name from|to
   list the url that are synchronized from or to, as defined by 'sync' command.
EOF
}

checkparams() {
  while [ -n "$1" ]; do
    param="$1"
    eval value="\$$param"
    if [ -z "$value" ]; then
      echo "$param is missing" >&2
      exit 1
    fi
    shift
  done
}

check_repo() {
  REPO="$1"
  if echo "$REPO" | grep -q '[^-_a-zA-Z0-9]'; then
    echo "Repository name can only contains letters, numbers, hyphen and underscore" >&2
    exit 2
  fi
}

check_username() {
  NAME="$1"
  if echo "$NAME" | grep -q '[^-_.@a-zA-Z0-9]'; then
    echo "User name can only contains letters, numbers, hyphen, underscore, dot and at sign" >&2
    exit 2
  fi
}

list_users() {
  for f in .keys/*.pwd; do
    basename $f .pwd
  done
}

create_user() {
  USERNAME="$1"
  PASSWD="$2"
  KEY="$3"
  check_username "$USERNAME"
  if [ -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME already exists." >&2
    exit 2
  fi
  mkdir -p .keys
  echo "$PASSWD" > .keys/$USERNAME.pwd
  if [ -n "$KEY" ]; then
    echo "$KEY" > .keys/$USERNAME.keys
  else
    touch .keys/$USERNAME.keys
  fi
  ~/makekeys.sh
}

change_user() {
  USERNAME="$1"
  PASSWD="$2"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  mkdir -p .keys
  echo "$PASSWD" > .keys/$USERNAME.pwd
}

show_pwd() {
  USERNAME="$1"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  cat .keys/$USERNAME.pwd
}

user_set_admin() {
  USERNAME="$1"
  ADMIN="$2"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  mkdir -p .admins
  if [ "$ADMIN" = "true" ]; then
    touch .admins/$USERNAME
  else
    rm -f .admins/$USERNAME
  fi
}

user_is_admin() {
  USERNAME="$1"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  if [ -e .admins/$USERNAME ]; then
    echo true
    true
  else
    echo false
    false
  fi
}

list_keys() {
  USERNAME="$1"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  cat .keys/$USERNAME.keys
}

add_key() {
  USERNAME="$1"
  KEY="$2"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  echo "$KEY" >> .keys/$USERNAME.keys
  ~/makekeys.sh
}

del_key() {
  USERNAME="$1"
  KEY="$2"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  if echo "$KEY" | grep -q "^[0-9]\+$"; then
    if [ $KEY -eq 1 ]; then
      sed -i -n '2,$p' .keys/$USERNAME.keys
    else
      prev=$(($KEY - 1))
      next=$(($KEY + 1))
      sed -i -n "1,${prev}p; ${next},\$p" .keys/$USERNAME.keys
    fi
  else
    sed -i "/^$KEY\$/d" .keys/$USERNAME.keys
  fi
  ~/makekeys.sh
}

destroy_user() {
  USERNAME="$1"
  check_username "$USERNAME"
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  rm .keys/$USERNAME.*
  for p in ?*.git; do
    [ -e $p/.users ] && sed -i "/^$USERNAME:.*/d" $p/.users
  done
  ~/makekeys.sh
}

create_repo() {
  REPO="$1"
  DESC="$2"
  check_repo "$REPO"
  if [ -d "$REPO".git ]; then
    echo "$REPO already exists." >&2
    exit 2
  fi
  mkdir "$REPO".git
  (
    cd "$REPO".git
    git --bare init
    git config core.sharedRepository 1
    if [ -n "$DESC" ]; then
      echo "$DESC" > description
    fi
  )
}

delete_repo() {
  REPO="$1"
  check_repo "$REPO"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  mv "$REPO".git ."$REPO".git
}

undelete_repo() {
  REPO="$1"
  check_repo "$REPO"
  if [ ! -d ."$REPO".git ]; then
    echo "$REPO cannot be undeleted, not found." >&2
    exit 2
  fi
  mv ."$REPO".git "$REPO".git
}

destroy_repo() {
  REPO="$1"
  check_repo "$REPO"
  if [ ! -d ."$REPO".git ]; then
    if [ -d "$REPO".git ]; then
      echo "$REPO should be deleted before being destroyed." >&2
      exit 2
    else
      echo "$REPO does not exist." >&2
      exit 2
    fi
  fi
  rm -rf ."$REPO".git
}

get_option() {
  REPO="$1"
  OPTION="$2"
  check_repo "$REPO"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ "$OPTION" = "description" ]; then
    cat "$REPO".git/description
  else
    (
      cd "$REPO".git
      git --bare config "$OPTION"
    )
  fi
}

set_option() {
  REPO="$1"
  OPTION="$2"
  OPT_VAL="$3"
  check_repo "$REPO"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ "$OPTION" = "description" ]; then
    echo "$OPT_VAL" > "$REPO".git/description
  else
    (
      cd "$REPO".git
      if [ -n "$OPT_VAL" ]; then
        git --bare config "$OPTION" "$OPT_VAL"
      else
        git --bare config --unset "$OPTION"
      fi
    )
  fi
}

add_user() {
  REPO="$1"
  USERNAME="$2"
  RIGHT="$3"
  check_repo "$REPO"
  check_username "$USERNAME"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ ! -f .keys/$USERNAME.pwd ]; then
    echo "$USERNAME does not exists." >&2
    exit 2
  fi
  [ -e "$REPO".git/.users ] || touch "$REPO".git/.users
  if grep -q "^$USERNAME:.*" "$REPO".git/.users; then
    sed -i -r "s/^$USERNAME:.*/$USERNAME:$RIGHT/" "$REPO".git/.users
  else
    echo "$USERNAME:$RIGHT" >> "$REPO".git/.users
  fi
}

user_right() {
  REPO="$1"
  USERNAME="$2"
  check_repo "$REPO"
  check_username "$USERNAME"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ -e "$REPO".git/.users ]; then
    sed -r -n "/^$USERNAME:/ { s/.*:(.*)/\1/; p }" "$REPO".git/.users
  fi
}

del_user() {
  REPO="$1"
  USERNAME="$2"
  check_repo "$REPO"
  check_username "$USERNAME"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ -e "$REPO".git/.users ]; then
    grep -q "^$USERNAME:.*" "$REPO".git/.users && sed -i "/^$USERNAME:.*/d" "$REPO".git/.users
  fi
}

show_users() {
  REPO="$1"
  check_repo "$REPO"
  if [ ! -d "$REPO".git ]; then
    echo "$REPO does not exist." >&2
    exit 2
  fi
  if [ -f "$REPO".git/.users ]; then
    sort -t: -k 2 -k 1 "$REPO".git/.users
  fi
}

graph() {
  NAME="$1"
  check_repo "$NAME"
  if [ ! -d "$NAME".git ]; then
    echo "$NAME does not exist." >&2
    exit 2
  fi
  (
    cd "$NAME".git
    git log --all --oneline --graph --decorate=short
  )
}

fetch() {
  NAME="$1"
  URL="$2"
  check_repo "$NAME"
  if [ -f "$NAME".git/fetchremotes ] && grep -q -F "$URL" "$NAME".git/fetchremotes; then
    (
      cd "$NAME".git
      git fetch -f "$URL" *:*
    )
  fi
}

exportRepo() {
  NAME="$1"
  DOEXPORT="$2"
  check_repo "$NAME"
  if [ "$DOEXPORT" = "on" ]; then
    touch "$NAME".git/git-daemon-export-ok
    (
      cd "$NAME".git
      git config http.getanyfile false
      git config http.uploadpack true
      git config http.receivepack false
    )
  else
    rm -f "$NAME".git/git-daemon-export-ok
    (
      cd "$NAME".git
      git config --unset-all http
    )
  fi
}

syncRepo() {
  NAME="$1"
  DIR="$2"
  URL="$3"
  check_repo "$NAME"
  if [ "$DIR" = "to" ]; then
    syncToRepo "$NAME" "$URL"
  elif [ "$DIR" = "from" ]; then
    syncFromRepo "$NAME" "$URL"
  else
    echo "$DIR is not a correct sync direction. 'to' or 'from' expected." >&2
    exit 2
  fi
}

syncToRepo() {
  NAME="$1"
  URL="$2"
  check_repo "$NAME"
  HOST=$(echo -n "$URL"|md5sum|cut -d' ' -f1)
  (
    cd "$NAME".git
    git remote add --mirror $HOST "$URL"
  )
  if [ ! -f "$NAME".git/hooks/post-update ]; then
    cat <<'EOF' > "$NAME".git/hooks/post-update
#!/bin/sh
if [ -d hooks/.post-update.d ]; then
  for f in hooks/.post-update.d/*; do 
    [ -x "$f" ] && ./"$f"
  done
fi
EOF
    chmod +x "$NAME".git/hooks/post-update
    mkdir -p "$NAME".git/hooks/.post-update.d
  fi
  if [ ! -f "$NAME".git/.sshwrapper ]; then
    cat <<'EOF' > "$NAME".git/.sshwrapper
#!/bin/sh
exec ssh \
  -o UserKnownHostsFile=/dev/null \
  -o StrictHostKeyChecking=no \
  -o LogLevel=ERROR \
  -i $(dirname "$0")/id_rsa \
  "$@"
EOF
  fi
  chmod +x "$NAME".git/sshwrapper
  echo 'GIT_SSH=$(readlink -f $(dirname "$0")/../../.sshwrapper) git push --quiet'" $HOST &" > "$NAME".git/hooks/.post-update.d/$HOST
  chmod +x "$NAME".git/hooks/.post-update.d/$HOST
}

syncFromRepo() {
  NAME="$1"
  URL="$2"
  check_repo "$NAME"
  echo "$URL" >> "$NAME".git/fetchremotes
}

unsyncRepo() {
  NAME="$1"
  DIR="$2"
  URL="$3"
  check_repo "$NAME"
  if [ "$DIR" = "to" ]; then
    unsyncToRepo "$NAME" "$URL"
  elif [ "$DIR" = "from" ]; then
    unsyncFromRepo "$NAME" "$URL"
  else
    echo "$DIR is not a correct sync direction. 'to' or 'from' expected." >&2
    exit 2
  fi
}

unsyncToRepo() {
  NAME="$1"
  URL="$2"
  check_repo "$NAME"
  HOST=$(echo -n "$URL"|md5sum|cut -d' ' -f1)
  (
    cd "$NAME".git
    git remote rm $HOST
  )
  [ -f "$NAME".git/hooks/.post-update.d/$HOST ] && rm "$NAME".git/hooks/.post-update.d/$HOST
  true
}

unsyncFromRepo() {
  NAME="$1"
  URL="$2"
  check_repo "$NAME"
  [ -f "$NAME".git/fetchremotes ] && sed -i -n "\,$URL,d; p" "$NAME".git/fetchremotes
  true
}

listSyncRepo() {
  NAME="$1"
  DIR="$2"
  check_repo "$NAME"
  if [ "$DIR" = "to" ]; then
    (
      cd "$NAME".git
      for r in $(git remote); do
        git config remote.$r.url
      done
    )
  elif [ "$DIR" = "from" ]; then
    if [ -f "$NAME".git/fetchremotes ]; then
      cat "$NAME".git/fetchremotes
    else
      true
    fi
  else
    echo "$DIR is not a correct sync direction. 'to' or 'from' expected." >&2
    exit 2
  fi
}

deployKeyRepo() {
  NAME="$1"
  check_repo "$NAME"
  [ -e "$NAME".git/id_rsa ] && rm "$NAME".git/id_rsa
  [ -e "$NAME".git/id_rsa.pub ] && rm "$NAME".git/id_rsa.pub
  ssh-keygen -q -t rsa -N '' -f "$NAME".git/id_rsa
}

ACTION=''
NAME=''
REPO=''
USERNAME=''
PASSWD=''
ADMIN=''
RIGHT=''
DESC=''
OPTION=''
OPT_VAL=''
KEY=''
URL=''
EXPORTREPO=''
SYNCDIRECTION=''
while [ -n "$1" ]; do
  case "$1" in
    -h|--help)
      usage
      exit 0
      ;;
    *)
      if [ -z "$ACTION" ]; then
        if echo "$1" | grep -q '^\(list-users\|create-user\|change-user\|show-pwd\|user-set-admin\|user-is-admin\|list-keys\|add-key\|del-key\|destroy-user\|create\|delete\|undelete\|destroy\|get\|set\|show-users\|add-user\|user-right\|del-user\|graph\|fetch\|export\|sync\|unsync\|listsync\|deploy-key\)$'; then
          ACTION="$1"
          shift
        else
          echo "Unrecognized action ($1)" >&2
          exit 1
        fi
      else
        if [ "$ACTION" = "list-users" ]; then
          echo "Unrecognized parameter ($1)" >&2
          exit 1
        elif [ -z "$NAME" ]; then
          NAME="$1"
          shift
        else
          if [ "$ACTION" = "list-keys" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "create-user" ]; then
            if [ -z "$PASSWD" ]; then
              PASSWD="$1"
              shift
            elif [ -z "$KEY" ]; then
              KEY="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "change-user" ]; then
            if [ -z "$PASSWD" ]; then
              PASSWD="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "show-pwd" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "add-key" ] || [ "$ACTION" = "del-key" ]; then
            if [ -z "$KEY" ]; then
              KEY="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "user-set-admin" ]; then
            if [ -z "$ADMIN" ] && echo "$1" | grep -q '^true\|false'; then
              ADMIN="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "user-is-admin" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "destroy-user" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "create" ]; then
            if [ -z "$DESC" ]; then
              DESC="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "delete" ] || [ "$ACTION" = "undelete" ] || [ "$ACTION" = "destroy" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "get" ] || [ "$ACTION" = "set" ]; then
            if [ -z "$OPTION" ]; then
              OPTION="$1"
              shift
            elif [ "$ACTION" = "set" ] && [ -z "$OPT_VAL" ]; then
              OPT_VAL="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "show-users" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "add-user" ]; then
            if [ -z "$USERNAME" ]; then
              USERNAME="$1"
              shift
            else
              if [ -z "$RIGHT" ] && echo "$1" | grep -q '^admin\|user\|readonly'; then
                RIGHT="$1"
                shift
              else
                echo "Unrecognized parameter ($1)" >&2
                exit 1
              fi
            fi
          elif [ "$ACTION" = "user-right" ]; then
            if [ -z "$USERNAME" ]; then
              USERNAME="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "del-user" ]; then
            if [ -z "$USERNAME" ]; then
              USERNAME="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "graph" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          elif [ "$ACTION" = "fetch" ]; then
            if [ -z "$URL" ]; then
              URL="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "export" ]; then
            if [ -z "$EXPORTREPO" ]; then
              EXPORTREPO="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "sync" ] || [ "$ACTION" = "unsync" ]; then
            if [ -z "$SYNCDIRECTION" ]; then
              SYNCDIRECTION="$1"
              shift
            elif [ -z "$URL" ]; then
              URL="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "listsync" ]; then
            if [ -z "$SYNCDIRECTION" ]; then
              SYNCDIRECTION="$1"
              shift
            else
              echo "Unrecognized parameter ($1)" >&2
              exit 1
            fi
          elif [ "$ACTION" = "deploy-key" ]; then
            echo "Unrecognized parameter ($1)" >&2
            exit 1
          fi
        fi
      fi
      ;;
  esac
done
case "$ACTION" in
  "")
    usage
    exit 1
    ;;
  list-users)
    list_users
    ;;
  create-user)
    USERNAME="$NAME"
    checkparams USERNAME PASSWD
    create_user "$USERNAME" "$PASSWD" "$KEY"
    ;;
  change-user)
    USERNAME="$NAME"
    checkparams USERNAME PASSWD
    change_user "$USERNAME" "$PASSWD"
    ;;
  show-pwd)
    USERNAME="$NAME"
    checkparams USERNAME
    show_pwd "$USERNAME"
    ;;
  user-set-admin)
    USERNAME="$NAME"
    checkparams USERNAME ADMIN
    user_set_admin "$USERNAME" "$ADMIN"
    ;;
  user-is-admin)
    USERNAME="$NAME"
    checkparams USERNAME
    user_is_admin "$USERNAME"
    ;;
  list-keys)
    USERNAME="$NAME"
    checkparams USERNAME
    list_keys "$USERNAME"
    ;;
  add-key)
    USERNAME="$NAME"
    checkparams USERNAME KEY
    add_key "$USERNAME" "$KEY"
    ;;
  del-key)
    USERNAME="$NAME"
    checkparams USERNAME KEY
    del_key "$USERNAME" "$KEY"
    ;;
  destroy-user)
    USERNAME="$NAME"
    checkparams USERNAME
    destroy_user "$USERNAME"
    ;;
  create)
    REPO="$NAME"
    checkparams REPO
    create_repo "$REPO" "$DESC"
    ;;
  delete)
    REPO="$NAME"
    checkparams REPO
    delete_repo "$REPO"
    ;;
  undelete)
    REPO="$NAME"
    checkparams REPO
    undelete_repo "$REPO"
    ;;
  destroy)
    REPO="$NAME"
    checkparams REPO
    destroy_repo "$REPO"
    ;;
  get)
    REPO="$NAME"
    checkparams REPO OPTION
    get_option "$REPO" "$OPTION"
    ;;
  set)
    REPO="$NAME"
    checkparams REPO OPTION
    set_option "$REPO" "$OPTION" "$OPT_VAL"
    ;;
  show-users)
    REPO="$NAME"
    checkparams REPO
    show_users "$REPO"
    ;;
  add-user)
    REPO="$NAME"
    checkparams REPO USERNAME
    [ -n "$RIGHT" ] || RIGHT=user
    add_user "$REPO" "$USERNAME" "$RIGHT"
    ;;
  user-right)
    REPO="$NAME"
    checkparams REPO USERNAME
    user_right "$REPO" "$USERNAME"
    ;;
  del-user)
    REPO="$NAME"
    checkparams REPO USERNAME
    del_user "$REPO" "$USERNAME"
    ;;
  graph)
    REPO="$NAME"
    checkparams REPO
    graph "$REPO"
    ;;
  fetch)
    REPO="$NAME"
    checkparams REPO URL
    fetch "$REPO" "$URL"
    ;;
  export)
    REPO="$NAME"
    checkparams REPO EXPORTREPO
    exportRepo "$REPO" "$EXPORTREPO"
    ;;
  sync)
    REPO="$NAME"
    checkparams REPO SYNCDIRECTION URL
    syncRepo "$REPO" "$SYNCDIRECTION" "$URL"
    ;;
  unsync)
    REPO="$NAME"
    checkparams REPO SYNCDIRECTION URL
    unsyncRepo "$REPO" "$SYNCDIRECTION" "$URL"
    ;;
  listsync)
    REPO="$NAME"
    checkparams REPO SYNCDIRECTION
    listSyncRepo "$REPO" "$SYNCDIRECTION"
    ;;
  deploy-key)
    REPO="$NAME"
    checkparams REPO
    deployKeyRepo "$REPO"
    ;;
esac

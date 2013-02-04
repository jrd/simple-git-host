simple-git-host
===============

Simple, easy web to install a git repositories 'server'.

Author: Cyrille Pontvieux <jrd@enialis.net>
Licence: GPL version 3 (or, at your option, any later version). See LICENCE.TXT for a full version
Quick guide to understand the licence: http://www.gnu.org/licenses/quick-guide-gplv3.html
First Release: February 2013
Version: 1.0

1/ The difference with other solutions
--------------------------------------
- Gnu/Linux only
- only sh scripts for the server
- only php scripts for the web interface
- few dependencies: sh (or any compatible shell), web server (or anything that can run php), php, sudo (the package, no need to be sudoer) and git

2/ Features
-----------
- authentification via ssl keys via ssh.
- anonymous read-only access over git protocol if you choose to run one.
- handling of users and repositories directly on the web interface.
- users can change their ssl keys directly via the web interface.
- authentification per repository.
- admin users are described in a simple text file.
- simple history in the web interface.
- full view of a repository with the help of gitweb if you choose to install it.
- synchronisation from github with the help of the 'post-update.php' script (the repository must have been configured manually to be able to be sync from a remote repo). See help on this in the corresponding chapter.

3/ Enable read-only access via the git protocol
-----------------------------------------------
- create the file 'git-daemon-export-ok' in each repository folder you want to export read-only.
- start the git-daemon:
    # git daemon --listen=0.0.0.0 --reuseaddr --base-path=$GIT_DIR --user=$WEB_USER --detach $GIT_DIR
  With usual parameters it will be something like:
    # git daemon --listen=0.0.0.0 --reuseaddr --base-path=/home/git --user=apache --detach /home/git

4/ Enable gitweb browsing
-------------------------
- download git/gitweb content and extract it:
    $ wget 'http://git.kernel.org/?p=git/git.git;a=snapshot;h=HEAD;sf=tgz' -O gitweb.tar.gz
    $ tar xf gitweb.tar.gz
    $ cd git-HEAD-*/gitweb
- or git clone it:
    $ git clone http://git.kernel.org/pub/scm/git/git.git
    $ cd git/gitweb
- become root or run the following command with sudo (depends on you distribution or configuration):
    # make prefix=/usr GITWEB_PROJECTROOT=$GIT_DIR GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/ GITWEB_SITENAME="$WEB_TITLE" gitwebdir=/var/www/$GIT_WEB_PATH install
  With usual parameters it will be something like:
    # make prefix=/usr GITWEB_PROJECTROOT=/home/git GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/ GITWEB_SITENAME="My Git repositories" gitwebdir=/var/www/git install
- you should enable cgi scripts for all .cgi file in the web folder. Here is the configuration to add to your virtual host apache file for example:
    <Directory "/var/www/$GIT_WEB_PATH">
      Options ExecCGI +FollowSymlinks +SymLinksIfOwnerMatch
      AllowOverride All
      Order allow,deny
      Allow from all
      AddHandler cgi-script cgi
      DirectoryIndex gitweb.cgi
    </Directory>
  Don't forget to adjust the /varr/www/$GIT_WEB_PATH to your real path, for exemple /var/www/git in our example.

5/ Synchronize one repository to another remote repository
----------------------------------------------------------
Say you want to automatically synchronize your repository to another remote one, for one reason or another.
You can do this by doing this:
  - add a remote repository to the bare one. For example to add a github one:
    $ git remote add github git@github.com:USER/REPO.git
  - create a file hooks/post-update (or copy from the sample one) with this line:
    git push --quiet github &
Of course you must be sure that your git user have the right to push to the remote repository.
For this, generate a ssl keys for the git user and add it to the authorized keys of the remote repository.

6/ Synchronize one repository from another remote repository (github only for now)
----------------------------------------------------------------------------------
  - do a POST call to the post-update.php script after each push, with a variable 'payload' that contains at least the following json:
    {
      'repository': {
        'name',
        'url'
      }
    }
  - 'name' is your repository name, without the .git part. It should be the same as the one you have on your server.
  - 'url' is the remote url name, from which you synchronize.
  - have a file 'fetchremotes' in your repository that contains that same url. It's if here for security reason.

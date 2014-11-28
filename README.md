simple-git-host
===============

Simple, easy web to install a git repositories 'server'.

| Type    | Information |
|:------- |:----------- |
| Author  | Cyrille Pontvieux <jrd@enialis.net> |
| Licence | GPL version 3 (or, at your option, any later version). See [LICENSE](LICENSE) for a full version and this [Quick guide](http://www.gnu.org/licenses/quick-guide-gplv3.html) to understand the licence |
| Version | __1.0__ |

Difference with other solutions
--------------------------------------
- GNU/Linux only
- sh scripts for the server
- php scripts for the web interface and perl for gitweb
- few dependencies:
    - sh (or any compatible shell)
    - web server
    - php
    - sudo package
    - git

Features
-----------
- authentification via __ssl keys__ via ssh.
- __anonymous read-only__ access.
- handling of *users* and *repositories* directly on the __web interface__.
- authentification and __rights per repository__: *administrator*, *regular*, *readonly*
- users and rights are described in simple __plain text files__.
- simple __history__ and __repository browser__ in the web interface.
- branches and tags __download__
- full view of a repository with the help of __gitweb__.
- __synchronisation__ *to* and *from* an external repository, i.e. *github*.

Installation
------------
A `configure` script is available to set parameters like git system user, path location and other information.
Next step is a regular `make` and `make install`.

You will then find in the `gen` folder a example configuration for your webserver:

- nginx
- apache http

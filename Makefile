include config.mk

all: _options gen/.website

_options:
	@echo Build options:
	@echo "  PREFIX = ${PREFIX}"
	@echo "  LOGS = ${LOGS}"
	@echo "  WEB_BASE_DIR = ${WEB_BASE_DIR}"
	@echo "  GITWEB_DIR = ${GITWEB_DIR}"
	@echo "  GIT_HOSTNAME = ${GIT_HOSTNAME}"
	@echo "  GIT_HOSTPORT = ${GIT_HOSTPORT}"
	@echo "  GIT_USER = ${GIT_USER}"
	@echo "  GIT_HOME = ${GIT_HOME}"
	@echo "  WEB_USER = ${WEB_USER}"
	@echo "  WEB_TYPE = ${WEB_TYPE}"
	@echo "  WEB_TITLE = ${WEB_TITLE}"
	@echo ""

gen/home:
	@mkdir -p $@

gen/www/$(WEB_BASE_DIR):
	@mkdir -p $@

gen/sudoers.d:
	@mkdir -p $@

gen/sudoers.d/git:
	@sed "s,__WEB_USER__,${WEB_USER},; s,__GIT_USER__,${GIT_USER},; s,__GIT_HOME__,${GIT_HOME},;" tpl/git.sudo > $@
	@visudo -c -f $@ || cat /dev/null > $@

gen/www/$(WEB_BASE_DIR)config.inc.php:
	@echo '<?php' > $@
	@echo '$$title = "${WEB_TITLE}";' >> $@
	@echo '$$githost = "${GIT_HOSTNAME}";' >> $@
	@echo '$$gitwebroot = "${WEB_BASE_DIR}";' >> $@
	@echo '$$gituser = "${GIT_USER}";' >> $@
	@echo '$$gitdir = "${GIT_HOME}";' >> $@
	@echo '$$gitwebpath = "${GITWEB_DIR}";' >> $@
	@echo '?>' >> $@

gen/git-daemon.example:
	@echo "# If you want to enable anonymous read-only git protocol on the repositories, run this:" > $@
	@echo "git daemon --listen=0.0.0.0 --reuseaddr --base-path=${GIT_HOME} --user=${WEB_USER} --detach ${GIT_HOME}" >> $@

gen/nginx.conf:
	@sed -r 's,__PREFIX__,${PREFIX},g; s,__LOGS__,${LOGS},g; s,__WEB_BASE_DIR__,${WEB_BASE_DIR},g; s,__GITWEB_DIR__,${GITWEB_DIR},g; s,__GIT_HOSTNAME__,${GIT_HOSTNAME},g; s,__GIT_HOSTPORT__,${GIT_HOSTPORT},g; s,__GIT_USER__,${GIT_USER},g; s,__GIT_HOME__,${GIT_HOME},g;' tpl/nginx.conf > $@

gen/apache.conf:
	@sed -r 's,__PREFIX__,${PREFIX},g; s,__LOGS__,${LOGS},g; s,__WEB_BASE_DIR__,${WEB_BASE_DIR},g; s,__GITWEB_DIR__,${GITWEB_DIR},g; s,__GIT_HOSTNAME__,${GIT_HOSTNAME},g; s,__GIT_HOSTPORT__,${GIT_HOSTPORT},g; s,__GIT_USER__,${GIT_USER},g; s,__GIT_HOME__,${GIT_HOME},g;' tpl/apache.conf > $@

gen/none.conf:
	touch $@

gen/.website: gen/home gen/www/$(WEB_BASE_DIR) gen/sudoers.d gen/sudoers.d/git gen/www/$(WEB_BASE_DIR)config.inc.php gen/git-daemon.example gen/$(WEB_TYPE).conf
	@cp -r homegit/* gen/home/
	@cp -r src/* src/.??* gen/www/${WEB_BASE_DIR}
	(cd git-master/gitweb && make prefix=/usr GITWEB_PROJECTROOT=${GIT_HOME} GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/${WEB_BASE_DIR} GITWEB_SITENAME="${WEB_TITLE}" gitwebdir=${PREFIX}/${WEB_BASE_DIR}${GITWEB_DIR} all)
	@for h in header footer indextext; do sed -r 's,__WEB_TITLE__,${WEB_TITLE},g; s,__PREFIX__,${PREFIX},g; s,__WEB_BASE_DIR__,${WEB_BASE_DIR},g; s,__GITWEB_DIR__,${GITWEB_DIR},g; s,__GIT_HOSTNAME__,${GIT_HOSTNAME},g; s,__GIT_HOSTPORT__,${GIT_HOSTPORT},g;' tpl/$$h.html > gen/$$h.html; done
	@touch $@
	@echo "Run 'make install' to install the git repositories and the web site"

clean:
	@rm -rf gen
	(cd git-master/gitweb && make clean)

install: _root gen/.website _githome _webhome _sudo
	(cd git-master/gitweb && make prefix=/usr GITWEB_PROJECTROOT=${GIT_HOME} GITWEB_PROJECT_MAXDEPTH=50 GITWEB_EXPORT_OK=git-daemon-export-ok GITWEB_HOME_LINK_STR=/${WEB_BASE_DIR} GITWEB_SITENAME="${WEB_TITLE}" gitwebdir=${PREFIX}/${WEB_BASE_DIR}${GITWEB_DIR} install)
	@cp -v gen/*.html ${PREFIX}/${WEB_BASE_DIR}${GITWEB_DIR}/
	@echo ""
	@echo "Installation complete."
	@echo ""
	@echo "Please configure your web server using gen/${WEB_TYPE}.conf."
	@echo "A git-daemon example script is also available in gen/git-daemon.example."
	@echo "  Adapt it to your OS service system."
	@echo ""
	@echo "/!\ Don't forget to create a new admin user using: make adminuser"

_root:
	@if [ $$(id -u) -ne 0 ]; then echo "You need to be root."; exit 1; fi

_githome:
	@if grep -q "^${GIT_USER}:" /etc/passwd; then usermod -s /usr/bin/git-shell -p '*' ${GIT_USER}; usermod -a -G $$(groups ${GIT_USER}|cut -d: -f2-|awk '{print $$1}') ${WEB_USER}; else useradd -d ${GIT_HOME} -m -r -s /usr/bin/git-shell -p '*' -U ${GIT_USER}; usermod -a -G ${GIT_USER} ${WEB_USER}; fi
	@cp -rv gen/home/* ${GIT_HOME}/
	
_webhome:
	@mkdir -p ${PREFIX}
	@cp -rv gen/www/* ${PREFIX}/

_sudo:
	@cp gen/sudoers.d/git /etc/sudoers.d/git
	@chmod ug=r,o= /etc/sudoers.d/git

adminuser: _root
	@echo "New admin user creation"
	@echo ""
	@sh -c 'echo -n "Username: "; read gituser; echo -n "Password: ";	read -s gitpass; gitpass=$$(echo -n "$$gitpass"|md5sum|cut -d" " -f1); sudo -u ${GIT_USER} ${GIT_HOME}/gitrepo.sh create-user $$gituser $$gitpass; sudo -u ${GIT_USER} ${GIT_HOME}/gitrepo.sh user-set-admin $$gituser true'

.PHONY: all _options clean install _root _githome _webhome _sudo adminuser

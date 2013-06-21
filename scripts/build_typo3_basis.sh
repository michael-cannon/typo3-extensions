#!/bin/sh

# Create a home directory for a Typo3 setup using a common source tree
#
# @author Michael Cannon, michael@peimic.com
# @version $Id: build_typo3_basis.sh,v 1.1.1.1 2010/04/15 10:04:01 peimic.comprock Exp $

# variable constants
HTACCESS='.htaccess'
WEB_USER='CHANGE_ME'
WEB_GROUP='nobody'
TYPO3_SRC="/home/${WEB_USER}/cb-third_party/TYPO3core"
PWD=`pwd`


# Typo3 source
rm typo3_src
ln -s ${TYPO3_SRC} typo3_src

# typo3 source links
ln -s typo3_src/typo3
ln -s typo3_src/t3lib
ln -s typo3_src/tslib
ln -s typo3_src/tslib/media

# root page links
ln -s tslib/index_ts.php index.php
ln -s tslib/showpic.php

# create hard directories
# tempory files
mkdir typo3temp
mkdir -p fileadmin/_temp_/

# extension 
mkdir -p typo3conf/ext/

# user files
mkdir fileadmin/static/

# extension and user uploads
mkdir -p fileadmin/uploads/pics/
mkdir fileadmin/uploads/media/
mkdir fileadmin/uploads/tf/

# log files
mkdir fileadmin/logs/

# static publication
mkdir publish

# create a starting localconf.php
# basic defaults are made sans database login information
cp ${TYPO3_SRC}/typo3conf/* typo3conf/.

# modify .htaccess
echo "Options FollowSymLinks" >> ${HTACCESS}
echo "RewriteEngine On" >> ${HTACCESS}
echo "RewriteRule ^typo3$ - [L]" >> ${HTACCESS}
echo "RewriteRule ^typo3/.*$ - [L]" >> ${HTACCESS}
echo "RewriteCond %{REQUEST_FILENAME} !-f" >> ${HTACCESS}
echo "RewriteCond %{REQUEST_FILENAME} !-d" >> ${HTACCESS}
echo "RewriteCond %{REQUEST_FILENAME} !-l" >> ${HTACCESS}
echo "RewriteRule (\.html|/)$ /index.php" >> ${HTACCESS}

# make uploads accessible from fileadmin
ln -s fileadmin/uploads

# fix web permissions function
fixUserGroup()
{
	# recursively reset user ownership
	chown -R ${WEB_USER} *

	# recursively reset group ownership
	chgrp -R ${WEB_GROUP} *

	# user/group read, write, execute; other read, execute
	find . -type d -exec chmod 0775 {} \;

	# keep user/group
	find . -type d -exec chmod ug+s {} \;

	# user/group read, write; other read
	find . -type f -exec chmod 0664 {} \;
}

chownFixUserGroup()
{
	chgrp -R ${WEB_GROUP} ${DIR}
	chmod 6775 ${DIR}
	cd ${DIR}
	fixUserGroup
}

# fix web permissions
fixUserGroup

# correct ext repository permissions
DIR='ext'
cd ${PWD}/typo3
chownFixUserGroup

# correct sysext repository permissions
DIR='sysext'
cd ../
chownFixUserGroup

# correct sysext repository permissions
DIR='ext'
cd ../../typo3conf
chownFixUserGroup

# now just point to the frontend at http://example.com/ for Install 1-2-3
# to customize database and other configuration information

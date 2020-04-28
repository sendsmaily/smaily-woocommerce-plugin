#!/bin/bash

# By Mike Jolley, based on work by Barry Kooij ;)
# License: GPL v3

# This program is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.

# This program is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.

# You should have received a copy of the GNU General Public License
# along with this program.  If not, see <http://www.gnu.org/licenses/>

# ----- START EDITING HERE -----

# The slug of your WordPress.org plugin
PLUGIN_SLUG="smaily-for-woocommerce"

# Wordpress.org SVN repository username
SVN_USER="sendsmaily"

# ----- STOP EDITING HERE -----

set -e

# ASK INFO
echo "--------------------------------------------"
echo "      Github to WordPress.org RELEASER      "
echo "--------------------------------------------"
read -p "TAG AND RELEASE VERSION: " VERSION
echo "--------------------------------------------"
echo ""
echo "Before continuing, confirm that you have done the following :)"
echo ""
read -p " - Added a changelog for "${VERSION}"?"
read -p " - Set version in the readme.txt and main file to "${VERSION}"?"
read -p " - Set stable tag in the readme.txt file to "${VERSION}"?"
read -p " - Updated the POT file?"
read -p " - Committed all changes up to GITHUB?"
echo ""
read -p "PRESS [ENTER] TO BEGIN RELEASING "${VERSION}

# VARS
GIT_REPO_PATH=`dirname $(readlink -f $0)`
TEMP_SVN_REPO="/tmp/${PLUGIN_SLUG}-svn"
SVN_REPO="https://plugins.svn.wordpress.org/${PLUGIN_SLUG}/"

# CHECKOUT SVN DIR IF NOT EXISTS
if [[ ! -d $TEMP_SVN_REPO ]];
then
    echo "Checking out WordPress.org plugin repository"
    svn checkout $SVN_REPO $TEMP_SVN_REPO --depth immediates || { echo "Unable to checkout repo."; exit 1; }
    svn update $TEMP_SVN_REPO/assets --set-depth infinity
    svn update $TEMP_SVN_REPO/tags/${VERSION} --set-depth infinity
    svn update $TEMP_SVN_REPO/trunk --set-depth infinity
fi

# Ensure we are on master branch.
echo "Switching to branch"
git checkout ${VERSION} || { echo "Unable to checkout branch."; exit 1; }

echo ""
read -p "PRESS [ENTER] TO DEPLOY VERSION "${VERSION}

# MOVE INTO SVN DIR
cd $TEMP_SVN_REPO

# UPDATE SVN
echo "Updating SVN"
svn update || { echo "Unable to update SVN."; exit 1; }

# UPDATE ASSETS
echo "Updating assets"
rm -Rf assets/
cp -R $GIT_REPO_PATH/assets assets/

# REPLACE TRUNK
echo "Replacing trunk"
rm -Rf trunk/
cp -R $GIT_REPO_PATH trunk/

# REMOVE UNWANTED FILES & FOLDERS
echo "Removing unwanted files"
rm -Rf trunk/.git
rm -Rf trunk/.github
rm -Rf trunk/.vscode
rm -Rf trunk/.wordpress-org
rm -Rf trunk/apigen
rm -Rf trunk/assets
rm -Rf trunk/tests
rm -f trunk/.coveralls.yml
rm -f trunk/.editorconfig
rm -f trunk/.gitattributes
rm -f trunk/.gitignore
rm -f trunk/.gitmodules
rm -f trunk/.jscrsrc
rm -f trunk/.jshintrc
rm -f trunk/.scrutinizer.yml
rm -f trunk/.stylelintrc
rm -f trunk/.travis.yml
rm -f trunk/apigen.neon
rm -f trunk/CHANGELOG.txt
rm -f trunk/CODE_OF_CONDUCT.md
rm -f trunk/composer.json
rm -f trunk/composer.lock
rm -f trunk/CONTRIBUTING.md
rm -f trunk/docker-compose.yml
rm -f trunk/Dockerfile
rm -f trunk/Gruntfile.js
rm -f trunk/package.json
rm -f trunk/phpcs.xml
rm -f trunk/phpunit.xml
rm -f trunk/phpunit.xml.dist
rm -f trunk/README.md
rm -f trunk/release.sh

# DO THE ADD ALL NOT KNOWN FILES UNIX COMMAND
svn add --force * --auto-props --parents --depth infinity -q

# DO THE REMOVE ALL DELETED FILES UNIX COMMAND
MISSING_PATHS=$( svn status | sed -e '/^!/!d' -e 's/^!//' )

# iterate over filepaths
for MISSING_PATH in $MISSING_PATHS; do
    svn rm --force "$MISSING_PATH"
done

# COPY TRUNK TO TAGS/$VERSION
echo "Copying trunk to new tag"
if [[ -d $TEMP_SVN_REPO/tags/${VERSION} ]];
then
    svn rm tags/${VERSION} || { echo "Failed to remove tag."; exit 1; }
fi
svn copy trunk tags/${VERSION} || { echo "Unable to create tag."; exit 1; }

# DO SVN COMMIT
echo "Showing SVN status"
svn status

# PROMPT USER
echo ""
read -p "PRESS [ENTER] TO COMMIT RELEASE "${VERSION}" TO WORDPRESS.ORG"
echo ""

# DEPLOY
echo ""
echo "Committing to WordPress.org...this may take a while..."
svn commit --username=$SVN_USER -m "Release "${VERSION}", see readme.txt for the changelog." || { echo "Unable to commit."; exit 1; }

# REMOVE THE TEMP DIRS
echo "CLEANING UP"
cd $GIT_REPO_PATH && git checkout master
rm -Rf $TEMP_SVN_REPO

# DONE, BYE
echo "RELEASER DONE :D"

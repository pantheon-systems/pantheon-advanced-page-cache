#!/bin/bash

###
# Prepare a Pantheon site environment for the Behat test suite, by installing
# and configuring the plugin for the environment. This script is architected
# such that it can be run a second time if a step fails.
###

terminus whoami > /dev/null
if [ $? -ne 0 ]; then
	echo "Terminus unauthenticated; assuming unauthenticated build"
	exit 0
fi

if [ -z "$TERMINUS_SITE" ] || [ -z "$TERMINUS_ENV" ]; then
	echo "TERMINUS_SITE and TERMINUS_ENV environment variables must be set"
	exit 1
fi

if [ -z "$WORDPRESS_ADMIN_USERNAME" ] || [ -z "$WORDPRESS_ADMIN_PASSWORD" ]; then
	echo "WORDPRESS_ADMIN_USERNAME and WORDPRESS_ADMIN_PASSWORD environment variables must be set"
	exit 1
fi

set -ex

###
# Create a new environment for this particular test run.
###
terminus env:create $TERMINUS_SITE.dev $TERMINUS_ENV
terminus env:wipe $SITE_ENV --yes

###
# Get all necessary environment details.
###
PANTHEON_GIT_URL=$(terminus connection:info $SITE_ENV --field=git_url)
PANTHEON_SITE_URL="$TERMINUS_ENV-$TERMINUS_SITE.pantheonsite.io"
PREPARE_DIR="/tmp/$TERMINUS_ENV-$TERMINUS_SITE"
BASH_DIR="$( cd -P "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

###
# Switch to git mode for pushing the files up
###
terminus connection:set $SITE_ENV git
rm -rf $PREPARE_DIR
git clone -b $TERMINUS_ENV $PANTHEON_GIT_URL $PREPARE_DIR

###
# Add the copy of this plugin itself to the environment
###
rm -rf $PREPARE_DIR/wp-content/plugins/pantheon-advanced-page-cache
cd $BASH_DIR/..
rsync -av --exclude='node_modules/' --exclude='vendor/' --exclude='tests/' ./* $PREPARE_DIR/wp-content/plugins/pantheon-advanced-page-cache
rm -rf $PREPARE_DIR/wp-content/plugins/pantheon-advanced-page-cache/.git

PHP_VERSION="$(terminus env:info $SITE_ENV --field=php_version)"
echo "PHP Version: $PHP_VERSION"

###
# Push files to the environment
###
cd $PREPARE_DIR
git add wp-content
git config user.email "pantheon-advanced-page-cache@getpantheon.com"
git config user.name "Pantheon"
git commit -m "Include Pantheon Advanced Page Cache and its configuration files"
git push

# Wait for Pantheon to process the git push and initialize the environment
terminus workflow:wait "$SITE_ENV" --max=300

###
# Set up WordPress, theme, and plugins for the test run
###
# Silence output so as not to show the password.
{
  terminus wp $SITE_ENV -- core install --title=$TERMINUS_ENV-$TERMINUS_SITE --url=$PANTHEON_SITE_URL --admin_user=$WORDPRESS_ADMIN_USERNAME --admin_email=pantheon-advanced-page-cache@getpantheon.com --admin_password=$WORDPRESS_ADMIN_PASSWORD
} &> /dev/null
terminus wp $SITE_ENV -- cache flush
terminus wp $SITE_ENV -- plugin activate pantheon-advanced-page-cache
terminus wp $SITE_ENV -- theme activate twentytwentythree
terminus wp $SITE_ENV -- rewrite structure '/%year%/%monthnum%/%day%/%postname%/'
# Add the pantheon-cache option. We're assuming it doesn't already exist.
terminus wp $SITE_ENV -- option add pantheon-cache '{"default_ttl":600,"maintenance_mode":"disabled"}' --format=json

# Multidevs do not reliably inherit the site's CDN, and results differ per CDN.
echo "=== CDN serving https://$PANTHEON_SITE_URL ==="
CDN_HEADERS=$(curl -sI "https://$PANTHEON_SITE_URL" || true)
echo "$CDN_HEADERS" | grep -iE "^(HTTP/|server|cf-ray|cf-mitigated|x-served-by|via):" || echo "no CDN headers returned"
if echo "$CDN_HEADERS" | grep -qi "^server: cloudflare"; then
  echo "CDN: Cloudflare"
elif echo "$CDN_HEADERS" | grep -qi "varnish"; then
  echo "CDN: Fastly"
else
  echo "CDN: unknown"
fi


#!/bin/bash

REPODIR=/var/dev/clone-website
WWWDIR=/var/dev/www

cd "$REPODIR"

echo "Updating GIT repo"
git stash && git stash drop
git pull origin master

# Show the maintenance message
echo "Down for maintenance" > "$WWWDIR/index.html"

# Copy server.properties config file to $REPODIR/deploy
cp /var/dev/deploy/server.properties "$REPODIR/deploy/"

# Run Phing
cd "$REPODIR/deploy"
echo "Running phing"
phing -f server.xml

if [ $? -ne 0 ]; then
        echo "Deploy failed" > "$WWWDIR/index.html"
        exit 1
fi

# Put current commit hash into git_head
git rev-parse HEAD > "$REPODIR/www/git_head"

# Create symlink for images folder
ln -s /var/www/www/sites/default/images/blog "$REPODIR/www/sites/default/images"

# Make the new www public
rm -rf "$WWWDIR"
mv "$REPODIR/www" "$WWWDIR"


#!/usr/bin/env bash

REPODIR=/var/dev/clone-website
WWWDIR=/var/dev/www
BAKDIR="$WWWDIR.bak-$(date +%y%m%d%H%M%S)"

cd "$REPODIR"

echo "Updating GIT repo"
git pull origin master

echo "Replacing production files"

if [ -d "$WWWDIR" ]; then
	mv "$WWWDIR" "$BAKDIR"
fi

mkdir -p "$WWWDIR"
echo "Down for maintenance" > "$WWWDIR/index.html"

cp -r "$REPODIR/www/" "$WWWDIR/../"

cd "$REPODIR/../deploy"

echo "Running phing"
phing -f server.xml

if [ $? -ne 0 ]; then
        echo -e "\033[1;31mBuild script failed.\033[m"
        exit 1
fi

# Restore wiki config file
cp "$BAKDIR/wiki/LocalSettings.php" "$WWWDIR/wiki/"

rm "$WWWDIR/index.html"

echo "Done"


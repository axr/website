#!/usr/bin/env bash

REPODIR=/var/dev/clone-website
WWWDIR=/var/dev/www

cd "$REPODIR"

echo "Updating GIT repo"
git pull


echo "Replacing production files"

if [ -d "$WWWDIR" ]; then
	mv "$WWWDIR" "$WWWDIR.bak-$(date +%y%m%d%H%M%S)"
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

rm "$WWWDIR/index.html"

echo "Done"


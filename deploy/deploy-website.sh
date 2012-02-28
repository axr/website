#!/usr/bin/env bash

REPODIR=/path/to/git/repo/root
WWWDIR=/path/to/production/www

cd "$REPODIR"

echo "Updating GIT repo"
git pull

cd "$REPODIR/deploy"

echo "Running phing"
phing -f server.xml

if [ $? -ne 0 ]; then
	echo -e "\033[1;31mBuild script failed.\033[m"
	exit 1
fi

echo "Replacing production files"

if [ -d "$WWWDIR" ]; then
	mv "$WWWDIR" "$WWWDIR.bak-$(date +%y%m%d%H%M%S)"
fi

mkdir -p "$WWWDIR"
echo "Down for maintenance" > "$WWWDIR/index.html"

cp -r "$REPODIR/www"/* "$WWWDIR"

rm "$WWWDIR/index.html"

echo "Done"


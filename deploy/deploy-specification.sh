#!/bin/bash

LOCKFILE=/tmp/deploy-specification.lock

if [ -f "$LOCKFILE" ]; then
	echo "The deploy script is already running"
	exit 1
fi

touch "$LOCKFILE"

# Change to working directory
cd /var/dev/clone-specification/

# Clean the repo
git reset --hard HEAD
git clean -f

# Get the SHA before pull
SHA_BEFORE=$(git rev-parse HEAD)

# Pull
git pull origin master

if [ "$SHA_BEFORE" = $(git rev-parse HEAD) ]; then
	rm "$LOCKFILE"
	echo "No new changes received. Nothing new to deploy"
	exit 0
fi

# Move files to www
rm -rf /var/dev/spec/
mkdir /var/dev/spec/
cp -rf * /var/dev/spec/

ln -s /var/www-shared/images/spec /var/dev/spec/images

rm "$LOCKFILE"

echo "Operation successful"


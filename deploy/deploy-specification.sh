#! /usr/bin/env bash

#change to working directoryy
cd ../clone-specification/

#clean the repo
git reset --hard HEAD
git clean -f

#update the clone
git pull

#move files to www
cp -rf * ../spec/

echo "Operation successful"

#! /usr/bin/env bash

#change to working directory
cd ../clone/

#update the clone
git pull

#move files to www
cp -rf www/* ../www/

echo "Operation successful"

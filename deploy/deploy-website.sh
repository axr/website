#! /usr/bin/env bash

#change to working directoryy
cd ../clone-website/

#update the clone
git pull

#move files to www
cp -rf www/* ../www/

cd ../deploy/

phing build.xml

echo "Operation successful"

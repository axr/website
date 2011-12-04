#! /usr/bin/env bash

#change to working directoryy
cd ../clone-specification/

#update the clone
git pull

#move files to www
cp -rf * ../spec/

echo "Operation successful"

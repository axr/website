#! /usr/bin/env bash

#clone the repo
#git clone -q "${1}" "clones/${2}"

cd "../clone/"

#update the submodules (how do we handle errors here?)
git pull

cp -rf www/* ../www/

echo "Operation successful"

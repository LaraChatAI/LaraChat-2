#!/bin/bash

# Refresh master branch with latest changes and rebuild
git checkout master && git reset --hard HEAD && git pull origin master

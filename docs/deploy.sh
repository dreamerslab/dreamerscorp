#!/bin/bash

# Copy this script to $HOME and run it

path=/srv/www/dreamerscorp.com
tmp=/srv/www/tmp

echo 'Cloning repo from github...'
git clone git@github.com:dreamerslab/dreamerscorp.git $tmp
echo '...done!'
echo ''

echo 'Cloning configs...'
cp configs/dreamercorp/wp-config.php $tmp/blog/wp-config.php
echo '...done!'
echo ''

echo 'Prepare for wp caching plugins...'
sudo mkdir -p $tmp/blog/wp-content/cache/hyper-cache/
sudo mkdir -p $tmp/blog/wp-content/tmp/
sudo mkdir -p $tmp/blog/wp-content/tmp/links/
sudo mkdir -p $tmp/blog/wp-content/tmp/options/
sudo mkdir -p $tmp/blog/wp-content/tmp/posts/
sudo mkdir -p $tmp/blog/wp-content/tmp/terms/
sudo mkdir -p $tmp/blog/wp-content/tmp/users/
sudo chown -R www-data:www-data $tmp/blog
echo '...done!'
echo ''

echo 'Backing up old version...'
mv $path $path`date +"%Y%m%d%H%M%S"`
echo '...done!'
echo ''

echo 'Switch to latest version...'
mv $tmp $path
echo '...done!'
echo ''
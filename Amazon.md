Pebblecube Amazon image config scripts
===
This file contains scripts used to configure an Amazon image on wich pebblecube could run.

## Image id: 
the image id used is the: ami-cef405a7 - http://uec-images.ubuntu.com/releases/maverick/release/

## Apache
    sudo apt-get install apache2
    sudo apt-get install php5
    sudo apt-get install libapache2-mod-php5
    sudo apt-get install php5-gd
    sudo apt-get install php5-mcrypt
    sudo /etc/init.d/apache2 restart

### Apache SSL 
(not necessary if not running APIs on SSL)

    sudo a2enmod ssl
    sudo a2ensite default-ssl
    
### Apache mod rewrite
    
    sudo a2enmod rewrite
    sudo /etc/init.d/apache2 force-reload
    sudo /etc/init.d/apache2 restart

## MongoDb 
[http://www.mongodb.org/display/DOCS/Ubuntu+and+Debian+packages](http://www.mongodb.org/display/DOCS/Ubuntu+and+Debian+packages)

    sudo cp /etc/apt/sources.list /etc/apt/sources.list.backup

edit  /etc/apt/sources.list and add 
	
    deb http://downloads.mongodb.org/distros/ubuntu 10.10 10gen
    deb http://downloads-distro.mongodb.org/repo/ubuntu-upstart dist 10gen

then

    sudo apt-key adv --keyserver keyserver.ubuntu.com --recv 7F0CEB10
    sudo apt-get update 
    sudo apt-get install mongodb-10gen
    sudo mkdir -p /data/db/
    sudo apt-get install php-pear 
    sudo apt-get install php5-dev
    sudo apt-get install libcurl3-openssl-dev 
    sudo pecl install mongo

## Memcached

    sudo apt-get install memcached
    sudo pecl install memcache
    sudo easy_install python-memcached

## S3 Lib using pear

    sudo pear channel-discover pear.amazonwebservices.com
    sudo pear remote-list -c aws
    sudo pear install aws/sdk

## Maxmind Ip

    easy_install pygeoip
    wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
    gunzip GeoLiteCity.dat.gz
    sudo mkdir -v /usr/share/GeoIP
    sudo mv -v GeoLiteCity.dat /usr/share/GeoIP/GeoIPCity.dat
    sudo apt-get install php5-geoip
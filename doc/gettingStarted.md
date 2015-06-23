Getting Started
===============

This is a super simple getting started guide for bringing up your own instance of Shields Up on Mac OS X 10.10

## Clone repo

Start by pulling a copy of the Shields up repository!

```
% git clone git@github.com:oapi/shieldsup.git
% cd shieldsup
```

## Install Ruby Dependencies

You'll need the following ruby gems:
* logger
* mysql2
* twitter

Install them like so:

```
% gem install logger mysql2 twitter
```

## Setup a MySQL

Find a mysql you can use (not covered here) and get the schema into it like so:

```
% mysql -h db.example.com -u yourUser -e 'CREATE DATABASE `shieldsup`'
% mysql -h db.example.com -u yourUser -e 'GRANT ALL ON `shieldsup`.* TO '\''shieldsup'\''@'\''%'\'' IDENTIFIED BY '\''superSecurePassword'\'''
% mysql -h db.example.com -u yourUser -D shieldsup < doc/db_schema.txt
```

## Create a config

Most easily done by copying the `conf.yaml.sample` file and editing that:

```
% cp conf.yaml.sample conf.yaml
```

## Start the Ruby Server

```
% bin/server.rb
```

## Get apache to serve Shields Up PHP App

This is a super hacky method to get the stock apache webserver in OS X Yosemite to get at the Shields up app:

Uncomment `LoadModule php5_module libexec/apache2/libphp5.so` in `/etc/apache2/httpd.conf` then setup a symlink to your `shieldsup/web` path

```
% pathToShieldsUp='/path/to/shieldsup'
% sudo ln -s "${pathToShieldsUp}/web" /Library/WebServer/Documents/shieldsup
% sudo apachectl restart
```

At this point, navigating to [http://localhost/shieldsup](http://localhost/shieldsup) should load the PHP app. YOU'RE NOT DONE YET.

## Setup PHP config

Most easily done by copying the `conf.php.sample` file under the `web/` directory and editing that:

```
% cp web/conf.php.sample web/conf.php
```

## Now you're done!

Go to [http://localhost/shieldsup](http://localhost/shieldsup) and check it out!

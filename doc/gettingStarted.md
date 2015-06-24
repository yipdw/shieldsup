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

Install the Ruby dependencies like so:

```
% bundle install
```

If you get an error running this command, make sure you have the MySQL libraries installed, as well as Bundler (which can be installed with `gem install bundler`).

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
% bundle exec thin -p 8080 start
```

Go to [http://localhost:8080](http://localhost:8080) and check it out!

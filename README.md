# Laravel DB Sync

![](https://repository-images.githubusercontent.com/506690782/a5b01352-4869-4e6d-8e46-d44e93c960df)

## Introduction
Sync remote database to a local database

>Note this requires remote MySQL access in order to connect to a remote MySQL database. It does not use SSH currently. I'm open to PR's

## Install

Install the package.

```bash
composer require dcblogdev/laravel-db-sync
```

## Config

You can publish the config file with:

```
php artisan vendor:publish --provider="Dcblogdev\DbSync\DbSyncServiceProvider" --tag="config"
``` 

## .env 

Set the remote database credentials in your .env file

```
REMOTE_DATABASE_HOST=
REMOTE_DATABASE_USERNAME=
REMOTE_DATABASE_NAME=
REMOTE_DATABASE_PASSWORD=
REMOTE_DATABASE_IGNORE_TABLES=''
```

Set a comma seperate list of tables NOT to export in `REMOTE_DATABASE_IGNORE_TABLES`

## Usage

To export a remote database to OVERRIDE your local database by running:

```bash
php artisan db:production-sync
```

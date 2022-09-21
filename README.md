# Laravel DB Sync

![DB Dync](https://repository-images.githubusercontent.com/506690782/a5b01352-4869-4e6d-8e46-d44e93c960df)

## Introduction
Sync remote database to a local database

> A word of warning you should only sync a remote database into a local database if you have permission to do so within your organisation's policies. I'm syncing during early phases of development where the data is largely test data and not actual customer data.

Connection can be made over SSH or using a remote MySQL connection.

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

When using SSH Add:
```
REMOTE_USE_SSH=true
REMOTE_SSH_PORT=22
REMOTE_SSH_USERNAME=
REMOTE_DATABASE_HOST=

REMOTE_DATABASE_USERNAME=
REMOTE_DATABASE_PORT=3306
REMOTE_DATABASE_NAME=
REMOTE_DATABASE_PASSWORD=
REMOTE_DATABASE_IGNORE_TABLES=''

REMOTE_REMOVE_FILE_AFTER_IMPORT=true
REMOTE_IMPORT_FILE=true
```

For only MySQL remote connections:
```
REMOTE_DATABASE_HOST=
REMOTE_DATABASE_USERNAME=
REMOTE_DATABASE_PORT=3306
REMOTE_DATABASE_NAME=
REMOTE_DATABASE_PASSWORD=
REMOTE_DATABASE_IGNORE_TABLES=''

REMOTE_REMOVE_FILE_AFTER_IMPORT=true
REMOTE_IMPORT_FILE=true
```

Set a comma seperate list of tables NOT to export in `REMOTE_DATABASE_IGNORE_TABLES`

To generate a SQL with a custom file name `REMOTE_DEFAULT_FILE_NAME`

To specify a different local database connection:
```
LOCAL_DATABASE_CONNECTION=different_mysql_connection
```

## Usage

To export a remote database to OVERRIDE your local database by running:

```bash
php artisan db:production-sync
```

Provide a filename for export on the fly by passing the option --filename, remember to provide .sql

```bash 
php artisan db:production-sync --filename=other.sql
```

Run the command without attempting to export:

```bash 
php artisan db:production-sync --test
```

## Aliases

There are shortcuts that can be used:

`-T` = will use `--test`
`F` = will use `--filename`

## Alternative name

When connecting to a none production database, say a staging database you can choose to use this alternative name:

`db:remote-sync` may be used instead of `db:production-sync`

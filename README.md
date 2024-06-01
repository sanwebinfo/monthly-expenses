# Expense Manager

A Simple website to Manage your Monthly Expenses and Track your Due date and paid status.

## Built using

- HTML
- Bulma CSS
- Javascript
- Fetch API
- PHP PDO
- PHP CRUD Operation
- MYSQL

## Setup

- Download project or clone the repo
- open `query.sql` file :  Copy `sql` queries to create database tables for storing user's expense data
- create `.env` and fill the required details

```env
APIKEY=xxxxxxxxxx
DBHOST=xxxxxxxxxxx
DBNAME=xxxxxxxxxx
DBUSER=xxxxxxx
DBPASSWORD=xxxxxxx
```

- Create New user

```sh

# start server
php -S localhost:6006

## New User
curl --request POST \
  --url http://localhost:6006/api/user.php \
  --header 'Authorization: Bearer xxxxxxxxxxxx' \
  --header 'Content-Type: application/json' \
  --data '{
  "username": "admin",
  "password": "admin123456789"
}
'

```

- Now Just open a webpage in browser > login > start adding your monthly expenses
- Done

## LICENSE

MIT

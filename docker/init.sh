#!/bin/bash

RET=1

while [[ RET -ne 0 ]]; do
    sleep 1;
    mysql -e 'exit' > /dev/null 2>&1; RET=$?
done

DB_NAME=uteg

cd /srv
php app/console doctrine:schema:update --force

mysqladmin -u root create $DB_NAME
mysql -u root < /mysqlcommands

if [ -n "$INIT" ]; then
    /srv/$INIT
fi
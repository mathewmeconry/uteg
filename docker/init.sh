#!/bin/bash

RET=1

while [[ RET -ne 0 ]]; do
    sleep 1;
    mysql -e 'exit' > /dev/null 2>&1; RET=$?
done

DB_NAME=uteg

<<<<<<< HEAD
mysqladmin -u root create $DB_NAME
=======

mysqladmin -u root create $DB_NAME

cd /srv
php app/console doctrine:schema:update --force

mysql -u root < /mysqlcommands
>>>>>>> refs/heads/EGT

if [ -n "$INIT" ]; then
    /srv/$INIT
fi
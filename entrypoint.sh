#!/bin/bash
set -e

# Wait for the database service to be available
until nc -z -v -w30 mariadb 3306
do
  echo "Waiting for database connection..."
  sleep 5
done

# Import the SQL dump into the database if it exists
if [ -f "/docker-entrypoint-initdb.d/staff.sql" ]; then
  echo "Importing hrm_attend.sql..."
  mysql -h mariadb -u root -p"$MYSQL_ROOT_PASSWORD" staff < /docker-entrypoint-initdb.d/staff.sql
fi

# Start PHP-FPM
php-fpm


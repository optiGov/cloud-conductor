#!/bin/bash

# migrate the application
php artisan app:migrate

# remove storage/tmp files
rm -rf storage/tmp/*

# start supervisor
supervisord -c docker/supervisor/supervisord.conf

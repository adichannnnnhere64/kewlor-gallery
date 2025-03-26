#!/bin/bash

access_token=$(curl -s -X POST 'https://api.cloudways.com/api/v1/oauth/access_token' \
  -d "api_key=$API_KEY&email=olaf@kewlor.com" | jq -r '.access_token')

if [ -z "$access_token" ]; then
 echo "Failed to obtain access_token"
 exit 1
fi

curl 'https://api.cloudways.com/api/v1/git/pull' -d 'server_id=941620&app_id=5363982&branch_name=main' -H "Authorization: Bearer $access_token"

php artisan optimize:clear;
sleep 5;
php artisan migrate;
npm i;
npm run build;
composer install;
php artisan optimize;

To get the project setup,
Pull the files,
Setup your local dev environment
PHP: up to version 8.3
Enable PHP extensions: openssl, gd, zip, curl, fileinfo
NOTE THAT DUE TO COPYRIGHT ISSUES, THE NOVA PACKAGE IS NOT INCLUDED AS IT IS PAID. TO PURCHASE, PLEASE GO TO websitehere
To install the nova package, just copy the nova files (unzipped) to ./vendor/nova
and add the custom repository to your composer.json fileinfo
Custom repository code
"repositories": [
{
"type": "path",
"url": "./vendor/nova"
}
],

Then Run the following commands in terminal:

'''
composer install
composer update
npm install && npm run build
php artisan migrate:fresh --seed
'''

Start serving the app:

php artisan serve

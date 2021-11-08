## Cleareststake Backend

### Install and Deploy

Relies on Laravel PHP, and Mysql if hosting locally

```bash
sudo apt -y install apache2
sudo a2enmod rewrite
sudo a2enmod headers
sudo a2enmod ssl
sudo apt -y install software-properties-common
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get install -y php7.4
sudo apt-get install -y php7.4-{bcmath,bz2,intl,gd,mbstring,mysql,zip,common,curl,xml}
php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
sudo php composer-setup.php --install-dir=/usr/local/bin --filename=composer
php -r "unlink('composer-setup.php');"
```

Setup the repo according to our VHOST path. Note, the actual VHOST path in this case should be set to **/var/www/cleareststake-backend/public**

```bash
cd /var/www/
git clone https://github.com/ledgerleapllc/cleareststake-backend
cd cleareststake-backend
```

Install packages and setup environment

```bash
composer install
composer update
cp .env.example .env
```

After adjusting .env with your variables, run Artisan to finish setup

```bash
php artisan key:generate
php artisan migrate
php artisan passport:install
php artisan config:clear
php artisan route:clear
php artisan cache:clear
(crontab -l 2>>/dev/null; echo "* * * * * cd /var/www/cleareststake-backend && php artisan schedule:run >> /dev/null 2>&1") | crontab -
```

You may also have to authorize Laravel to write to the storage directory

```bash
sudo chown -R www-data:www-data storage/
```

Last, you need to setup roles and admins to start using the portal and see it work. Visit the URL of the backend with the path **/install**. This will install these things for you. You will find your admin credentials generated in the Laravel log file. You may want to disable this endpoint after the initial install to prevent this install endpoint from being used again if you are planning on deploying to a production environment in the future.

### Contributing

Fork and create a PR if you find and want to provide improvements.

For security related issues, please email thomas@ledgerleap.com
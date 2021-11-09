<p align="center">
	<img src="https://cleareststake.com/cleareststake.png" width="400">
</p>


## Cleareststake Backend

Administration portal for fund management of LP users. This is backend repo of the portal. To see the frontend repo, visit https://github.com/ledgerleapllc/cleareststake-frontend

### Install and Deploy

Relies on Laravel PHP, server software (Apache/Nginx), and Mysql if hosting locally

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


### Usage Guide

For full functionality we recommend adding a key for Sendgrid and a Coin Market Cap API key to support all features.

**Start here -**

After deployment of the portal, log in with the admin code.

Next, you will want to invite your first user (LP) via the Add User button. Enter a name, last name, email, starting balance, and select whether or not the use in "In Fund." This last selection allows tracking Fund LPs separate from other users who are just staking to a given node.

In the live version (with the mailer keys added - we recommend sendgrid) the user will get an email. They will set a password and can then log in to their portal.

Users can see their current holdings, ask questions to admin, request a withdrawal, and track metrics such as all transactions and inflation transactions. They can also update their own email and password. Most LP user functions are designed to give transparency to the user because they are limited in what they can manage. ClearestStake is primary management software for funds and staking provider so most functionality is on the management side of the portal.

Back on the admin side of the portal the admin has further functions available now that a user is active.

* Admins can click into this user to see their transactions, resend invites, toggle them as a fund user (or out of the fund) and review security information such as IP logging.

* Admins can download the tables via CSV for audits.

* Admins can reset passwords for users.

* Admins can "Update for Inflation" which is designed to be a monthly reconciliation process that will attached any new token balance, defined as tokens in excess of the current total, to EVERY user in portions pro-rata to their current holdings. This is an essential function of the portal. It is accurate to 6 decimal places to maintain proper balances even at very large token balances.

* Admins can "Process Deposit" which will add tokens to a single user and re-tally the  percentages of the total for later "Update for Inflation" actions.

* Admins can "Process Withdrawal" which will subtract tokens from a single user and re-tally the  percentages of the total for later "Update for Inflation" actions.

* Admins can process a "Fund Sale" which calculates a pro-rate removal of a number of tokens from Fund Users ONLY without changing the totals for non-fund (staking provider) users.

**Other notes -**

These features were scoped and determined to be the essential features needed for fund management. All tables are optimized to show the needed information for accounting from the point of view of a staking provider or fund manager. Email any questions to team@ledgerleap.com.
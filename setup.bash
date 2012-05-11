#!/bin/bash

echo "The commands detailed in this setup script are intended to be run by hand so that the deployer learns how the system works."
exit 1

sudo add-apt-repository ppa:kohana/stable
sudo sed -i "s/`lsb_release -cs`/maverick/" /etc/apt/sources.list.d/kohana-stable-lucid.list

sudo apt-get update
sudo apt-get install git-core libkohana3.2-core-php libkohana3.2-mod-auth-php libkohana3.2-mod-cache-php libkohana3.2-mod-codebench-php libkohana3.2-mod-database-php libkohana3.2-mod-image-php libkohana3.2-mod-orm-php libkohana3.2-mod-unittest-php mysql-client mysql-server php5-mysql libmysqlclient15-dev

sudo a2enmod rewrite

cd /srv/www/default

sudo git clone git://github.com/sown/node_control.git .

# Hack httpd to use /srv/www/default
# Hack httpd.conf to allow allow override all

sudo /etc/init.d/apache2 restart
sudo /etc/init.d/mysql restart

cd /srv/www
sudo git clone https://github.com/Flynsarmy/KODoctrine2.git
sudo ln -s /srv/www/KODoctrine2/modules/doctrine2/ /usr/share/php/kohana3.2/modules/doctrine2

cd /tmp
wget http://www.doctrine-project.org/downloads/DoctrineORM-2.2.2-full.tar.gz
tar -zxf DoctrineORM-2.2.2-full.tar.gz
sudo mv DoctrineORM-2.2.2/* /usr/share/php/kohana3.2/modules/doctrine2/classes/vendor/doctrine
rm DoctrineORM-2.2.2-full.tar.gz
rm -r DoctrineORM-2.2.2

echo "CREATE DATABASE sown_data; GRANT ALL PRIVILEGES ON sown_data.* TO 'sown'@'localhost' IDENTIFIED BY 'password'" | mysql -u root
mysql -u sown --password=password sown_data < /srv/www/default/sql/sown_data.sql

sudo chmod 2777 /srv/www/default/application/logs
sudo chmod 2777 /srv/www/default/application/cache
sudo chmod 2777 -R /srv/www/default/application/models/proxies

sudo chown -R root:www-data /srv/www/default/application

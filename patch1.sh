#!/bin/bash
sed -i 's:#DocumentRoot "/opt/app-root/src":DocumentRoot "/var/www/html/":g' /etc/httpd/conf/httpd.conf
sed -i 's:variables_order = "GPCS":variables_order = "GPCES":g' /etc/php.ini
rm -rf /var/cache/dnf && dnf remove -y nodejs && dnf clean all && dnf update -y && dnf upgrade -y
dnf install -y sudo yum-utils php-pear php-devel unzip zip 

#RHEL 8 and Oracle Linux 8
curl https://packages.microsoft.com/config/rhel/8/prod.repo | sudo tee /etc/yum.repos.d/mssql-release.repo

sudo yum remove unixODBC-utf16 unixODBC-utf16-devel #to avoid conflicts
sudo ACCEPT_EULA=Y yum install -y msodbcsql18
# optional: for bcp and sqlcmd
sudo ACCEPT_EULA=Y yum install -y mssql-tools18
echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
source ~/.bashrc
# optional: for unixODBC development headers
# sudo yum install -y unixODBC-devel

sudo yum install -y initscripts unixODBC-2.3.7 unixODBC-devel-2.3.7

sudo pecl channel-update pecl.php.net

sudo pecl install sqlsrv-5.10.1
# wget http://pecl.php.net/get/sqlsrv-5.10.1.tgz
# sudo pear install sqlsrv-5.10.1.tgz

sudo pecl install pdo_sqlsrv-5.10.1
# wget http://pecl.php.net/get/pdo_sqlsrv-5.10.1.tgz
# sudo pear install pdo_sqlsrv-5.10.1.tgz

echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini
echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/20-sqlsrv.ini

sudo pecl install redis

echo extension=redis.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/40-redis.ini

curl -sS https://getcomposer.org/installer | tac | tac | php -- --install-dir=/usr/local/bin --filename=composer
chmod 777 /usr/local/bin/composer
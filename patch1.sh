#!/bin/bash
sed -i 's:#DocumentRoot "/opt/app-root/src":DocumentRoot "/var/www/html/":g' /etc/httpd/conf/httpd.conf
#sed -i '$ a ServerName localhost' /etc/httpd/conf/httpd.conf
#dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm -y
#dnf update -y && dnf install -y php-pear php-devel
sed -i 's:variables_order = "GPCS":variables_order = "GPCES":g' /etc/php.ini

rm -rf /var/cache/dnf && dnf remove -y nodejs && dnf clean all && dnf update -y && dnf upgrade -y
# dnf install -y php-pear php-devel php-pdo unzip zip

yum -y install sudo

sudo yum install -y unixODBC-devel



yum -y install php-pear php-devel php-pdo unzip zip
## sudo yum -y install unixODBC-devel


curl -sS https://getcomposer.org/installer | tac | tac | php -- --install-dir=/usr/local/bin --filename=composer
chmod 777 /usr/local/bin/composer
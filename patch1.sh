#!/bin/bash
sed -i 's:#DocumentRoot "/opt/app-root/src":DocumentRoot "/var/www/html/":g' /etc/httpd/conf/httpd.conf
#sed -i '$ a ServerName localhost' /etc/httpd/conf/httpd.conf
#dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm -y
#dnf update -y && dnf install -y php-pear php-devel
mkdir -p /opt/ibm
sed -i 's:variables_order = "GPCS":variables_order = "GPCES":g' /etc/php.ini

rm -rf /var/cache/dnf && dnf remove -y nodejs && dnf clean all && dnf update -y && dnf upgrade -y
# dnf install unzip zip libpng-devel libjpeg-turbo-devel postgresql-devel
# dnf install -y unzip zip 
dnf install -y php-pear php-devel unzip zip 
curl -sS https://getcomposer.org/installer | tac | tac | php -- --install-dir=/usr/local/bin --filename=composer
chmod 777 /usr/local/bin/composer

#echo "<?php phpinfo();?>" > /var/www/html/index.php
#echo 'auto_prepend_file="/var/www/html/php/siteheader.php"' > /etc/php.d/rob.ini
#echo 'auto_append_file="/var/www/html/php/sitefooter.php"' >> /etc/php.d/rob.ini
#echo 'memory_limit=512M' >> /etc/php.d/rob.ini

# 
# docker run -dit -p 80:8080 -v /root/local/sbe-dashboard:/var/www/html/ --name sbe sbe
# docker run -dit -p 80:8080 --name rob rob
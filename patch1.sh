#!/bin/bash
sed -i 's:#DocumentRoot "/opt/app-root/src":DocumentRoot "/var/www/html/":g' /etc/httpd/conf/httpd.conf
#sed -i '$ a ServerName localhost' /etc/httpd/conf/httpd.conf
#dnf install https://dl.fedoraproject.org/pub/epel/epel-release-latest-8.noarch.rpm -y
#dnf update -y && dnf install -y php-pear php-devel
sed -i 's:variables_order = "GPCS":variables_order = "GPCES":g' /etc/php.ini

rm -rf /var/cache/dnf && dnf remove -y nodejs && dnf clean all && dnf update -y && dnf upgrade -y
# dnf install -y php-pear php-devel php-pdo unzip zip

yum -y install sudo

#Download appropriate package for the OS version
#Choose only ONE of the following, corresponding to your OS version

# #RHEL 8 and Oracle Linux 8
# sudo curl https://packages.microsoft.com/config/rhel/8/prod.repo > /etc/yum.repos.d/mssql-release.repo

# sudo yum remove unixODBC-utf16 unixODBC-utf16-devel #to avoid conflicts
# sudo ACCEPT_EULA=Y yum install -y msodbcsql18
# # optional: for bcp and sqlcmd
# sudo ACCEPT_EULA=Y yum install -y mssql-tools18
# echo 'export PATH="$PATH:/opt/mssql-tools18/bin"' >> ~/.bashrc
# source ~/.bashrc
# # optional: for unixODBC development headers
# sudo yum install -y unixODBC-devel


#RHEL 8 and Oracle Linux 8
sudo curl https://packages.microsoft.com/config/rhel/8/prod.repo > /etc/yum.repos.d/mssql-release.repo

sudo yum remove unixODBC-utf16 unixODBC-utf16-devel #to avoid conflicts
sudo ACCEPT_EULA=Y yum install -y msodbcsql17
# optional: for bcp and sqlcmd
sudo ACCEPT_EULA=Y yum install -y mssql-tools
echo 'export PATH="$PATH:/opt/mssql-tools/bin"' >> ~/.bashrc
source ~/.bashrc
# optional: for unixODBC development headers
sudo yum install -y unixODBC-devel



yum -y install php-pear php-devel php-pdo unzip zip
## sudo yum -y install unixODBC-devel
sudo pecl install sqlsrv-5.10.1
sudo pecl install pdo_sqlsrv-5.10.0


echo extension=pdo_sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/30-pdo_sqlsrv.ini
echo extension=sqlsrv.so >> `php --ini | grep "Scan for additional .ini files" | sed -e "s|.*:\s*||"`/20-sqlsrv.ini


curl -sS https://getcomposer.org/installer | tac | tac | php -- --install-dir=/usr/local/bin --filename=composer
chmod 777 /usr/local/bin/composer
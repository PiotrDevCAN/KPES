FROM registry.access.redhat.com/ubi8/php-74
USER root
ADD . /var/www/html/
ADD ./patch1.sh /patch1.sh
RUN bash /patch1.sh && rm /patch1.sh
RUN dnf -y install php-zip
RUN dnf remove nginx-filesystem -y
WORKDIR /var/www/html/
RUN composer install --no-interaction
USER root
ADD ./patch2.sh /patch2.sh
RUN bash /patch2.sh && rm /patch2.sh
ENTRYPOINT ["httpd", "-D", "FOREGROUND"]
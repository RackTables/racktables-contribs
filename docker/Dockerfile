FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt update && apt-get install apache2-bin libapache2-mod-php7.4 php7.4-gd php7.4-mysql php7.4-mbstring php7.4-bcmath php7.4-json php7.4-snmp php7.4-curl snmp-mibs-downloader -y

RUN download-mibs && sed -i '4 s/^/#/' /etc/snmp/snmp.conf

COPY . /app

RUN cp /app/racktables.conf /etc/apache2/sites-available/racktables.conf

RUN a2enmod dir

RUN wget https://github.com/RackTables/racktables/archive/refs/tags/RackTables-0.22.0.tar.gz -O /app/RackTables-0.22.0.tar.gz

RUN tar -xvf /app/RackTables-0.22.0.tar.gz -C /app/

RUN ln -s /app/racktables-RackTables-0.22.0/wwwroot /var/www/html/racktables

RUN a2ensite racktables && a2dissite 000-default

RUN apache2ctl -t && apache2ctl graceful

RUN touch /app/racktables-RackTables-0.22.0/wwwroot/inc/secret.php && chown www-data:nogroup -R /app/racktables-RackTables-0.22.0/wwwroot && chmod -R 0700 /app/racktables-RackTables-0.22.0/wwwroot && chmod 600 /app/racktables-RackTables-0.22.0/wwwroot/inc/secret.php

EXPOSE 80

CMD apachectl -DFOREGROUND

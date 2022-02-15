FROM ubuntu:20.04

ARG DEBIAN_FRONTEND=noninteractive

RUN apt update && apt-get install apache2-bin libapache2-mod-php7.4 php7.4-gd php7.4-mysql php7.4-mbstring php7.4-bcmath php7.4-json php7.4-snmp php7.4-curl snmp-mibs-downloader -y

RUN download-mibs && sed -i '4 s/^/#/' /etc/snmp/snmp.conf

COPY . /app

RUN cp /app/racktables.conf /etc/apache2/sites-available/racktables.conf

RUN a2enmod dir

RUN wget https://sourceforge.net/projects/racktables/files/RackTables-0.21.5.tar.gz/download -O /app/RackTables-0.21.5.tar.gz

RUN tar -xvf /app/RackTables-0.21.5.tar.gz -C /app/

RUN ln -s /app/RackTables-0.21.5/wwwroot /var/www/html/racktables

RUN a2ensite racktables && a2dissite 000-default

RUN apache2ctl -t && apache2ctl graceful

RUN touch /app/RackTables-0.21.5/wwwroot/inc/secret.php && chown www-data:nogroup -R /app/RackTables-0.21.5/wwwroot && chmod -R 0700 /app/RackTables-0.21.5/wwwroot && chmod 600 /app/RackTables-0.21.5/wwwroot/inc/secret.php

EXPOSE 80

CMD apachectl -DFOREGROUND

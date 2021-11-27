Racktables Docker Configuration
---------------------------------------------------------------
11/26/2021 - Steven Burlett <sburlett1313@gmail.com>
---------------------------------------------------------------
---------------------------------------------------------------

INSTALL:

copy Dockerfile, docker-compose.yml and racktables.conf to your racktables directory
change the port in docker-compose.yml for whatever you want to connect to

---------------------------------------------------------------

USAGE:

### Docker setup 
Run:
docker-compose up -d
docker ps - to get container ids
docker inspect {DB_CONTAINER} to get i.p. for setup 

Browse:
http://localhost:8083/racktables/index.php?module=installer

Setup steps 1 - 3

Setup step 4:
docker exec -it {CONTAINER_NAME} /bin/bash
run: chown www-data:nogroup /var/www/racktables/inc/secret.php && chmod 440 /var/www/racktables/inc/secret.php

If you get the "You do not have the SUPER privilege and binary logging is enabled" error the work around is:
docker exec -it {DB_CONTAINER} /bin/bash
mysql -u root -p
set global log_bin_trust_function_creators=1;

Finish setup steps

---------------------------------------------------------------

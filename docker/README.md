# Racktables Docker Configuration

2021-11-26 - Steven Burlett <sburlett1313@gmail.com>

* All the hard work to get it off the ground!

2022-02-15 - https://github.com/NoifP

* Including RackTables download in docker build.
* Fixing issue with `service apache2 reload` failing during build.
* Expanding documentation.

---------------------------------------------------------------

## INSTALL

1. Copy Dockerfile, docker-compose.yml and racktables.conf to your RackTables directory on your docker host (e.g. /opt/racktables).
1. Change the port in docker-compose.yml to whatever you want to connect to ( default is `8083:80` ).
1. Change `MYSQL_PASSWORD:` in docker-compose.yml (You'll need this later in RackTables installation step 3).

---------------------------------------------------------------

## Docker setup

1. Run:
   
   ```bash
   docker-compose up -d
   docker ps - to get container ids
   docker inspect {DB_CONTAINER} to get i.p. for setup
   ```

1. Browse http://localhost:8083/racktables/index.php?module=installer .

1. Complete RackTables Setup steps 1 and 2.
1. RackTables setup step 3 you'll need to enter the following info (note: blank or irrelevant fields are not listed below).

   | Field | Value | note |
   | --- | --- | --- |
   | TCP connection | * | |
   | TCP host | db | |
   | TCP Port | 3306 | |
   | database | racktables_db | |
   | username | racktables_user | |
   | password | change-me | same as `MYSQL_PASSWORD:` in docker-compose.yml |

1. RackTables setup step 4:
   
   ```bash
   docker exec -it {CONTAINER_NAME} /bin/bash
   run: chown www-data:nogroup /var/www/html/racktables/inc/secret.php && chmod 440 /var/www/html/racktables/inc/secret.php
   ```
   
   If you get the "You do not have the SUPER privilege and binary logging is enabled" error the work around is:
   
   ```bash
   docker exec -it {DB_CONTAINER} /bin/bash
   mysql -u root -p
   set global log_bin_trust_function_creators=1;
   ```

1. Finish RackTables setup steps 5 to 7.
1. You should now have a working RackTables! Setup some users!

---------------------------------------------------------------

## Notes

* HTTPS is left as an exercise for the reader (maybe you like Traefik or Nginx).

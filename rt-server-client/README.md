RT-server-client
================

Description
-----------

RT-Server-Client is server discovery script for Racktables project.

It discover system, import or update infromation into racktables database.

If you run script from cron you should have automatic update system for racktables based on actual state of server infrastructure
 
Script support following infromation

    * hostname
    * transfer comment field to server motd (message of the day)
    * commend-edit utility for editing comments on racktables directly from server
    * service tag
    * supermicro exeption for service tag (my supermicro servers has all same ST and Expres ST. I don't know why)
    * for Dell servers: get warranty and support information from Dell website based on server Service Tag
    * Physical and logical interfaces (eth,bond,bridges)
    * IPv4 and IPv6 IP addresses, import and update in database
    * Dell iDrac IP address (require Dell-OMSA Installed)
    * OS Dristribution and Release information
    * HW vendor and product type
    * Hypervisor recognition (Xen 4.x)
    * Virtual server recognition (Xen 4.x)
    * Link Virtual server with hypervisor as container in Racktables
    * Racktables logging - when change ip addresses or virtual link with hypervisor
    * Interface Connection (LLDPD needed for this feature. System automatically link server interfaces with switch ports in RackTables)

TODO

    * support for other virtualization technologies (OpenVZ, KVM)
    * test script on various linux distributions and BSD installations
    * Linux Virtual Server shared IP catalogization
    * better discovery of physical interfaces

Demonstration
-------------
For some description, screenshots and examples visit https://www.cypherpunk.cz/automatic-server-audit-for-racktables-project/

How To ?
--------

For information about config, usage and installation visit please project github at https://github.com/rvojcik/rt-server-client.git

Contact
-------
Robert Vojcik 

robert@vojcik.net

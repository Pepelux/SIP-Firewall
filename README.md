# What is SIP Firewall? #

SIP Firewall es un firewall para sistemas de VoIP que permite filtrar aquellas conexiones que vayan a tu centralita, de forma que bloquee ataques basándose en determinados parámetros como país de procedencia, dirección IP, usuario, dominio, userAgent, etc.

# Technology #

  * _**Backend**_ Backend managed with Kamailio 5 + MariaDB + PHP 7 (for the Rest API).

  * _**frontend**_ Frontend programmed with Angular 6 + Bootstrap 4 + Typescript.

# Installation #

Download of all components:
```
# cd /usr/local/src
# svn co https://github.com/Pepelux/SIP-Firewall.git
```
Install MariaDB and create database and users for Kamailio and web. Please choose strong passwords for all users:
```
# apt-get install mysql-server

# mysql -u root -p
MariaDB [(none)]> CREATE DATABASE security;
Query OK, 1 row affected (0.01 sec)

MariaDB [(none)]> CREATE USER 'kamailio'@'localhost' IDENTIFIED BY 'STRONG_KAMAILIO_PASSWORD';
Query OK, 0 rows affected (0.01 sec)

MariaDB [(none)]> CREATE USER 'security'@'localhost' IDENTIFIED BY 'STRONG_WEB_PASSWORD';
Query OK, 0 rows affected (0.00 sec)

MariaDB [(none)]> GRANT ALL PRIVILEGES ON kamailio.* TO 'kamailio'@'localhost';
Query OK, 0 rows affected (0.00 sec)

MariaDB [(none)]> GRANT ALL PRIVILEGES ON security.* TO 'security'@'localhost';
Query OK, 0 rows affected (0.00 sec)

# mysql -u root -p security < /usr/local/src/SIP-Firewall/config/security.sql
```

Install Kamailio:
```
# echo "deb http://deb.kamailio.org/kamailio51 stretch main" >> /etc/apt/sources.list
# echo "deb-src http://deb.kamailio.org/kamailio51 stretch main" >> /etc/apt/sources.list
# curl http://deb.kamailio.org/kamailiodebkey.gpg | apt-key add -
# apt-get update

# apt-get install kamailio kamailio-geoip-modules kamailio-mysql-modules kamailio-tls-modules
# cd /etc/kamailio
# cp /usr/local/src/SIP-Firewall/config/kamailio.cfg .
# cp /usr/local/src/SIP-Firewall/config/kamailio_default /etc/default/kamailio
```

Edit kamailio.cfg and change some values:

* MY_IP_ADDRESS is the IP address for this server (firewall).
* PBX_IP_ADDRESS is the IP address for the PBX you will send traffic through this firewall.

```
#!define IPADDRESS "MY_IP_ADDRESS"
#!define PBX_IPADDRESS "PBX_IP_ADDRESS"

# Database access
#!define DBURL  "mysql://kamailio:STRONG_KAMAILIO_PASSWORD@localhost/kamailio"
# SQL OPS
modparam("sqlops","sqlcon","cb=>mysql://security:STRONG_WEB_PASSWORD@localhost/security")
```

Download GeoLiteCity database and restart Kamailio:
```
# wget http://geolite.maxmind.com/download/geoip/database/GeoLiteCity.dat.gz
# gzip -d GeoLiteCity.dat.gz

# /etc/init.d/kamailio restart
```

Install Apache and PHP:
```
# apt-get install apache2 php
# cd /var/www/html
# mkdir apirest
# cp /usr/local/src/SIP-Firewall/apirest/* apirest/
# cp -r /usr/local/src/SIP-Firewall/web/* .
```

Configure database access from the API Rest. Edit apirest/conecta.php file and change IP address and password:
```
$pbxip = "MY_IP_ADDRESS";

class DB {
        // Configuration information:
        private static $user = 'security';
        private static $pass = 'STRONG_WEB_PASSWORD';
        private static $config = array(
                'write' => array('localhost'),
                'read' => array('localhost')
        );
```

Configure the API Rest access from the control panel. Edit /var/www/html/assets/url.conf file and change IP address:
```
http://MI_IP_ADDRESS/apirest
```

Now you can access to the URL _**http://MY_IP_ADDRESS/**_ adn sign in with user _**admin**_ and password _**admin**_.


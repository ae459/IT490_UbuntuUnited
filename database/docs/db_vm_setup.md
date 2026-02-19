DB VM Setup Checklist

1) Install packages
 - sudo apt update
 - sudo apt install mysql-server php php-mysql

2) MySQL only listens locally (there is no direct access from web VM)
 - Edit /etc/mysql/mysql.conf.d/mysqld.cnf
 - bind address
 - sudo systemctl restart mysql

3) FIrewall blocks MYSQL port
 - sudo ufw enable
 - sudo ufw deny 3306
 - sudo ufw status

4) Create schema
 - sudo mysql < database/sql/schema.sql

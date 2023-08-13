<!DOCTYPE html>
<html>
<head>
    <title>SQL testing</title>
</head>
<body>
<pre>
MySQL testing server:
PhpMyAdmin: <a href="/mysql/phpmyadmin">/mysql/phpmyadmin</a>
Hostname: %MYSQL_HOSTNAME%
Port: %MYSQL_PORT%
Username: %MYSQL_USERNAME%
Password: %MYSQL_PASSWORD%
Database: %MYSQL_DATABASE%
PDO: new PDO('mysql:host=%MYSQL_HOSTNAME%;port=%MYSQL_PORT%;dbname=%MYSQL_DATABASE%', '%MYSQL_USERNAME%', '%MYSQL_PASSWORD%', array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_EMULATE_PREPARES => false,
));


Future ideas:
- Adminer (https://www.adminer.org/)
- PostgreSQL
- MariaDB
- Microsoft SQL Server
- SQLite
GitHub: <a href="https://github.com/loltek/sql.loltek.net">https://github.com/loltek/sql.loltek.net</a>
</pre>
</body>
</html>
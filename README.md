# JobSystem
 
A PHP and MySQL job portal. Employers can post and manage job listings, while jobseekers can browse openings and submit applications.
 
---
 
## Requirements
 
- [XAMPP](https://www.apachefriends.org/) with Apache and MySQL
- PHP 7.4 or higher
- A modern web browser
---
 
## Installation
 
### 1. Clone or download the project
 
Place the project folder inside your XAMPP `htdocs` directory:
 
```
xampp/htdocs/JobSystem/
```
 
### 2. Import the database
 
1. Start **Apache** and **MySQL** in the XAMPP Control Panel.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin`.
3. Create a new database (e.g., `jobsystem`).
4. Select that database, go to the **Import** tab, and upload `jobsystem.sql`.
### 3. Configure the database connection
 
Open `config/database.php` and update the credentials to match your setup:
 
```php
$host     = 'localhost';
$database = 'jobsystem';   // the database name you created
$username = 'root';        // default XAMPP username
$password = '';            // default XAMPP password (blank)
```
 
### 4. Run the project
 
Open your browser and go to:
 
```
http://localhost/JobSystem
```

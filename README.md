# JobSystem

JobSystem is a small PHP and MySQL job portal made for a local XAMPP setup. The project is still under development, but the goal is to allow employers to post jobs and jobseekers to browse and apply for them.

## Features

- User registration and login
- Employer dashboard for posting and managing jobs
- Jobseeker dashboard for viewing and applying to jobs
- Resume/file upload support
- Basic job search and listing pages

## Requirements

- XAMPP or any PHP server
- PHP
- MySQL or MariaDB
- Web browser

## How to Run

1. Put the project folder inside `xampp/htdocs`.
2. Start Apache and MySQL in XAMPP.
3. Set up the database connection in `config/database.php`.
4. Open the project in the browser:

```text
http://localhost/JobSystem
```

## Project Folders

- `auth/` - login, register, and logout pages
- `employer/` - employer pages
- `jobseeker/` - jobseeker pages
- `functions/` - PHP helper functions
- `includes/` - shared page files
- `assets/` - CSS and JavaScript files
- `uploads/` - uploaded files

## AI Assistance Note

This project is still under development and was made with AI assistance for help with code structure, debugging, and documentation. The code is being reviewed and adjusted as the project continues.
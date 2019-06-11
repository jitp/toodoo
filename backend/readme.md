# Welcome to TOODOO!

Please follow the instrutions below for local development deployment.

#### Server Requirements

Make sure you have the following:

- PHP >= 7.1.3
- OpenSSL PHP Extension
- PDO PHP Extension
- Mbstring PHP Extension
- Tokenizer PHP Extension
- XML PHP Extension
- Ctype PHP Extension
- JSON PHP Extension

#### Local development deployment

1. Give write permissions to web server to the following folders:
   * bootstrap/cache
   * storage
   
2. Run: `composer install`
3. Configure your web server's document / web root to be the public directory. 
   The index.php in this directory serves as the front controller for all HTTP requests entering your application.
4. Renamed the .env.example file to .env
5. Configure your enviroment in .env file.
6. Generate application key by running: `php artisan key:generate`
7. Generate jwt secret key: `php artisan jwt:secret`
8. Create a database and place configuration values requested in .env file.
9. Run migrations: `php artisan migrate`

#### Enviroment configuration

The .env file has various defaults but we are just interested in:

```
DB_CONNECTION=
DB_HOST=
DB_PORT=
DB_DATABASE=
DB_USERNAME=
DB_PASSWORD=

```
Fill those out with your current database configuration.

```
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
```
Fill those out with your current email solution.

Place frontend url in:

```
FRONTEND_URL=http://localhost:4200
```

#### Web server configuration

##### Apache

Laravel includes a public/.htaccess file that is used to provide URLs without the index.php front controller in the path. Before serving Laravel with Apache, be sure to enable the mod_rewrite module so the .htaccess file will be honored by the server.

If the .htaccess file that ships with Laravel does not work with your Apache installation, try this alternative:

```
Options +FollowSymLinks -Indexes
RewriteEngine On

RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]
```

##### Nginx
If you are using Nginx, the following directive in your site configuration will direct all requests to the index.php front controller:

```
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```
# AaxisTest

Basic Technical Test PHP Symfony Developer

## Setup

### Pre-requisites:

- Git
- Docker
- Xampp|MAMP, etc (php 8.2)
- composer
- Postman|Apidog, etc
- Postgresql (15.5)

#### Docker

Install Docker for your platform.

- Mac: https://store.docker.com/editions/community/docker-ce-desktop-mac
- Windows: https://store.docker.com/editions/community/docker-ce-desktop-windows
- Linux: Please see your distributions package management system

#### composer

Install composer for your platform.

- Mac: Included with Docker
- Windows: Included with Docker
- Linux: Please see your distributions package management system

#### Possible usage of command:

For run phpstan: `vendor/bin/phpstan analyse src tests`
For run unit test: `vendor/bin/phpunit`

### Installation

First we need to clone the project.

```bash
cd ~/code # or whatever directory you keep your projects in
git clone https://github.com/oaguilante/aaxis_test.git
cd aaxis_test
```

Now that we have the application configured, we need to install our dependencies. Before doing that though we need the docker images we use.

```bash
docker-compose pull
```

Now we can install our dependencies.

```bash
composer install
```

And finally it's time to start up our containers:

```bash
docker-compose up -d
```

Now we need to create the user in PostgreSQL with SUPER USER privileges an LOGIN.

user: 'aaxis'
password: 'aaxis2018'

In the php.ini file add this file in 'Module Settings' sections

extension=php_pgsql.dll
extension=php_pdo_pgsql.dll

And restart the Php services

Now we need to create the database.

```bash
php bin/console doctrine:database:create    
```

And now create the 'product' table.

```bash
php bin/console doctrine:migrations:migrate    
```

# How to execute
You can make AaxisTest work via console web browser or postman.

# Execute from web browser
To run from the web browser, an example URL would be: 

Use de next credentials:
Username: AdminAaxis
Password: AdminAaxxis2018

'http://localhost/aaxis_test/public/products/load'
'http://localhost/aaxis_test/public/products/update'
'http://localhost/aaxis_test/public/products/list'

And load de files examples products.json or products - update.json

# Execute from web postman
To run from Postman or another tool, an example URL would be:

'http://localhost/aaxis_test/public/api/products/load'
'http://localhost/aaxis_test/public/api/products/update'
'http://localhost/aaxis_test/public/api/products/list'

Using 'Basic Auth'
Username: AdminAaxis
Password: AdminAaxxis2018

And for the Apis '/api/products/load' and '/api/products/update'' use a payload like this:

{
    "products": [
        {
            "sku": "SKU12345",
            "product_name": "Laptop Dell XPS 13",
            "description": "Laptop ultradelgada con pantalla táctil de alta resolución y procesador de última generación."
        },
        {
            "sku": "SKU67890",
            "product_name": "Smartphone Samsung Galaxy S30",
            "description": "Teléfono inteligente con cámara avanzada, batería de larga duración y diseño elegante."
        }
    ]
}


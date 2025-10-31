# Symfony Home Budget App
Symfony-based Expense Tracker API with MySQL.

> This README provides a complete setup for the Symfony Home Budget App. All commands are separated by comments so you can follow the workflow step by step safely.

## 🚀 Get Started

```bash
# Build and start Docker containers
docker-compose up -d --build

# Enter the PHP container
docker exec -it symfony_php bash

# Install PHP dependencies
composer install

# Reset the database
php bin/console doctrine:database:drop --force
php bin/console doctrine:database:create

# Run database migrations
php bin/console doctrine:migrations:migrate

# Load fixtures
php bin/console doctrine:fixtures:load --no-interaction

# Generate JWT keys
mkdir -p config/jwt
openssl genrsa -out config/jwt/private.pem 4096
openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem

# Access the API
# Open your browser at http://localhost:8080/api/doc

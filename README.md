# Symfony Home Budget App
Symfony-based Expense Tracker API sa MySQL.

## 🚀 Get Started
Run the following commands in your terminal:

```bash
docker-compose up -d --build
docker exec -it symfony_php bash -c "\
    composer install && \
    php bin/console doctrine:database:drop --force && \
    php bin/console doctrine:database:create && \
    php bin/console doctrine:migrations:migrate && \
    php bin/console doctrine:fixtures:load --no-interaction && \
    mkdir -p config/jwt && \
    openssl genrsa -out config/jwt/private.pem 4096 && \
    openssl rsa -pubout -in config/jwt/private.pem -out config/jwt/public.pem \
"

Once the app is running, open your browser at:
👉 http://localhost:8080/api/doc

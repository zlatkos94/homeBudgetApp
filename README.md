# Symfony Home Budget App
Symfony-based Expense Tracker API sa MySQL.
## Get started
```bash
git clone git@github.com:zlatkos94/homeBudgetApp.git
cd homeBudgetApp
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

FROM shinsenter/symfony:php8.1

COPY src/ /var/www/html

ADD --chown=$APP_USER:$APP_GROUP ./ /var/www/html/

EXPOSE 80
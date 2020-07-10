FROM php:7.1-apache

# Update packages
RUN apt-get update && apt-get upgrade -y && apt-get autoremove -y
RUN apt install -y git
# Install mysqli extension for php
RUN docker-php-ext-install mysqli

# Add certificates and keys
COPY ca/			/var/www/ca/
RUN rm /etc/ssl/openssl.cnf && ln -s /var/www/ca/client/openssl.cnf	/etc/ssl/openssl.cnf

# Add public key for `emergency` scenarios
COPY emergency.pub.pem		/var/www/

# Add web-app files
COPY html/	/var/www/html/
COPY config/	/var/www/config/
COPY php.ini	/usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
## Enable configs and sites
COPY composer.json /var/www/html/
RUN /usr/local/bin/composer install
# Adjust permissions
RUN chown -R www-data:www-data /var/www/ca/ && \
    chown -R www-data:www-data /var/www/html

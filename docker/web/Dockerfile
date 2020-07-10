FROM php:7.1-apache

ENV CLIENT_CA_CHAIN	"ca/ca-chain.cert.pem"

# Update packages
RUN apt-get update && apt-get upgrade -y && apt-get autoremove -y
RUN apt install -y git

# Install mysqli extension for php
RUN docker-php-ext-install mysqli

# Install php csrf protector


# Add Apache config files
COPY apache2/dhparam.pem			/etc/ssl/certs/dhparam.pem
COPY apache2/conf-available/ssl-params.conf	/etc/apache2/conf-available/ssl-params.conf
COPY apache2/sites-available/default-ssl.conf	/etc/apache2/sites-available/default-ssl.conf
COPY apache2/sites-available/000-default.conf	/etc/apache2/sites-available/000-default.conf

# Add CA chains
COPY ${CLIENT_CA_CHAIN}	/etc/apache2/ssl.crt/ca-bundle.crt

# Add TLS certificates and keys
COPY ca/server/certs/asl-server.cert.pem	/etc/ssl/certs/asl-server.cert.pem
COPY ca/server/private/asl-server.key.pem	/etc/ssl/private/asl-server.key.pem
COPY ca/server/certs/ca-chain.cert.pem		/etc/apache2/ssl.crt/ca-chain.cert.pem

# Add web-app files
COPY html/	/var/www/html/
COPY config	/var/www/config/
COPY php.ini	/usr/local/etc/php/

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
COPY composer.json /var/www/html/
RUN /usr/local/bin/composer install

# Enable configs and sites
RUN 	a2enmod ssl && \
	a2enmod rewrite && \
	a2enmod headers && \
	a2enconf ssl-params && \
	a2ensite default-ssl && \
	a2ensite 000-default && \
	apache2ctl configtest

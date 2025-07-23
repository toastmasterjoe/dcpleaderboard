FROM wordpress:6.8.2-php8.4-apache

RUN apt-get update
RUN apt-get install sudo -y
RUN usermod -aG sudo www-data

COPY my-php.ini /usr/local/etc/php/conf.d/memory.ini

COPY ./.prod.env ./
RUN . ./.prod.env

COPY install_wp.sh /usr/bin/

RUN curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar

# Make it executable
RUN chmod a+x wp-cli.phar

# Move to a directory in PATH
RUN mv wp-cli.phar /usr/local/bin/wp

USER root
RUN chmod u+x /usr/bin/install_wp.sh
RUN wp core download --path=/var/www/html/ --allow-root 
COPY ./plugins/wp-crontrol /var/www/html/wp-content/plugins/wp-crontrol

USER www-data

WORKDIR /var/www/html/
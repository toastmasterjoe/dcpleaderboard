FROM wordpress:6.8.2-php8.4-apache

RUN apt-get update
RUN apt-get install sudo -y
#done only due to iptables which should not be required on an actual production server
RUN usermod -aG sudo www-data 
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" | tee /etc/sudoers.d/www-data-nopasswd
RUN chmod 0440 /etc/sudoers.d/www-data-nopasswd

COPY my-php.ini /usr/local/etc/php/conf.d/memory.ini

COPY ./.staging.env ./
RUN . ./.staging.env

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
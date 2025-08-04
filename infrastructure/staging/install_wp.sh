#!/bin/sh

wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST:$WORDPRESS_DB_PORT" --path=/var/www/html/ --allow-root --force
wp core install --url="$WORDPRESS_URL" --title="$WORDPRESS_TITLE" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --path=/var/www/html/ --allow-root

sudo iptables -t nat -A PREROUTING -p tcp --dport 8181 -j REDIRECT --to-port 80
sudo iptables -t nat -A OUTPUT -p tcp -o lo --dport 8181 -j REDIRECT --to-port 80
sudo bash -c "iptables-save > /etc/iptables/rules.v4"

/usr/local/bin/docker-entrypoint.sh apache2-foreground
exec "$@"
      
#!/bin/sh

wp config create --dbname="$WORDPRESS_DB_NAME" --dbuser="$WORDPRESS_DB_USER" --dbpass="$WORDPRESS_DB_PASSWORD" --dbhost="$WORDPRESS_DB_HOST:$WORDPRESS_DB_PORT" --path=/var/www/html/ --allow-root --force
wp core install --url="$WORDPRESS_URL" --title="$WORDPRESS_TITLE" --admin_user="$WORDPRESS_ADMIN_USER" --admin_password="$WORDPRESS_ADMIN_PASSWORD" --admin_email="$WORDPRESS_ADMIN_EMAIL" --path=/var/www/html/ --allow-root

sudo sysctl -w net.ipv4.ip_forward=1
sudo sysctl -p 
sudo iptables -t nat -A OUTPUT -p tcp -o lo --dport 8080 -j REDIRECT --to-port 80

/usr/local/bin/docker-entrypoint.sh apache2-foreground
exec "$@"
      
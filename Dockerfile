FROM library/ubuntu:17.04
RUN apt update
RUN apt -y upgrade
RUN DEBIAN_FRONTEND=noninteractive && apt install -y php apache2 libapache2-mod-php php-sqlite3  php-curl php-xdebug
ADD 000-default.conf /etc/apache2/sites-enabled/000-default.conf
RUN a2enmod rewrite
RUN a2enmod headers 
COPY docker-entrypoint.sh /usr/local/bin/

ENTRYPOINT ["docker-entrypoint.sh"]


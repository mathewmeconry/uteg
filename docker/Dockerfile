FROM debian:jessie

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y
<<<<<<< HEAD
RUN apt-get install -y nginx php5-fpm php5-mysqlnd php5-cli mysql-server supervisor

RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php5/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php5/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php5/fpm/pool.d/www.conf
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf

ADD vhost.conf /etc/nginx/sites-available/default
ADD supervisor.conf /etc/supervisor/conf.d/supervisor.conf
ADD init.sh /init.sh

RUN chmod u+x /init.sh

EXPOSE 80
=======
RUN apt-get install -y nginx php5-fpm php5-mysqlnd php5-cli mysql-server supervisor php5-intl php5-dev php5-gd php5-curl

RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php5/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php5/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php5/fpm/pool.d/www.conf
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf
RUN echo "extension=gd.so" >> /etc/php5/fpm/php.ini

ADD libjpeg8.deb /tmp/libjpeg8.deb
ADD wkhtmltox-0.12.2-patched.deb /tmp/wkhtmltox-0.12.2-patched.deb
RUN dpkg -i libjpeg8.deb
RUN dpkg -i wkhtmltox-0.12.2-patched.deb

ADD vhost.conf /etc/nginx/sites-available/default
ADD supervisor.conf /etc/supervisor/conf.d/supervisor.conf
ADD my.cnf /etc/mysql/my.cnf
ADD init.sh /init.sh
ADD mysqlcommands /mysqlcommands

RUN chmod u+x /init.sh

EXPOSE 80:80
EXPOSE 3306:3306
>>>>>>> refs/heads/EGT

VOLUME ["/srv"]
WORKDIR /srv

CMD ["/usr/bin/supervisord"]
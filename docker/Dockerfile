FROM debian:jessie

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y
RUN apt-get install -y nginx php5-fpm php5-mysqlnd php5-cli mysql-server supervisor php5-intl php5-dev wkhtmltopdf xvfb

RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php5/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php5/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php5/fpm/pool.d/www.conf
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf
RUN echo 'exec xvfb-run -a -s "-screen 0 640x480x16" wkhtmltopdf "$@"' | tee /usr/local/bin/wkhtmltopdf >/dev/null
RUN chmod a+x /usr/local/bin/wkhtmltopdf

ADD vhost.conf /etc/nginx/sites-available/default
ADD supervisor.conf /etc/supervisor/conf.d/supervisor.conf
ADD my.cnf /etc/mysql/my.cnf
ADD init.sh /init.sh
ADD mysqlcommands /mysqlcommands

RUN chmod u+x /init.sh

EXPOSE 80:80
EXPOSE 3306:3306

VOLUME ["/srv"]
WORKDIR /srv

CMD ["/usr/bin/supervisord"]
FROM debian:jessie

ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update -y
RUN apt-get install -y nginx php5-fpm php5-mysqlnd php5-cli mysql-server supervisor php5-intl php5-dev wkhtmltopdf xvfb

RUN sed -e 's/;daemonize = yes/daemonize = no/' -i /etc/php5/fpm/php-fpm.conf
RUN sed -e 's/;listen\.owner/listen.owner/' -i /etc/php5/fpm/pool.d/www.conf
RUN sed -e 's/;listen\.group/listen.group/' -i /etc/php5/fpm/pool.d/www.conf
RUN echo "\ndaemon off;" >> /etc/nginx/nginx.conf
RUN echo 'xvfb-run -a -s "-screen 0 640x480x16" /usr/bin/wkhtmltopdf -q "$@"' | tee /usr/local/bin/wkhtmltopdf >/dev/null
RUN chmod a+x /usr/local/bin/wkhtmltopdf
RUN curl -O http://ftp.us.debian.org/debian/pool/main/libj/libjpeg8/libjpeg8_8d1-2_amd64.deb
RUN dpkg -i libjpeg8_8d1-2_amd64.deb
RUN curl -O http://www.ajvg.com/downloads/wkhtmltox-0.12.2-7058a04_linux-wheezy-amd64.deb
RUN dpkg -i wkhtmltox-0.12.2-7058a04_linux-wheezy-amd64.deb
RUN rm libjpeg8_8d1-2_amd64.deb
RUN rm wkhtmltox-0.12.2-7058a04_linux-wheezy-amd64.deb

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
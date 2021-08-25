FROM ubuntu:18.04

ENV LC_ALL C.UTF-8
ENV LANG en_US.UTF-8
ENV LANGUAGE en_US.UTF-8
ENV DEBIAN_FRONTEND noninteractive

RUN apt-get update
RUN apt-get upgrade -y
RUN apt-get install -y apache2 apache2-utils libapache2-mod-php php-xml php-mysql php-gd

RUN a2enmod php7.2 headers rewrite
RUN a2dissite 000-default
RUN echo "Listen 8080" | tee /etc/apache2/ports.conf

ENV APACHE_RUN_USER www-data
ENV APACHE_RUN_GROUP www-data
ENV APACHE_LOG_DIR /var/log/apache2
ENV APACHE_PID_FILE /var/run/apache2.pid
ENV APACHE_LOCK_DIR /var/lock/apache2
ENV APACHE_RUN_DIR /var/run/apache2

ADD sig.conf /etc/apache2/sites-available/sig.conf
RUN a2ensite sig

ADD sig.php index.php admin.php /var/www/html/
COPY controllers /usr/share/php/controllers/
COPY include /usr/share/php/include/
COPY models /usr/share/php/models/
#COPY templates /usr/share/php/templates/
COPY views /usr/share/php/views/

RUN mkdir -p /var/www/html/images /var/www/html/tmp /var/www/html/images/fractals
RUN chown -R www-data /var/www/html/images /var/www/html/tmp /var/www/html/images/fractals

RUN apache2 -t

CMD ["apache2", "-D", "FOREGROUND"]

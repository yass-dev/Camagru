FROM php:8.0.2-apache

RUN apt-get update && apt-get upgrade -y

ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/
RUN chmod +x /usr/local/bin/install-php-extensions
RUN install-php-extensions pdo pdo_mysql gd
RUN a2enmod rewrite

RUN apt-get update && apt-get install -q -y mailutils sendmail ca-certificates curl git
# RUN service sendmail start

RUN curl -Lsf 'https://storage.googleapis.com/golang/go1.8.3.linux-amd64.tar.gz' | tar -C '/usr/local' -xvzf -
ENV PATH /usr/local/go/bin:$PATH
RUN go get github.com/mailhog/mhsendmail
RUN cp /root/go/bin/mhsendmail /usr/bin/mhsendmail
RUN cp /usr/local/etc/php/php.ini-development /usr/local/etc/php/php.ini
RUN echo 'sendmail_path = /usr/bin/mhsendmail --smtp-addr mailhog:1025' > /usr/local/etc/php/php.ini
RUN echo 'upload_max_filesize = 40M' >> /usr/local/etc/php/php.ini
RUN echo 'post_max_size = 41M' >> /usr/local/etc/php/php.ini

RUN echo "ServerName localhost" >> /etc/apache2/apache2.conf

COPY ./ /var/www/html/
RUN chmod 777 /var/www/html/static/**

EXPOSE 80
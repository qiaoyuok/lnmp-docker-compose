FROM php:7.4-fpm

MAINTAINER  孙大圣 1589946526@qq.com

#时区
ENV TZ Asia/Shanghai
RUN date -R

WORKDIR /working

RUN mkdir -p /usr/share/nginx/logs

RUN apt-get update --fix-missing && apt-get install -y libzip-dev libpng-dev libjpeg-dev libfreetype6-dev zip unzip \
    && docker-php-ext-configure gd --with-freetype=/usr/include/ --with-jpeg=/usr/include/ \
    && docker-php-ext-install gd mysqli opcache pdo_mysql gd zip

ENV PHPREDIS_VERSION 4.0.1
ENV PHPXDEBUG_VERSION 3.1.4
ENV PHPSWOOLE_VERSION 4.8.11
ENV PHPMONGODB_VERSION 1.5.3
RUN pecl install redis-$PHPREDIS_VERSION \
    && pecl install xdebug-$PHPXDEBUG_VERSION \
    && pecl install swoole-$PHPSWOOLE_VERSION \
    && pecl install mongodb-$PHPMONGODB_VERSION \
    && docker-php-ext-enable redis xdebug swoole mongodb mysqli

# install composer new
# https://getcomposer.org/installer | https://install.phpcomposer.com/installer
 RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
        && php composer-setup.php \
        && php -r "unlink('composer-setup.php');" \
        && mv composer.phar /usr/local/bin/composer \
        && composer config -g repo.packagist composer https://mirrors.aliyun.com/composer/ \
        && apt-get install -y git \
        && rm -rf /var/cache/apt/* \
        && rm -rf /var/lib/apt/lists/* \
        && mkdir /var/lib/sessions \
        && chmod o=rwx -R /var/lib/sessions \
        && chmod o=rwx -R /usr/share/nginx/logs

#容器启动时执行指令
CMD ["php-fpm"]
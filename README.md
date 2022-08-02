### 一、LNMP服务版本&整体结构
>php-fpm:7.4
> 
>nginx:1.21.5
> 
>redis:5.0.5
> 
>mysql:5.7.39
> 

<font color=red>项目根路径替换为自己的：/Users/sunqiaoyu/GolandProjects/go/src/lnmp</font>
>sed -i "s@/Users/sunqiaoyu/GolandProjects/go/src/lnmp@/root/lnmp/lnmp-docker-compose@g" docker-compose.yml

````azure
├── config  #配置文件目录
│   ├── mysql  #MySQL配置文件
│   │   └── my.cnf
│   ├── nginx #Nginx配置文件
│   │   ├── conf.d #其他Server配置项目录
│   │   │   └── demo.conf
│   │   └── nginx.conf #主配置
│   ├── php-fpm #php-fpm配置文件
│   │   ├── php #PHP配置
│   │   │   ├── conf.d #扩展文件配置
│   │   │   │   ├── docker-php-ext-gd.ini
│   │   │   │   ├── docker-php-ext-mongodb.ini
│   │   │   └── php.ini #php.ini配置项
│   │   └── php-fpm.d #php-fpm配置
│   │       ├── docker.conf
│   │       ├── www.conf
│   │       ├── www.conf.default
│   │       └── zz-docker.conf
│   └── redis   #redis配置
│       └── redis.conf
├── data    #存放持久化数据的目录（MySQL、redis等）
├── docker-compose.yml #构建服务的配置
├── dockerfile #各个服务的dockerfile文件
│   ├── mysql
│   │   └── Dockerfile
│   ├── nginx
│   │   └── Dockerfile
│   ├── php-fpm
│   │   └── Dockerfile
│   └── redis
│       └── Dockerfile
├── logs #所有服务的日志存放目录
└── www #站点根目录
    ├── index.html
    └── info.php
````

### 二、Nginx服务
````azure
FROM nginx:1.21.5

MAINTAINER  孙大圣 1589946526@qq.com

CMD ["nginx","-g","daemon off;"]
````

### 三、php-fpm服务
````azure
FROM php:7.4-fpm

MAINTAINER  孙大圣 1589946526@qq.com

#时区
ENV TZ Asia/Shanghai
RUN date -R

WORKDIR /working

RUN sed -i "s@http://deb.debian.org@http://mirrors.aliyun.com@g" /etc/apt/sources.list \
    && sed -i "s@http://security.debian.org@http://mirrors.aliyun.com@g" /etc/apt/sources.list \
    && cat /etc/apt/sources.list \
    && rm -Rf /var/lib/apt/lists/* \
    && mkdir -p /usr/share/nginx/logs

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
````

### 四、Redis服务
````azure
FROM redis:5.0.5

MAINTAINER  孙大圣 1589946526@qq.com

CMD ["redis-server" , "/etc/redis/redis.conf"]

````
### 五、MySQL服务
````azure
FROM mysql:5.7.39 
#FROM mysql  #m1 的arm架构 8.0才开始支持

MAINTAINER 孙大圣 1589946526@qq.com

CMD ["mysqld"]

````

### 六、docker-compose.yml
````azure
version: "3"

services:
nginx:
build: "./dockerfile/nginx"
container_name: ng121
ports:
    - "1080:80"
      - "1081-1181:1081-1181"
volumes:
    - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/nginx/conf.d:/etc/nginx/conf.d
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/nginx/nginx.conf:/etc/nginx/nginx.conf
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/www:/usr/share/nginx/html
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/logs/nginx:/var/log/nginx/
networks:
    - lnmp
depends_on:
    - php
php:
build: "./dockerfile/php-fpm"
container_name: php74
volumes:
    - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/www:/usr/share/nginx/html
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/logs/php:/usr/share/nginx/logs
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/php-fpm/php:/usr/local/etc/php
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/php-fpm/php-fpm.d:/usr/local/etc/php-fpm.d
networks:
    - lnmp
depends_on:
    - redis
      - mysql
redis:
build: "./dockerfile/redis"
container_name: redis505
ports:
    - "16379:6379"
volumes:
    - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/redis/redis.conf:/etc/redis/redis.conf
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/data/redis:/data/data
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/logs/redis:/data/logs
networks:
    - lnmp
mysql:
build: "./dockerfile/mysql"
container_name: mysql
environment:
MYSQL_ROOT_PASSWORD: root
MYSQL_ALLOW_EMPTY_PASSWORD: root
ports:
    - "13306:3306"
volumes:
    - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/config/mysql/my.cnf:/etc/my.cnf
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/data/mysql:/var/lib/mysql/data
      - /Users/sunqiaoyu/GolandProjects/go/src/lnmp/logs/mysql:/var/lib/mysql/log
networks:
    - lnmp
networks:
lnmp:

# sed -i "s@/Users/sunqiaoyu/GolandProjects/go/src/lnmp@/root/lnmp/lnmp-docker-compose@g" docker-compose.yml
````



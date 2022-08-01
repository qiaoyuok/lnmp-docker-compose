FROM mysql:5.7.39
#FROM mysql

MAINTAINER 孙大圣 1589946526@qq.com

RUN mkdir -p /var/lib/mysql && mkdir -p /var/log/mysql \
    && chmod 777 /var/lib/mysql && chmod 777 /var/lib/mysql

#时区
ENV TZ Asia/Shanghai
RUN date -R

CMD ["mysqld"]
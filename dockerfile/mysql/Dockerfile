FROM mysql:5.7.39
#FROM mysql

MAINTAINER 孙大圣 1589946526@qq.com

#时区
ENV TZ Asia/Shanghai
RUN date -R

RUN mkdir /var/lib/mysql/log && chmod 777 /var/lib/mysql/log

CMD ["mysqld"]
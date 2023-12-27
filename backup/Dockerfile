ARG PHP="8.1"
FROM synstd/php-service:${PHP}
ARG PHP="8.1"


MAINTAINER Synapse <mail@synapse-studio.ru>

#COPY script & config:::
COPY config/opcache.ini /etc/php/${PHP}/mods-available/opcache.ini
COPY console /opt/console
RUN mkdir -p /opt/opcache && \
  chown www-data:www-data -R /opt/opcache && \
  chown www-data:www-data -R /opt/console && \
  sudo -u www-data composer install -d /opt/console && \
  sudo -u www-data ls /opt/console/vendor && \
  sudo -u www-data chmod 755 /opt/console/console.php && \
  rm -rf ~/.composer

RUN /opt/console/console.php echo

ENTRYPOINT ["/usr/bin/php", "/opt/console/console.php"]

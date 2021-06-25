FROM php:7.4-cli

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN apt-get update \
    && apt-get install -y git nano zip unzip zlib1g-dev libzip-dev \
    && apt-get -y autoremove \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && groupadd --gid 1000 app \
    && useradd --uid 1000 --gid app --shell /bin/bash --create-home app \
    && touch /var/log/xdebug.log && chgrp 1000 /var/log/xdebug.log && chmod g+w /var/log/xdebug.log \
    && sed -i -e '/alias l\|export\|eval/s/#//' /root/.bashrc \
    && mkdir --parents /var/www/src /var/www/tests \
    && chown 1000:1000 /var/www/src /var/www/tests \
    && sed -i -e '/xdebug.remote_autostart/s/1/0/' /usr/local/etc/php/conf.d/30-xdebug.ini \
    && true

USER app

ENV PATH "$PATH:/home/app/.composer/vendor/bin"

RUN composer global require friendsofphp/php-cs-fixer \
    && sed -i -e '/alias l\|export\|eval/s/#//' /home/app/.bashrc

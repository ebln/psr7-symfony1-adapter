# PHP 7.4 is EOL upstream: 7.4.33 was the last release. The official php:7.4-cli image is that
# release on Debian bullseye, and it no longer builds: apt refuses bullseye-security, whose Release
# file expired on 2026-09-07.
#
# This image takes PHP 7.4 from packages.sury.org on Debian bookworm. sury's packages are the Debian
# PHP packaging, with security fixes after the upstream EOL. GitHub CI (nanasess/setup-php) installs
# PHP 7.4 from the same maintainer's Ubuntu PPA.
FROM debian:bookworm-slim

ENV DEBIAN_FRONTEND=noninteractive

# Bookworm's own packages are installed before sury's list is added, so that sury only supplies
# the php7.4-* packages.
RUN set -ex \
    && apt-get update --quiet=2 \
    && apt-get upgrade --assume-yes --quiet=2 \
    && apt-get install --assume-yes --no-install-recommends --quiet=2 \
        ca-certificates \
        curl \
        git \
        gnupg \
        nano \
        unzip \
        zip \
    && curl --fail --silent --show-error --location \
        --output /usr/share/keyrings/sury-php.gpg https://packages.sury.org/php/apt.gpg \
    && echo 'deb [signed-by=/usr/share/keyrings/sury-php.gpg] https://packages.sury.org/php/ bookworm main' \
        > /etc/apt/sources.list.d/sury-php.list \
    && apt-get update --quiet=2 \
    && apt-get install --assume-yes --no-install-recommends --quiet=2 \
        php7.4-cli \
        php7.4-curl \
        php7.4-mbstring \
        php7.4-xdebug \
        php7.4-xml \
        php7.4-zip \
    # 1:7.4.33-31 is the first sury revision with the fix for CVE-2026-109618 and CVE-2026-109619.
    # The packages are not pinned, so the build stops when an older revision arrives.
    && version=$(dpkg-query --show --showformat='${Version}' php7.4-cli) \
    && { dpkg --compare-versions "$version" ge 1:7.4.33-31 \
        || { echo "php7.4-cli $version predates the CVE-2026-109618/-109619 fix" >&2; exit 1; }; } \
    && apt-get autoremove --assume-yes \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/* \
    && groupadd --gid 1000 app \
    && useradd --uid 1000 --gid app --shell /bin/bash --create-home app \
    && touch /var/log/xdebug.log && chgrp 1000 /var/log/xdebug.log && chmod g+w /var/log/xdebug.log \
    && sed -i -e '/alias l\|export\|eval/s/#//' /root/.bashrc \
    && mkdir --parents /var/www/src /var/www/tests /var/www/coverage

COPY --from=composer:2.10.3 /usr/bin/composer /usr/bin/composer

COPY 30-xdebug-config.ini /etc/php/7.4/cli/conf.d/

ENV COMPOSER_HOME=/root/.composer
ENV PATH="$PATH:/root/.composer/vendor/bin"

# The same php-cs-fixer version as .github/workflows/buildTest.yml; newer versions report other files.
RUN composer global require friendsofphp/php-cs-fixer:3.58

WORKDIR /var/www/

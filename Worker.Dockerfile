ARG COMPOSER_VERSION=latest

FROM composer:${COMPOSER_VERSION} AS vendor
FROM php:8.3.4-cli

ARG UID=1000
ARG GID=1000
ARG TZ=UTC
ARG USER=worker

ENV TERM=xterm-color \
    APP_ENV=production \
    APP_DEBUG=false
  
WORKDIR /var/www/html

RUN ln -snf /usr/share/zoneinfo/${TZ} /etc/localtime \
    && echo ${TZ} > /etc/timezone

ADD --chmod=0755 https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apt-get update; \
    apt-get install -yqq --no-install-recommends --show-progress \
    pipx \
    # Install PHP extensions
    && install-php-extensions \
    pcntl \
    opcache \
    pdo_mysql \
    intl \
    zip \
    redis \
    && apt-get -y autoremove \
    && apt-get clean \
    && docker-php-source delete \
    && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* \
    && rm /var/log/lastlog /var/log/faillog


RUN groupadd --force -g ${GID} ${USER} \
    && useradd -ms /bin/bash --no-log-init --no-user-group -g ${GID} -u ${UID} ${USER}


USER ${USER}

COPY --chown=${USER}:${USER} --from=vendor /usr/bin/composer /usr/bin/composer

COPY --chown=${USER}:${USER} . .

RUN composer install \
    --classmap-authoritative \
    --no-interaction \
    --no-ansi \
    --no-dev \
    && composer clear-cache

RUN pipx install rembg[gpu,cli] \
    && pipx ensurepath

RUN /home/${USER}/.local/bin/rembg d

RUN rm -rf deployment/


ENTRYPOINT ["php", "artisan", "queue:work"]
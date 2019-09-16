FROM php:7.2-fpm
    
    ENV TIMEZONE Asia/Shanghai

    RUN sed -i 's/deb.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && sed -i 's/security.debian.org/mirrors.aliyun.com/g' /etc/apt/sources.list \
    && cp /usr/share/zoneinfo/${TIMEZONE} /etc/localtime \
    && echo "{TIMEZONE}" > /etc/timezone

    Run echo "nameserver 223.5.5.5" >> /etc/resolv.conf \
    && echo "nameserver 223.6.6.6" >> /etc/resolve.conf \
    && apt-get update \
    && apt-get install -y \
        curl \
        vim \
        zip \
        unzip \
        locales \
        python \
        libfreetype6-dev \
        libjpeg62-turbo-dev \
        libpng-dev \
        supervisor \
    && mkdir -p /etc/supervisord.d/log \
    && mkdir -p /etc/supervisord.d/conf \
    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install mysqli pdo_mysql \
    && pecl install swoole \
    && pecl install redis \
    && docker-php-ext-enable swoole redis

    RUN curl -sS https://install.phpcomposer.com/installer | php \
    && mv composer.phar /usr/local/bin/composer \
    && composer self-update --clean-backups \
    && composer config -g repo.packagist composer https://packagist.phpcomposer.com
    
    # copy from local current path to image
    COPY supervisord_local.conf /etc/supervisord.conf

    ENTRYPOINT ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisord.conf"]

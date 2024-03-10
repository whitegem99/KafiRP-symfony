FROM php:7.4-fpm

########################################################################################################################

# Install nano and php-gd dependencies
RUN apt-get update && apt-get install -y nano zip unzip git \
    libfreetype6-dev \
    libjpeg62-turbo-dev \
    libpng-dev \
    libxpm-dev \
    libvpx-dev

# Configure GD package for JPEG support
RUN docker-php-ext-configure gd
    		#--with-freetype-dir=/usr/lib/x86_64-linux-gnu/ \
    		#--with-jpeg-dir=/usr/lib/x86_64-linux-gnu/ \
    		#--with-xpm-dir=/usr/lib/x86_64-linux-gnu/
    		#--with-vpx-dir=/usr/lib/x86_64-linux-gnu/

# Download script to install PHP extensions and dependencies
ADD https://raw.githubusercontent.com/mlocati/docker-php-extension-installer/master/install-php-extensions /usr/local/bin/

RUN chmod uga+x /usr/local/bin/install-php-extensions && sync

# Install some extra PHP Extension
RUN install-php-extensions opcache \
    pdo \
    pdo \
    pdo_mysql \
    mbstring \
    bcmath \
    sockets \
    gd \
    mbstring \
    intl \
    zip

# APCu
RUN pecl install apcu && echo "extension=apcu.so\napc.enable_cli = 1" > /usr/local/etc/php/conf.d/ext-apcu.ini

# Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash && mv /root/.symfony/bin/symfony /usr/local/bin/symfony


########################################################################################################################

# Fix permissions
# https://github.com/docker-library/php/issues/222#issuecomment-211527819
# http://gbraad.nl/blog/non-root-user-inside-a-docker-container.html
# https://docs.docker.com/engine/reference/builder/#user
# https://vsupalov.com/docker-arg-env-variable-guide/
# /etc/passwd Example: www-data:x:33:33:www-data:/var/www:/usr/sbin/nologin
# /etc/group Example: www-data:x:33:
ARG HOST_UID
ARG HOST_GID
ENV HOST_UID $HOST_UID
ENV HOST_GID $HOST_GID


RUN echo "$HOST_UID"
RUN echo "$HOST_GID"

RUN sed -ri 's#^www-data:x:33:33:www-data:/var/www:#www-data:x:'"$HOST_UID"':'"$HOST_GID"':www-data:/home/www-data:#' /etc/passwd
RUN sed -ri 's#^www-data:x:33:#www-data:x:'"$HOST_GID"':#' /etc/group
RUN mkdir /home/www-data && chown -R www-data:www-data /home/www-data
USER www-data

########################################################################################################################

# PHP.ini
COPY php.ini /usr/local/etc/php/conf.d/php.ini

# Composer Cache
ENV COMPOSER_HOME /home/www-data/.cache/composer
RUN mkdir -p ${COMPOSER_HOME} && chmod 777 ${COMPOSER_HOME}
VOLUME ${COMPOSER_HOME}

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

########################################################################################################################

WORKDIR /var/www/project

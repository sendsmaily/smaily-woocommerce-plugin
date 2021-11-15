FROM wordpress:5.8-php7.3-apache

ENV WOOCOMMERCE_VERSION=4.9.2

# Install required packages.
RUN apt-get update && \
    DEBIAN_FRONTEND=noninteractive apt-get install -y --no-install-recommends \
    unzip && \
    apt-get clean && rm -rf /var/lib/apt/lists/*

# Install WooCommerce.
RUN curl -sL -o /tmp/woocommerce.zip https://github.com/woocommerce/woocommerce/releases/download/${WOOCOMMERCE_VERSION}/woocommerce.zip \
    && unzip -q /tmp/woocommerce.zip -d /usr/src/wordpress/wp-content/plugins \
    && rm /tmp/woocommerce.zip \
    && chown www-data:www-data -R /usr/src/wordpress/wp-content/plugins/woocommerce

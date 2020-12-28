# use the official wordpress Docker image
FROM wordpress:5.4.2-php7.3-apache

# install curl and jq
RUN apt-get update && apt-get install -y jq curl unzip wget zlib1g-dev libicu-dev g++

# install latest tested release of woocommerce from github
RUN mkdir /usr/src/wordpress/wp-content/plugins/woocommerce \
  && curl -sL $(curl -sL -H "Accept: application/vnd.github.v3.full+json" \
  https://api.github.com/repos/woocommerce/woocommerce/releases/tags/4.3.0 \
  | jq -r .tarball_url) \
  | tar zx -C /usr/src/wordpress/wp-content/plugins/woocommerce \--strip-components 1
# Install Product Feed PRO for WooCommerce
RUN mkdir /usr/src/wordpress/wp-content/plugins/woo-product-feed-pro \
  && wget https://downloads.wordpress.org/plugin/woo-product-feed-pro.9.3.0.zip \
  && unzip woo-product-feed-pro.9.3.0.zip \
  && mv woo-product-feed-pro/* /usr/src/wordpress/wp-content/plugins/woo-product-feed-pro

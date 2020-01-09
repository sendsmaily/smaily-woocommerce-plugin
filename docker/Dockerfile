# use the official wordpress Docker image
FROM wordpress

# install curl and jq
RUN apt update && apt install -y jq curl

# install latest tested release of woocommerce from github
RUN mkdir /usr/src/wordpress/wp-content/plugins/woocommerce \
  && curl -sL $(curl -sL -H "Accept: application/vnd.github.v3.full+json" \
  https://api.github.com/repos/woocommerce/woocommerce/releases/tags/3.8.0 \
  | jq -r .tarball_url) \
  | tar zx -C /usr/src/wordpress/wp-content/plugins/woocommerce \--strip-components 1
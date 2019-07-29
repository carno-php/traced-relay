FROM carno/php

ADD composer.json /app

RUN wget https://getcomposer.org/download/1.8.6/composer.phar -O /tmp/composer && \
    chmod +x /tmp/composer && \
    /tmp/composer config minimum-stability stable && \
    /tmp/composer update && \
    /tmp/composer clear-cache && \
    rm -f /tmp/composer

ADD . /app

ENTRYPOINT ["/app/bin/traced", "server:start"]

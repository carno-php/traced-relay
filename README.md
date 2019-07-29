# Traced:relay - local agent for spans transport

# Installation

```bash
composer require carno-php/traced-relay
```

# Docker

```bash
docker run --rm -it -p 1234:1234/udp \
    carno/traced-relays \
    --socket-bind=udp://0.0.0.:1234 \
    --tracing-addr=zipkin://endpoint:80/api/v2/spans
```

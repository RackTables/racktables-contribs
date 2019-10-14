# racktables-docker

[![](https://img.shields.io/docker/pulls/ptman/racktables.svg)](https://hub.docker.com/r/ptman/racktables/)
[![](https://img.shields.io/docker/automated/ptman/racktables.svg)](https://hub.docker.com/r/ptman/racktables/builds/)
[![](https://images.microbadger.com/badges/image/ptman/racktables.svg)](http://microbadger.com/images/ptman/racktables)

## Quickstart

Note that this isn't a production setup, but fairly close. Make sure to change
the configuration if you intend to use it in production.

    docker-compose up
    # Start by browsing to http://localhost:8000/?module=installer&step=5

## Configuration

Look at the env vars available in the `Dockerfile` and `entrypoint.sh`.

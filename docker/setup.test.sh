#!/bin/sh
docker build -f ./php/Dockerfile . -t mkpc-php-test
docker build -f ./mysql/Dockerfile.test . -t mkpc-db-test

#!/bin/sh
docker build -f ./php/Dockerfile.test . -t mkpc-php-test
docker build -f ./mysql/Dockerfile.test . -t mkpc-db-test

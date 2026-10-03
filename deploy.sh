#!/bin/sh
# Deploys on the production server: pulls the current branch, then applies the new migrations.
#   On the server:        ./deploy.sh
#   From your machine:    ssh ovh /var/www/malahieude.net/mkpc/deploy.sh
set -e
cd "$(dirname "$0")"
before=$(git rev-parse HEAD)
git pull --ff-only
git log --oneline "$before..HEAD"
php php/migrations/migrate.php
php php/migrations/migrate.php --check || echo 'Warning: the database differs from docker/php/scripts/setup.sql, a migration is probably missing.'

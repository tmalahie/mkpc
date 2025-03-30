This repository contains the source code of MKPC website: https://mkpc.malahieude.net.

This project has historically only been maintained by one developer (@tmalahie, known on the site as Wargor), however if you want to contribute you're welcome :)

# Installation
## Using Docker
The easiest way to install MKPC website is by using a [Docker](https://www.docker.com/) image. Just run the command:
```
docker-compose up --build
```
And Docker will set up everything for you. The site should be reachable at http://localhost:8080.

You're not completely done yet though. You'll have to feed the database structure and data (+ setup some data folders like the ones containing custom track upload images).  
To do this, run the following command:
```
docker exec -it mkpc_web /root/scripts/setup.sh
```
If there's an error saying that the file was not found, try this command:  
```
docker exec -it mkpc_web sh -c "sed -i 's/\r$//' /root/scripts/setup.sh && sh /root/scripts/setup.sh"
```

Which will create everything you need. The database is reachable at `127.0.0.1:8306` using credentials `mkpc_user` and `mkpc_pwd` (db name `mkpc`).

You can now start developing!


## Dockerless
If you don't want to use a Docker image, it's a little more complex but still doable.

First install [PHP](https://www.php.net/manual/en/install.php) >= 8, [MariaDB](https://mariadb.com/kb/en/getting-installing-and-upgrading-mariadb/) and [Apache](https://httpd.apache.org/docs/current/install.html).
For PHP you'll need the following packages:
```
php-curl php-gd php-dev php-mbstring php-mcrypt php-mysql php-xdebug php-xml
```

If you're on Windows, you'll probably be faster by just installing [Wamp](https://www.wampserver.com/) which comes with all the base packages out of the box.

Then create a MySQL database with the name `mkpc`.  
Feed the database structure and data by importing the SQL script in `docker/php/scripts/setup.sql`.

Then copy some placeholder config files in the root config folder:
```
cp docker/php/config/* config
```
And edit the file `config/db.php` to put the right credentials.

If everything is set up correctly, the site should be reachable at http://localhost/mkpc/ (or whatever the URL you configure in your Apache config).

You can now start developing!

# Tests

A small test suite is provided with the project.

```
npx playwright test
  Runs the end-to-end tests.

npx playwright test --ui
  Starts the interactive UI mode.

npx playwright test --project=chromium
  Runs the tests only on Desktop Chrome.

npx playwright test example
  Runs the tests in a specific file.

npx playwright test --debug
  Runs the tests in debug mode.

npx playwright codegen
  Auto generate tests with Codegen.
```

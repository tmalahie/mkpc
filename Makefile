PHP_SOURCES := $(shell find . -name '*.php' -not -path './node_modules/*' | LC_COLLATE=C sort)

.SUFFIXES:

lint: php-lint translations-lint

php-lint:
	for file in $(PHP_SOURCES); do php -l "$${file}" || exit 1; done

translations-lint:
	php php/tools/lint-translations.php

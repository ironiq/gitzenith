.PHONY: help
.DEFAULT_GOAL := help
NAME := gitlist-fork
VERSION := $(shell git show -s --format=%h)
EXEC_DOCKER ?= docker compose exec -T
EXEC_PHP ?= $(EXEC_DOCKER) php-fpm
EXEC_NODE ?= $(EXEC_DOCKER) node
EXEC_WEB ?= $(EXEC_DOCKER) web

# Display the application manual
help:
	@echo -e "$(NAME) version \033[33m$(VERSION)\n\e[0m"
	@echo -e "\033[1;37mUSAGE\e[0m"
	@echo -e "  \e[4mmake\e[0m <command> [<arg1>] ... [<argN>]\n"
	@echo -e "\033[1;37mAVAILABLE COMMANDS\e[0m"
	@grep -E '^[a-zA-Z_-]+:.*?# .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?# "}; {printf "  \033[32m%-20s\033[0m %s\n", $$1, $$2}'

check-deps: check-local-overrides
	@if ! docker compose --help >/dev/null; then\
	  echo '\n\033[0;31mdocker compose is not installed.';\
	  exit 1;\
	else\
	  echo "\033[0;32mdocker compose installed\033[0m";\
	fi

# Setup dependencies and development configuration
setup: check-deps
	@docker compose pull || true
	@docker compose up -d --build
	$(EXEC_PHP) git config --global --add safe.directory /application
	$(EXEC_PHP) composer install

# Update dependencies
update: check-deps
	@docker compose pull || true
	@docker compose up -d --build
	$(EXEC_PHP) git config --global --add safe.directory /application
	$(EXEC_PHP) composer update

# Create and start containers
up:
	@docker compose up -d

# Cleanup containers and build artifacts
clean:
	@docker compose down
	$(MAKE) setup

# Start a bash session in the PHP container
bash:
	@docker compose exec php-fpm /bin/bash

# Run automated test suite
test:
	$(EXEC_PHP) composer test
	$(EXEC_NODE) npm run test

# Run acceptance test suite
acceptance:
	$(EXEC_NODE) npm run cypress

# Open applicatipn in your browser
show-app:
	xdg-open http://$$(docker-compose port webserver 80)/

# Run code style autoformatter
format:
	$(EXEC_PHP) composer format

# Build application package
build:
	@rm -rf vendor/
	@rm -rf public/assets/*
	@composer install --ignore-platform-reqs --no-dev --no-scripts -o
	@npm run build
	@zip ./build.zip \
	-r * .[^.]* \
	-x '.github/*' \
	-x 'assets/*' \
	-x 'bin/*' \
	-x 'docker/*' \
	-x 'node_modules/*' \
	-x 'tests/*' \
	-x 'var/cache/*' \
	-x 'var/log/*' \
	-x '.git/*' \
	-x '.dockerignore' \
	-x '.editorconfig' \
	-x '.env' \
	-x '.env.dist' \
	-x '.gitignore'  \
	-x '.php-cs-fixer.cache' \
	-x '.php-cs-fixer.php' \
	-x '.phpunit.result.cache' \
	-x '.prettierrc' \
	-x 'composer.json' \
	-x 'composer.lock' \
	-x 'crowdin.yml' \
	-x 'cypress.yml' \
	-x 'cypress.json' \
	-x 'docker-compose.override.yml' \
	-x 'docker-compose.override.yml.dist' \
	-x 'docker-compose.yml' \
	-x 'Makefile' \
	-x 'package-lock.json' \
	-x 'package.json' \
	-x 'phpstan.neon' \
	-x 'phpunit.xml.dist' \
	-x 'postcss.config.js' \
	-x 'webpack.config.js'

fix-perms:
	sudo setfacl -R -m u:root:rwX -m u:`whoami`:rwX var/cache var/log vendor/
	sudo setfacl -dR -m u:root:rwx -m u:`whoami`:rwx var/cache var/log vendor/

check-local-overrides:
	@$(MAKE) --quiet .env
	@$(MAKE) --quiet docker-compose.override.yml

docker-compose.override.yml:
	@ln -s --backup=none docker-compose.override.yml.dist $@

.env:
	@ln -s --backup=none .env.dist $@

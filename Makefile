up: ## Start local docker env
up: vendor node_modules .env docker-compose-up front-build-dev migrate-db

down: ## Stop local docker env
down: docker-compose-down

watch: ## Run webpack watch
watch: node_modules
	$(NODE) $(WEBPACK) --env development --progress --watch

beautify: ## Beautify your code
beautify: vendor
	$(PHP) $(CS_FIXER)

test: ## Run code tests
test: vendor
	$(PHP) $(ARTISAN_TEST)

check: ## Run code linters
check: vendor node_modules
	$(PHP) $(PSALM)
	$(PHP) $(CS_FIXER_CHECK)
	$(COMPOSER) validate --no-check-publish

update: ## Update all dependencies
	$(COMPOSER) update --ignore-platform-reqs
	$(YARN) upgrade

shell: ## Get access to container
	$(DOCKER_EXEC) /bin/sh

vendor: composer.json composer.lock
	$(COMPOSER) install --ignore-platform-reqs

node_modules: package.json yarn.lock
	$(YARN) install

migrate-db: vendor
	$(DOCKER_EXEC) php artisan migrate

.env: vendor
	cp .env.docker.dist .env
	$(PHP) artisan key:generate

docker-compose-up: .env
	docker-compose up -d

docker-compose-down:
	docker-compose down

front-build-dev: node_modules
	$(NODE) $(WEBPACK) --env development

front-build-prod: node_modules
	$(NODE) $(WEBPACK) --env production

ci-lint: vendor
	$(PSALM)
	$(CS_FIXER_CHECK)

ci-test: vendor
	$(ARTISAN_TEST)

help:
	@printf "\033[33mUsage:\033[0m\n  make TARGET\n\033[33m\nTargets:\n"
	@fgrep -h "##" $(MAKEFILE_LIST) | fgrep -v fgrep | sed -e 's/\\$$//' | sed -e 's/##//' | \
	awk 'BEGIN {FS = ":"}; {printf "  \033[33m%-10s\033[0m%s\n", $$1, $$2}'

.DEFAULT_GOAL = help
.PHONY: up down watch beautify test check update shell migrate-db docker-compose-up docker-compose-down \
	front-build-dev front-build-prod ci-lint ci-test help

ifneq ($(SKIP_DOCKER),true)
    BIN_DIR = bin/
endif

DOCKER_EXEC = docker-compose exec app

COMPOSER = $(BIN_DIR)composer
NODE = $(BIN_DIR)node
PHP = $(BIN_DIR)php
YARN = $(BIN_DIR)yarn

WEBPACK = node_modules/webpack/bin/webpack.js --config=node_modules/laravel-mix/setup/webpack.config.js
PSALM = vendor/bin/psalm
CS_FIXER = vendor/bin/php-cs-fixer fix -v --show-progress=dots
CS_FIXER_CHECK = $(CS_FIXER) --dry-run
ARTISAN_TEST = artisan test

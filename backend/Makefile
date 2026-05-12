SHELL := /bin/bash

DC := docker compose
APP := $(DC) exec app
ARTISAN := $(APP) php artisan
COMPOSER := $(APP) composer

export UID := $(shell id -u)
export GID := $(shell id -g)

.PHONY: help up down build rebuild restart sh logs logs-app logs-nginx \
        install migrate migrate-fresh seed fresh test test-coverage \
        lint format analyse qa key cache-clear route-list scribe \
        currency-fetch

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

up: ## Start the docker stack
	$(DC) up -d

down: ## Stop the docker stack
	$(DC) down

build: ## Build images
	$(DC) build

rebuild: ## Rebuild images without cache
	$(DC) build --no-cache

restart: ## Restart all services
	$(DC) restart

sh: ## Open a shell in the app container
	$(APP) bash

logs: ## Tail all logs
	$(DC) logs -f

logs-app: ## Tail app container logs
	$(DC) logs -f app

logs-nginx: ## Tail nginx logs
	$(DC) logs -f nginx

install: ## Run composer install
	$(COMPOSER) install

migrate: ## Run migrations
	$(ARTISAN) migrate

migrate-fresh: ## Drop all tables and re-run migrations
	$(ARTISAN) migrate:fresh

seed: ## Run seeders
	$(ARTISAN) db:seed

fresh: ## Fresh migrate with seed
	$(ARTISAN) migrate:fresh --seed

test: ## Run pest test suite
	$(COMPOSER) test

test-coverage: ## Run tests with coverage
	$(COMPOSER) test:coverage

lint: ## Check code style with pint
	$(COMPOSER) lint

format: ## Apply pint code style fixes
	$(COMPOSER) format

analyse: ## Run phpstan static analysis
	$(COMPOSER) analyse

qa: ## Run lint + analyse + test
	$(COMPOSER) qa

key: ## Generate APP_KEY
	$(ARTISAN) key:generate

cache-clear: ## Clear all Laravel caches
	$(ARTISAN) optimize:clear

route-list: ## List all routes
	$(ARTISAN) route:list

scribe: ## Regenerate API documentation (and refresh the static docs/api copy)
	$(ARTISAN) scribe:generate
	@mkdir -p docs/api
	@cp storage/app/private/scribe/collection.json docs/api/postman_collection.json
	@cp storage/app/private/scribe/openapi.yaml docs/api/openapi.yaml
	@echo "Static copies refreshed: docs/api/{postman_collection.json,openapi.yaml}"

currency-fetch: ## Trigger TCMB currency rates fetch
	$(ARTISAN) currency:fetch

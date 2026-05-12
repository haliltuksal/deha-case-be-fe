SHELL := /bin/bash
DC := docker compose
APP := $(DC) exec app
FE := $(DC) exec frontend
ARTISAN := $(APP) php artisan

# Forwarded to the backend image build so bind-mounted files keep host
# ownership (matters on Linux; Docker Desktop ignores it).
export UID := $(shell id -u)
export GID := $(shell id -g)

.PHONY: help up down build rebuild restart logs ps \
        sh-app sh-frontend \
        migrate seed currency-fetch scribe \
        test fe-test qa health bootstrap

help: ## Show this help
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | \
	  awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-18s\033[0m %s\n", $$1, $$2}'

up: ## Boot the full stack (backend + frontend) in the background
	$(DC) up -d

down: ## Stop the stack and remove containers
	$(DC) down

build: ## Build all images
	$(DC) build

rebuild: ## Rebuild all images from scratch
	$(DC) build --no-cache

restart: ## Restart all services
	$(DC) restart

logs: ## Tail logs for every service
	$(DC) logs -f

ps: ## Show running services
	$(DC) ps

sh-app: ## Open a shell inside the backend PHP container
	$(APP) bash

sh-frontend: ## Open a shell inside the frontend container
	$(FE) sh

migrate: ## Run backend database migrations
	$(ARTISAN) migrate

seed: ## Seed demo accounts + sample products
	$(ARTISAN) db:seed

currency-fetch: ## Pull today's TCMB rates into Redis
	$(ARTISAN) currency:fetch

scribe: ## Regenerate API documentation (HTML + Postman + OpenAPI)
	$(APP) php artisan scribe:generate
	$(APP) sh -c 'mkdir -p docs/api && cp storage/app/private/scribe/collection.json docs/api/postman_collection.json && cp storage/app/private/scribe/openapi.yaml docs/api/openapi.yaml'

test: ## Run the backend Pest suite
	$(APP) composer test

fe-test: ## Run the frontend Vitest suite
	$(FE) pnpm test

qa: ## Run both QA pipelines (lint + analyse + tests)
	$(APP) composer qa
	$(FE) pnpm qa

health: ## Probe the BFF -> Laravel chain from the host
	@curl -fsS http://localhost:$${FRONTEND_PORT:-3000}/api/health | (command -v jq >/dev/null && jq . || cat)

bootstrap: ## One-shot: up + warm currency rates (fresh reviewer setup)
	$(MAKE) up
	@echo "Waiting for backend entrypoint (migrations + seed)..."
	@until curl -fsS -o /dev/null http://localhost:$${APP_PORT:-8080}/api/v1/health 2>/dev/null; do sleep 1; done
	$(MAKE) currency-fetch
	@echo ""
	@echo "Stack ready:"
	@echo "  Storefront    http://localhost:$${FRONTEND_PORT:-3000}"
	@echo "  Laravel API   http://localhost:$${APP_PORT:-8080}/api/v1"
	@echo "  API docs      http://localhost:$${APP_PORT:-8080}/docs"

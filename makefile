.PHONY: help build build-dev build-prod up down restart logs bash console composer migrate cache-clear test

# Environnement par défaut
ENV ?= prod

# Afficher l'aide
help:
	@echo "Commandes disponibles :"
	@echo "  make build       - Construire l'image"
	@echo "  make build-dev   - Construire l'image de développement"
	@echo "  make build-prod  - Construire l'image de production"
	@echo "  make up          - Démarrer les conteneurs"
	@echo "  make down        - Arrêter les conteneurs"
	@echo "  make logs        - Afficher les logs"
	@echo "  make bash        - Ouvrir un shell PHP"
	@echo "  make console     - Exécuter une commande Symfony"
	@echo "  make migrate     - Exécuter les migrations"
	@echo "  make test        - Lancer les tests"

# Build
build:
	docker compose build --build-arg APP_ENV=$(ENV) php nginx

build-dev:
	$(MAKE) build ENV=dev

build-prod:
	$(MAKE) build ENV=prod

# Docker Compose
up:
	docker compose up -d

down:
	docker compose down

restart:
	docker compose restart

logs:
	docker compose logs -f

# Symfony
bash:
	docker compose exec php sh

console:
	docker compose exec php php bin/console

composer:
	docker compose exec php composer

migrate:
	docker compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction

jwt-generate:
	docker compose exec php php bin/console lexik:jwt:generate-keypair --no-interaction --overwrite

cache-clear:
	docker compose exec php php bin/console cache:clear

# Tests
test:
	docker compose run --rm php php bin/phpunit
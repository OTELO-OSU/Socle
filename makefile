.PHONY: help dev-build dev-up dev-down up down bash console composer migrate populate create-admin jwt-generate cache-clear test

help:
	@echo "Commandes disponibles :"
	@echo ""
	@echo "  Build :"
	@echo "    make dev-build       - Construire les images de développement"
	@echo ""
	@echo "  Docker Compose :"
	@echo "    make up              - Démarrer les conteneurs de production"
	@echo "    make down            - Arrêter les conteneurs de production"
	@echo "    make dev-up          - Démarrer les conteneurs de développement"
	@echo "    make dev-down        - Arrêter les conteneurs de développement"
	@echo ""
	@echo "  Symfony :"
	@echo "    make bash            - Ouvrir un shell PHP"
	@echo "    make console         - Exécuter une commande Symfony"
	@echo "    make composer        - Exécuter une commande Composer"
	@echo "    make migrate         - Exécuter les migrations Doctrine"
	@echo "    make populate        - Peupler la base de données"
	@echo "    make create-admin    - Créer un administrateur"
	@echo "    make jwt-generate    - Générer les clés JWT"
	@echo "    make cache-clear     - Vider le cache Symfony"
	@echo ""
	@echo "  Tests :"
	@echo "    make test            - Lancer les tests PHPUnit"
	
# Build
dev-build:
	docker compose -f docker-compose.dev.yml build --build-arg APP_ENV=dev php nginx

# Docker Compose
dev-up:
	docker compose -f docker-compose.dev.yml up -d

up:
	docker compose up -d

down:
	docker compose down

dev-down:
	docker compose -f docker-compose.dev.yml down

# Symfony
bash:
	docker compose exec php sh

console:
	docker compose exec php php bin/console

composer:
	docker compose exec php composer

migrate:
	docker compose run --rm php php bin/console doctrine:migrations:migrate --no-interaction

populate:
	docker compose run --rm php php bin/console app:populate

create-admin:
	docker compose exec php php bin/console app:create-admin

jwt-generate:
	docker compose exec php php bin/console lexik:jwt:generate-keypair --no-interaction --overwrite

cache-clear:
	docker compose exec php php bin/console cache:clear

# Tests
test:
	docker compose run --rm php php bin/phpunit
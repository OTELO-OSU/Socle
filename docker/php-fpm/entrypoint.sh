#!/bin/sh
set -e

# crond hérite de l'environnement du conteneur (APP_ENV, DATABASE_URL, ...) pour les tâches planifiées
crond -b -l 8 -L /dev/stdout

exec "$@"

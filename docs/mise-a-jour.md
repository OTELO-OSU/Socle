# Comment mettre à jour une instance de SOCLE

[Image de la notification](./assets/maj.png)

## Exemple :

Dans l'image précédente on me précise que la nouvelle version v1.0.0 est disponible et que je actuellement sur la v0.0.0.

Dans mon .env je peux actuellement retrouver

```
APP_VERSION=v0.0.0
```

Je vais simplement la modifier de manière à avoir la nouvelle version comme ci-dessous.

```
APP_VERSION=v1.0.0
```

Je vais en suite supprimer les conteneures actuels avec la commande

```sh
make down
```

Puis les relancer avec

```sh
make up
```

J'execute ensuite les migrations pour prévenir tout problème avec le changement de schéma de la base de données

```sh
make migrate
```

Et vous êtes désormais sur la nouvelle version 🎉
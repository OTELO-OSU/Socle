# PLAIRe-OSU

A PLAIRe version for the OSU

## Packages

| Package | Stack | Dev port | Description |
| - | - | - | - |
| [Backend](backend) | Symfony | 80 | Backend API behind a /api reverse proxy |
| [Frontend](frontend) | Vue + Vite | 80 | App behind a / reverse proxy |
| Central tag | nginx | 125 | An nginx exposing a json file of tags |

## Requirements

- Docker

## Devcontainer

A devcontainer gives every contributor the same clean, reproducible environment. Open the project in it (VS Code: "Reopen in Container").

## How it works

PLAIRe OSU is an application to maintain an SIP (Strategic Instrumentation Plan) over the years. All information is contained within platforms. A user in charge to write the SIP of his platform will have the right to edit his platform. A platform is in a laboratory which is itself in the OSU.
A platform can have multiple tags from among those defined in the central tag repository. Users with the same tag as a platform are automatically platform manager

### Roles

```Admin``` - Can do everything

```OSU Director``` - Can do everything

```Unit Director``` - For his laboratory; can create a platform, assign user to be platform manager, edit platform, approve users who sign up to be in charge of platform

```Platform Manager``` - Can edit his platform

```Public``` - Can only view the homepage with all platform with limited information

## Deployment

Firstly you have to rename the .env.example to .env and fill it

```sh
mv .env.example .env
```

Then start all the containers

```sh
docker compose up
```

And finally generate the rsa key for the JWT

```sh
docker compose exec -it php php /var/www/backend/bin/console lexik:jwt:generate-keypair
# Socle

A PLAIRe version for the OSU

## Packages

| Package | Stack | Dev port | Description |
| - | - | - | - |
| [Backend](backend) | Symfony | 3000 | Backend API behind a /api reverse proxy |
| [Frontend](frontend) | Vue + Vite | 3000 | App behind a / reverse proxy |
| Central tag | nginx | 125 | An nginx exposing a json file of tags |

## Requirements

- Docker
- make

## Devcontainer

A devcontainer gives every contributor the same clean, reproducible environment. Open the project in it (VS Code: "Reopen in Container").

## How it works

Socle is an application to maintain an SIP (Strategic Instrumentation Plan) over the years. All information is contained within platforms. A user in charge to write the SIP of his platform will have the right to edit his platform. A platform is in a laboratory which is itself in the OSU.
A platform can have multiple tags from among those defined in the central tag repository. Users with the same tag as a platform are automatically platform manager

The authenticated platform list combines local and harvested platforms, with shared search, tag filters and alphabetical pagination. For harvested platforms, the users shown as having access come from the remote instance. Harvested lists are cached per remote instance for one hour; user access is checked on every request. If a remote instance cannot be reached, the available results remain visible with a warning that counts and filters are incomplete.

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

Then you have to start the containers

```sh
make up
```

Generate the rsa key for the JWT

```sh
make jwt-generate
```

Finally you are going to migrate all the migrations to have the latest sql schema
```sh
make migrate
```

### Create your user as admin

```sh
# It will ask you a mail and a password to create the first admin user (you)
make create-admin
```

### Populate the database for an OSU

The application use a lot of static data (body, cnu, section, etc..) 
By running this command you can choose an OSU to load all of his laboratories
```sh
make populate
```
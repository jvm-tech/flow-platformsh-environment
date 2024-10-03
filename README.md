# platform.sh config package

This package automatically adds service credentials, platform.sh environment-variables and platform.sh variables to your
php environment.

## Install

`composer require jvmtech-flowplatformsh`

## Usage

create a `.platform.env` in the `FLOW_ROOT` of your project. Add the desired environment variables to this file in the
following syntax:

```
<ENV_VAR_NAME>=<(RELATIONSHIP_NAME|variables|platform)>.<KEY>
```

Example:

Given the following platform.app.yaml configuration:

```
variables:
  env:
    FLOW_CONTEXT: 'Production/PlatformSh'

relationships:
  database: 'database:mysql'
```

Populate the php environment with these values from relationships:

```
// from relationship
DATABASE_HOST=database.host
DATABASE_PORT=database.port
DATABASE_NAME=database.path
DATABASE_USER=database.username
DATABASE_PASSWORD=database.password

// from platform.sh environment variables
SENTRY_DSN=variable.SENTRY_DSN

// from platform.sh variable
SENTRY_ENVIRONMENT=platform.environment
```

Use the environment variables in your flow-configuration like this (
in `Configuration/Production/PlatformSh/Settings.yaml`):

```
Neos:
  Flow:
    persistence:
      backendOptions:
        driver: pdo_mysql
        dbname: '%env:DATABASE_NAME%'
        port: '%env:DATABASE_PORT%'
        user: '%env:DATABASE_USER%'
        password: '%env:DATABASE_PASSWORD%'
        host: '%env:DATABASE_HOST%'
```

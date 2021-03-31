ToDoList
========

Base du projet #8 : Améliorez un projet existant

https://openclassrooms.com/projects/ameliorer-un-projet-existant-1

## Project badges

[![SymfonyInsight](https://insight.symfony.com/projects/78a8aa9d-287a-4520-b0a1-b7e5ea0a0f40/mini.svg)](https://insight.symfony.com/projects/78a8aa9d-287a-4520-b0a1-b7e5ea0a0f40)
[![Maintainability](https://api.codeclimate.com/v1/badges/92e55a3a7dd16d1775e9/maintainability)](https://codeclimate.com/github/pierregaimard/projet08/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/92e55a3a7dd16d1775e9/test_coverage)](https://codeclimate.com/github/pierregaimard/projet08/test_coverage)

## Project installation procedure

### Clone the repository
On your server, open a terminal, go to your web directory then launch
the following command:  
`git clone https://github.com/pierregaimard/projet08.git`

### Install project dependencies
Then jump into the project directory `cd projet08`
and install the project dependencies by running the command:  
`composer install --classmap-authoritative`

### Setup .env variables
In the `.env` file, set up the different information:

#### Database URL
Add your database information to `DATABASE_URL` variable

#### Env
Change the env type to prod: `APP_ENV=prod`

### Initialize the database
Now run the following commands to set up the database:
-   `php bin/console d:d:c` Database creation
-   `php bin/console d:m:m` Database schema migration
-   `php bin/console hautelook:fixtures:load` Load fixtures

### Unit and Functional Tests
Then you need to run the tests to be sure that the application is functional:

The first step is to configure the database url for tests by setting
the `DATABASE_URL` variable in the `.env.test` file.

Then initialize the database for tests environment
-   `php bin/console d:d:c --env=test` Database creation
-   `php bin/console d:m:m --env=test` Database schema migration

Then launch the tests by running the following command:  
`php bin/phpunit`

### Dump the autoload for performance improvement
Run the following command to improve loading performances
`dump-autoload --classmap-authoritative`

If the tests are all green you're done!.

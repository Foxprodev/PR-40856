# Sample debug app

Related to issue : https://github.com/symfony/symfony/issues/40034

Install app

- clone repo and install dependencies
- configure a database and create it (`d:d:c`)
- apply existing migrations (`d:m:m`)
- load fixtures (`d:f:l -n`)
- launch a webserver (via `symfony` or `php -S localhost:8000 -t public`)

Steps to reproduce

- execute `curl localhost:8080/api/contacts`
    - you should see a JSON of contacts with 2 fields
- go to `src/Entity/Contact.php` and add or remove `@Groups("contacts_get")` on the entity properties, save
- execute `curl localhost:8080/api/contacts`
    - :warning: you should see _no changes_
- execute `cache:clear`
- execute `curl localhost:8080/api/contacts`
    - :warning: you should see _changes_


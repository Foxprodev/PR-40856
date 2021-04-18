# Sample debug app

Related to PR : https://github.com/symfony/symfony/pull/40856

```
git clone git@github.com:monteiro/PR-40856.git
composer install
// up the postgresql db
docker-compose up
bin/console doctrine:migrations:migrate
symfony serve
```


- execute `curl localhost:8000/api/contacts`
    - you should see a JSON of contacts with 2 fields
- go to `src/Entity/Contact.php` and add or remove `@Groups("contacts_get")` on the entity properties, save
- execute `curl localhost:8080/api/contacts`
    - :warning: you should see _no changes_
- execute `cache:clear`
- execute `curl localhost:8080/api/contacts`
    - :warning: you should see _changes_

Notes: This issue was reported by @jhice

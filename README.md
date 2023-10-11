# Upsert-Doctrine

Upsert-Doctrine is a PHP library aimed at providing an elegant solution for upsert operations in Doctrine. 
This library simplifies the process of either inserting a new record or updating an existing one 
in a single operation, all within the Doctrine ORM ecosystem.

## Installation

Install Upsert-Doctrine using [Composer](https://getcomposer.org/):

```bash
composer require pavelvais/upsert-doctrine
```

## Usage
Below is a basic example demonstrating how to use Upsert-Doctrine:

```php
use PavelVais\UpsertDoctrine\UpsertManager;

$upsertQueryBuilder = new UpsertQueryBuilder($entityManager);
$data = [
    'id' => 1,
    'name' => 'John Doe',
    'email' => 'johndoe@example.com'
];

// Execute the upsert
$upsertQueryBuilder->upsertQuery($data,DoctrineOrmEntity::class);
```
## Roadmap

- [✅] Milestone 1: Basic Upsert Functionality
- [ ] Milestone 2: Batch Upsert method
- [ ] Milestone 3: Postgres Support
- [ ] Milestone 4: Live database testing


# Contributing

Contributions are welcome! For major changes, please open an issue first to discuss what you would like to change.

Ensure to update tests as necessary.

# License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

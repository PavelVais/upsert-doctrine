# Upsert-Doctrine

[![PHP Unit Tests](https://github.com/PavelVais/upsert-doctrine/actions/workflows/php.yml/badge.svg?branch=main)](https://github.com/PavelVais/upsert-doctrine/actions/workflows/php.yml)

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

// Create upsert query
$query = $upsertQueryBuilder->upsertQuery($data, DoctrineOrmEntity::class);

// Execute upsert query
$statement = $entityManager->getConnection()->prepare($sql);

foreach ($data as $column => $value) {
    $statement->bindValue(':' . $column, $value);
}

$result = $statement->executeQuery();
```

## Local Testing with Docker

This project includes a Docker setup to run local testing and development. 
Here are the steps to get started:

1. **Build and manage the Containers**:
   Build and start the containers in the background.
   ```bash
   make build
    ```
   To stop and remove the containers, use the following command:
    ```bash
   make stop
    ```

2. Installing Dependencies:
   Install the project dependencies using Composer with the following command:
    ```bash
    make composer-install
    ```
3. Running Tests:
    Run the PHPUnit tests to ensure everything is working as expected.  
    ```bash
    make test
    ```


## Roadmap

- [x] Milestone 1: Basic Upsert Functionality
- [ ] Milestone 2: Batch Upsert method
- [ ] Milestone 3: Postgres Support
- [ ] Milestone 4: Live database testing


# Contributing

Contributions are welcome! For major changes, please open an issue first to discuss what you would like to change.

Ensure to update tests as necessary.

# License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details._

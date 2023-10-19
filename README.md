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

$entityManager = // ... get your Doctrine entity manager here
$upsertManager = new UpsertManager($entityManager);

// Example for Single Upsert
$data = [
    'book_id' => 1,
    'author_id' => 2,
    'ownership_type' => 2,
];
$repositoryClass = DoctrineOrmEntity::class; // Replace with your actual repository class

try {
    $result = $upsertManager->execute($data, $repositoryClass);
    // $result will contain the number of affected rows
} catch (\Exception $e) {
    // Handle exceptions
}

// Example for Batch Upsert
$batchData = [
    [
        'book_id' => 1,
        'author_id' => 2,
        'ownership_type' => 2,
    ],
    [
        'book_id' => 2,
        'author_id' => 3,
        'ownership_type' => 1,
    ],
];

try {
    $result = $upsertManager->executeBatch($batchData, $repositoryClass);
    // $result will contain the number of affected rows
} catch (\Exception $e) {
    // Handle exceptions
}

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

- [x] Basic Upsert Functionality
- [x] Batch Upsert method
- [ ] Query Manager
- [ ] Custom On Conflict callback
- [ ] Postgres Support
- [ ] Live database testing


# Contributing

Contributions are welcome! For major changes, please open an issue first to discuss what you would like to change.

Ensure to update tests as necessary.

# License

This project is licensed under the MIT License - _see the [LICENSE](LICENSE) file for details._

# MeraShop

## Table of Contents

- [Quick Start](#quick-start)

## Quick Start

1. **Add local domains to `/etc/hosts`:**

   ```bash
   127.0.0.1 merashop.test
   127.0.0.1 api.merashop.test
   127.0.0.1 admin-api.merashop.test
   127.0.0.1 sources.merashop.test
   ```

2. **Start the application:**

   ```bash
   docker compose build
   docker compose up -d
   ```

3. **Access the application:**

- Web: [merashop.test](http://merashop.test)
- Public API: [api.merashop.test](http://api.merashop.test)
- Admin API: [admin-api.merashop.test](http://admin-api.merashop.test)
- Sources: [sources.merashop.test](http://sources.merashop.test)


## Development Workflow
### Code Quality Tools

**Enabled GrumPHP tasks:**

1. GrumPHP :octocat: [GitHub](https://github.com/phpro/grumphp)
2. [CS Fixer](https://cs.symfony.com/)[GitHub](https://github.com/PHP-CS-Fixer/PHP-CS-Fixer)
3. [PHPStan](https://phpstan.org/user-guide/getting-started)[GitHub](https://github.com/phpstan/phpstan)
4. [Deptrac](https://deptrac.github.io/deptrac/)[GitHub](https://github.com/deptrac/deptrac)

**Manual execution examples in container:**

- **All tasks**:

  ```bash
  php vendor/bin/grumphp run -n
  ```

- **CS task** with the progress bar:

  ```bash
  php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.dist.php --ansi --no-interaction
  ```

- **PHPStan task** with the progress bar:

  ```bash
  php vendor/bin/phpstan analyse --configuration=phpstan.dist.neon --memory-limit=-1 --no-ansi --no-interaction
  ```

- **Deptrac tasks** with the progress bar:

  ```bash
  php vendor/bin/deptrac analyse --config-file=deptrac.yaml
  php vendor/bin/deptrac analyse --config-file=deptrac-modules.yaml
  ```

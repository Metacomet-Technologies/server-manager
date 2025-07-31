# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

### Development Commands
```bash
# Install dependencies
composer install

# Run tests
composer test

# Run tests with coverage
composer test-coverage

# Static analysis
composer analyse

# Format code
composer format
```

### Package Usage Commands
```bash
# Install the package
composer require metacomet-technologies/server-manager

# Publish assets
php artisan vendor:publish --tag="server-manager-migrations"
php artisan vendor:publish --tag="server-manager-config"
php artisan migrate
```

## Architecture

This is a Laravel package for managing local and remote servers via interactive shell, following standard Laravel package patterns:

- **Service Provider**: `src/ServerManagerServiceProvider.php` - Uses Spatie's PackageServiceProvider for registration
- **Main Class**: `src/ServerManager.php` - Core functionality (currently empty skeleton)
- **Facade**: `src/Facades/ServerManager.php` - Laravel facade for easy access
- **Artisan Command**: `src/Commands/ServerManagerCommand.php` - CLI interface
- **Configuration**: `config/server-manager.php` - Package configuration
- **Migrations**: `database/migrations/` - Database structure

### Key Technical Decisions
- PHP 8.3+ minimum requirement
- Supports Laravel 10, 11, and 12
- Uses Pest PHP for testing instead of PHPUnit
- PHPStan level 5 for static analysis with Laravel extensions
- Laravel Pint for code formatting

### Testing Structure
- Tests located in `tests/`
- Uses Orchestra Testbench for package testing
- Architecture tests in `tests/ArchTest.php`
- Feature tests in `tests/ExampleTest.php`

### Current State
The package is in skeleton/initial state with empty implementations. Core server management functionality needs to be implemented in the ServerManager class.
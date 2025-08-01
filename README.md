# Laravel Server Manager

[![Latest Version on Packagist](https://img.shields.io/packagist/v/metacomet-technologies/server-manager.svg?style=flat-square)](https://packagist.org/packages/metacomet-technologies/server-manager)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/metacomet-technologies/server-manager/run-tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/metacomet-technologies/server-manager/actions?query=workflow%3Arun-tests+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/metacomet-technologies/server-manager/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/metacomet-technologies/server-manager/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/metacomet-technologies/server-manager.svg?style=flat-square)](https://packagist.org/packages/metacomet-technologies/server-manager)

A Laravel package for managing local and remote servers through an interactive shell. Features include SSH connections, real-time terminal access via WebSockets, session sharing, and a complete React-based web interface.

## Support us

[<img src="https://github-ads.s3.eu-central-1.amazonaws.com/server-manager.jpg?t=1" width="419px" />](https://spatie.be/github-ad-click/server-manager)

We invest a lot of resources into creating [best in class open source packages](https://spatie.be/open-source). You can support us by [buying one of our paid products](https://spatie.be/open-source/support-us).

We highly appreciate you sending us a postcard from your hometown, mentioning which of our package(s) you are using. You'll find our address on [our contact page](https://spatie.be/about-us). We publish all received postcards on [our virtual postcard wall](https://spatie.be/open-source/postcards).

## Features

- 🖥️ **Local & Remote Server Management** - Connect to any server via SSH or manage the local server
- 🔐 **Multiple Authentication Methods** - Support for password and SSH key authentication
- 🚀 **Real-time Terminal** - Interactive terminal with WebSocket support using Laravel Reverb
- 👥 **Session Sharing** - Share terminal sessions with other users with view/execute permissions
- 🌐 **RESTful API** - Complete API for headless operation
- ⚛️ **React Frontend** - Optional React-based web interface with Inertia.js
- 🔒 **Security** - Encrypted credential storage, authorization policies, and access gates
- 📝 **Command History** - Track all executed commands with output

## Requirements

- PHP 8.3+
- Laravel 10, 11, or 12
- Optional: Laravel Reverb for WebSocket support
- Optional: SSH2 PHP extension for better performance

## Installation

### Quick Install

```bash
composer require metacomet-technologies/server-manager
php artisan server-manager:install
```

That's it! 🎉 The package comes with pre-built assets and requires no npm installation or build steps.

### What Makes This Different?

Server Manager is **completely self-contained**:

- ✅ **No npm install required** - All assets are pre-built and served from the package
- ✅ **No build step needed** - Works immediately after installation
- ✅ **No conflicts** - Runs as a separate Inertia app alongside your existing pages
- ✅ **Zero frontend configuration** - Everything works out of the box

### Manual Installation

1. Install the package:
```bash
composer require metacomet-technologies/server-manager
```

2. Publish and run migrations:
```bash
php artisan vendor:publish --tag="server-manager-migrations"
php artisan migrate
```

3. (Optional) Publish the configuration:
```bash
php artisan vendor:publish --tag="server-manager-config"
```

5. (Optional) Configure Laravel Reverb for WebSockets:
```bash
composer require laravel/reverb
php artisan reverb:install
```

## Configuration

After installation, configure the package in `config/server-manager.php`:

```php
// SSH driver: 'phpseclib' or 'ssh2'
'ssh_driver' => env('SERVER_MANAGER_SSH_DRIVER', 'phpseclib'),

// Web interface settings
'web' => [
    'enabled' => true,
    'prefix' => 'server-manager', // Access at yourapp.com/server-manager
    'middleware' => ['web', 'auth'],
],

// Local server access gate
'local_server_gate' => 'server-manager:access-local',
```

### Setting up the Local Server Access Gate

Define the gate in your `AuthServiceProvider`:

```php
Gate::define('server-manager:access-local', function ($user) {
    return $user->hasRole('admin'); // Or your own logic
});
```

## Usage

### Web Interface

After installation, visit `yourapp.com/server-manager` to access the web interface.

### API Usage

```php
use Metacomet\ServerManager\Facades\ServerManager;

// Create a server
$server = ServerManager::createServer([
    'user_id' => auth()->id(),
    'name' => 'Production Server',
    'host' => '192.168.1.100',
    'username' => 'deploy',
    'auth_type' => 'key',
    'private_key' => file_get_contents('/path/to/key'),
]);

// Create a session
$session = ServerManager::createSession(
    auth()->id(),
    $server,
    'Deployment Session'
);

// Execute commands
$result = ServerManager::execute($session, 'ls -la');
echo $result['output'];
```

### API Endpoints

```
# Servers
GET    /api/server-manager/servers
POST   /api/server-manager/servers
GET    /api/server-manager/servers/{id}
PUT    /api/server-manager/servers/{id}
DELETE /api/server-manager/servers/{id}
POST   /api/server-manager/servers/{id}/test-connection

# Sessions
GET    /api/server-manager/sessions
POST   /api/server-manager/sessions
GET    /api/server-manager/sessions/{id}
DELETE /api/server-manager/sessions/{id}
POST   /api/server-manager/sessions/{id}/share
DELETE /api/server-manager/sessions/{id}/share/{userId}

# Commands
POST   /api/server-manager/sessions/{id}/execute
POST   /api/server-manager/sessions/{id}/execute-async
GET    /api/server-manager/sessions/{id}/processes/{processId}/output
GET    /api/server-manager/sessions/{id}/processes/{processId}/status
DELETE /api/server-manager/sessions/{id}/processes/{processId}
GET    /api/server-manager/sessions/{id}/history
```

## WebSocket Support

For real-time terminal functionality, start Laravel Reverb:

```bash
php artisan reverb:start
```

The package will automatically use WebSockets when available.

## Testing

```bash
composer test
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Development

### Building Assets

```bash
npm install
npm run build
```

### Running Tests

```bash
composer test
```

### Releasing a New Version

We provide a release script that handles the entire release process:

```bash
# Bump patch version (1.0.0 -> 1.0.1)
./release.sh

# Bump minor version (1.0.0 -> 1.1.0)
./release.sh minor

# Bump major version (1.0.0 -> 2.0.0)
./release.sh major
```

The release script will:
- Run tests and static analysis
- Build frontend assets
- Create and push a git tag
- Trigger GitHub Actions for release

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [Devon Garbalosa](https://github.com/DGarbs51)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

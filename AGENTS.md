# Agent Guide: Referrer Spam Blocker

## Project Overview

This is the **Referrer Spam Blocker** project - a PHP library that generates configuration files for various web servers (Apache, Nginx, IIS, uWSGI, Caddy, Varnish, HAProxy, Lighttpd) and Google Analytics segments to block referrer spam traffic. OpenLiteSpeed users should use the Apache .htaccess file as OpenLiteSpeed is Apache-compatible.

### Key Details

- **Language**: PHP 8.3+
- **Namespace**: `StevieRay\`
- **Main Purpose**: Generate blacklist configuration files from a domain list to prevent referrer spam
- **Output**: Configuration files for multiple web servers and Google Analytics exclusion lists

## Development Environment: DDEV

This project uses **DDEV** for local development. DDEV is a Docker-based local development environment.

### DDEV Configuration

- **Project Name**: `referrer-spam-blocker`
- **PHP Version**: 8.3
- **Web Server**: nginx-fpm
- **Database**: Not used (omitted in config)
- **Composer Version**: 2

### Important DDEV Commands

When working with this project, use DDEV commands to run PHP/Composer commands:

```bash
# Start the DDEV environment
ddev start

# Run composer commands
ddev composer install
ddev composer update
ddev composer test
ddev composer quality

# Run PHP scripts
ddev exec php run.php
ddev exec php vendor/bin/phpunit

# Access the container shell
ddev ssh

# Stop the environment
ddev stop
```

### DDEV Hooks

The project has post-start hooks configured in `.ddev/config.yaml`:
- Automatically runs `composer install` when DDEV starts
- Automatically runs `php run.php` to generate config files

### Running Commands

**Always use `ddev exec` or `ddev composer`** instead of running commands directly, as PHP and Composer are inside the DDEV container, not on the host system.

Examples:
- ❌ `composer install` → ✅ `ddev composer install`
- ❌ `php run.php` → ✅ `ddev exec php run.php`
- ❌ `phpunit` → ✅ `ddev exec php vendor/bin/phpunit`

## Project Structure

```
referrer-spam-blocker/
├── src/
│   ├── CLI/              # Command-line interface
│   ├── Config/           # Config generators for different server types
│   ├── Domain/           # Domain processing logic
│   ├── Service/          # File writing services
│   └── domains.txt       # Source domain list (9118+ domains)
├── tests/
│   ├── Unit/             # Unit tests
│   └── Integration/      # Integration tests
├── .ddev/                # DDEV configuration
│   └── config.yaml       # DDEV project config
├── composer.json         # PHP dependencies
├── phpcs.xml            # PHP CodeSniffer rules (PSR-12)
├── phpunit.xml          # PHPUnit configuration
├── run.php              # CLI entry point
└── Generated files:     # Output files (git-tracked)
    ├── .htaccess
    ├── referral-spam.conf
    ├── referral-spam.vcl
    ├── web.config
    └── google-exclude-*.txt
```

## Key Components

### Generator (`src/Generator.php`)
Main class that orchestrates config file generation for all server types.

### Config Generators (`src/Config/`)
Each server type has its own generator class:
- `ApacheConfigGenerator` → `.htaccess`
- `NginxConfigGenerator` → `referral-spam.conf`
- `VarnishConfigGenerator` → `referral-spam.vcl`
- `IISConfigGenerator` → `web.config`
- `CaddyConfigGenerator` / `CaddyV2ConfigGenerator` → Caddy configs
- `UwsgiConfigGenerator` → `referral_spam.res`
- `HAProxyConfigGenerator` → `referral-spam.haproxy`
- `LighttpdConfigGenerator` → `referral-spam.lighttpd.conf`
- `GoogleAnalyticsConfigGenerator` → `google-exclude-*.txt` (split files)

**Note:** OpenLiteSpeed users should use the Apache `.htaccess` file as OpenLiteSpeed is Apache-compatible.

### Domain Processor (`src/Domain/DomainProcessor.php`)
Handles reading and processing the domain list from `src/domains.txt`.

## Development Workflow

1. **Start DDEV**: `ddev start`
2. **Install dependencies**: `ddev composer install` (auto-runs on start)
3. **Make changes** to source code
4. **Run tests**: `ddev composer test` or `ddev exec php vendor/bin/phpunit`
5. **Check code quality**: `ddev composer quality`
6. **Generate configs**: `ddev exec php run.php` (auto-runs on start)
7. **Commit changes** including generated config files

## Testing

The project uses:
- **PHPUnit 12.4** for unit tests
- **PHPStan** (Level 8) for static analysis
- **PHP CodeSniffer 4.0** (PSR-12 standard) for code style
- **Psalm** for additional static analysis

Run all quality checks:
```bash
ddev composer quality
```

This runs: PHPStan → PHPCS → Psalm → Tests

## Code Style

- **PSR-12** coding standard
- PHP 8.3+ features (typed properties, enums, etc.)
- Strict types: `declare(strict_types=1);` in all files

## Important Notes for Agents

1. **Always use DDEV commands** - PHP/Composer are containerized
2. **Generated files are tracked** - Config files in the root are committed to git
3. **Domain list is large** - `src/domains.txt` has 9000+ domains
4. **No database** - This is a pure PHP library, no DB needed
5. **PHP 8.3+ only** - Uses modern PHP features
6. **PHP_CodeSniffer 4.0** - Recently upgraded, no custom sniffs needed

## Common Tasks

### Adding a new config generator type
1. Create new class in `src/Config/` extending `AbstractConfigGenerator`
2. Implement required methods
3. Register in `src/Generator.php`
4. Add tests in `tests/Unit/Config/`

### Updating the domain list
1. Edit `src/domains.txt`
2. Run `ddev exec php run.php` to regenerate all configs
3. Commit both the domain list and generated configs

### Running specific tests
```bash
ddev exec php vendor/bin/phpunit tests/Unit/GeneratorTest.php
```

### Checking code style
```bash
ddev exec php vendor/bin/phpcs src/ tests/
ddev exec php vendor/bin/phpcbf src/ tests/  # Auto-fix
```


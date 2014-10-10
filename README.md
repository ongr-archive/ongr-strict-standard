# PSR2 strict standard

Strict PHPCS standard for PSR-2 code based on Squiz standards.

## Installation

Add to your `composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:ongr-io/psr2-strict-standard.git"
        }
    ],
    "require-dev": {
        "ongr/psr2-strict-standard": "~1.0-alpha"
    }
}
```

## Running

After `composer update`, run:

```sh
vendor/bin/phpcs -p --standard=vendor/ongr/psr2-strict-standard/ONGR --ignore=vendor/,Tests/app/,Resources/public/ ./
```

## PHPStorm helper

Configure PHPCS: http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html

Configure this standard:

1. Go to Settings > Inspections > PHP > PHP Code Sniffer Validation.
1. Mark checkbox ON for "PHP Code Sniffer Validation".
1. Select Custom standard.
1. Locate `vendor/ongr/psr2-strict-standard/ONGR` standard directory, press OK.

Code should be validated automatically on each PHP file edit.

# PSR2 strict standard

Strict PHPCS standard for PSR-2 code based on Squiz standards.

## Usage

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
        "ongr/psr2-strict-standard": "~1.0"
    }
}
```

After `composer update`, run:

```sh
vendor/bin/phpcs -p --standard=vendor/ongr/psr2-strict-standard/ONGR --ignore=vendor/,Tests/app/,Resources/public/ ./
```

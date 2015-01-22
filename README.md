# ONGR Strict Coding Standard

[![Build](https://travis-ci.org/ongr-io/ongr-strict-standard.svg?branch=master)](https://travis-ci.org/ongr-io/ongr-strict-standard)
[![Total Downloads](https://poser.pugx.org/ongr/ongr-strict-standard/downloads.svg)](https://packagist.org/packages/ongr/ongr-strict-standard)
[![Latest Stable Version](https://poser.pugx.org/ongr/ongr-strict-standard/v/stable.svg)](https://packagist.org/packages/ongr/ongr-strict-standard)
[![Latest Unstable Version](https://poser.pugx.org/ongr/ongr-strict-standard/v/unstable.svg)](https://packagist.org/packages/ongr/ongr-strict-standard)
[![License](https://poser.pugx.org/ongr/ongr-strict-standard/license.svg)](https://packagist.org/packages/ongr/ongr-strict-standard)

This standard provides strict code style checking for whitespace, commenting style and PHPDoc. It is PSR-2 compatible, enforces good programming and documentation practices.

## Example

Ever wanted to standardize your team's code style?

```php
<?php
namespace   Project/MyClass ;

use Foo;

use  Bar;
use Baz;

/**
 *  this class has method

 */
class MyClass
{

    public  function foo( $param){
        $a = $param *  2;
        return $a+1;

    }
}
```

*No more!*

## Features

- **Standardizes whitespace** almost **everywhere**. E.g. between methods, spaces around operators, indentation of statements, etc.
- Required **class short description** everywhere except for PHPUnit testing classes.
- Required **method short descriptions** except for setters / getters and some magic methods.
- Comments start with **capital letter** and ends with appropriate punctuation. This suggests proper documentation and **reduces laziness**.
- PHP >= 5.4 **short array syntax** is required.
- Require **type hinting** in PHPDoc for all parameters. **Require @return and @throws tags** where necessary.
- Code **must not be aligned** in assignments and array definitions.
- Strings should be "double quoted" only with a reason ($variable interpolation inside, etc.)

## Acknowledgement

Our work is based solely on Squiz Labs [Squiz coding standard](https://github.com/squizlabs/PHP_CodeSniffer) and [opensky Symfony2 coding standard](https://github.com/escapestudios/Symfony2-coding-standard).

## Installation

Composer:

```json
{
    "require-dev": {
        "ongr/ongr-strict-standard": "~1.0-beta",
        "squizlabs/php_codesniffer": "~1"
    }
}
```

Or optionally you can install globally to all projects at `~/.composer/composer.json`.

Then: `composer global update`.

**Warning** if you are planing on developing, then sources should be located in `ONGR` directory.

For example, when cloning add target directory:

```sh
    git clone git@github.com:<username>/ongr-strict-standard.git ONGR
```

## Running

```sh
vendor/bin/phpcs -p --standard=/home/<user>/.composer/vendor/ongr/ongr-strict-standard/ONGR --ignore=vendor/,Tests/app/,Resources/public/ ./
```

Note: do not use `~` for HOME parameter, as PHPCS will not expand it.

**IDEs also support running Code Sniffer** and adding error annotations directly on editor's source code (e.g. PHPStorm). Please see your IDE's documentation on how to add standard from custom directory.

## PHPStorm Helper

Configure PHPCS: http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html

Configure this standard:

1. Go to Settings > Inspections > PHP > PHP Code Sniffer Validation.
1. Mark checkbox ON for "PHP Code Sniffer Validation".
1. Select Custom standard.
1. Locate `vendor/ongr/ongr-strict-standard/ONGR` standard directory, press OK.

Code should be validated automatically on each PHP file edit.

# Licensing

This repository contains copied and or modified files from other packages. Their licenses are defined and clearly stated in those files and must be followed.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

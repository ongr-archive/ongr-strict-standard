ONGR Strict Coding Standard
===========================

.. image:: https://travis-ci.org/ongr-io/ongr-strict-standard.svg?branch=master
    :target: https://travis-ci.org/ongr-io/ongr-strict-standard

.. image:: https://poser.pugx.org/ongr/ongr-strict-standard/downloads.svg
    :target: https://packagist.org/packages/ongr/ongr-strict-standard

.. image:: https://poser.pugx.org/ongr/ongr-strict-standard/v/stable.svg
    :target: https://packagist.org/packages/ongr/ongr-strict-standard

.. image:: https://poser.pugx.org/ongr/ongr-strict-standard/v/unstable.svg
    :target: https://packagist.org/packages/ongr/ongr-strict-standard

.. image:: https://poser.pugx.org/ongr/ongr-strict-standard/license.svg
    :target: https://packagist.org/packages/ongr/ongr-strict-standard


This standard provides strict code style checking for whitespace, commenting style and PHPDoc. It is PSR-2 compatible, enforces good programming and documentation practices.

Example
-------

Ever wanted to standardize your team's code style? This is a **PSR-2 compliant code**:

.. code:: php

    <?php
    namespace Project\MyClass ;
    
    use Foo;
    
    /**
     *  this class has method
    
     */
    class MyClass
    {
    
        public function foo($param)
        {
            $a = $param *  2;
            return $a+1;
    
        }
    }

.. warning::
No more code like this!

After ONGR Strict Standard validation:

.. code:: bash

    ----------------------------------------------------------------------
    FOUND 10 ERRORS AFFECTING 8 LINES
    ----------------------------------------------------------------------
    1 | ERROR | [ ] There must be one blank line after the php open tag
    |       |     and no whitespaces
    2 | ERROR | [x] Space found before semicolon; expected "MyClass;"
    |       |     but found "MyClass ;"
    10 | ERROR | [ ] Class name doesn't match filename; expected "class
    |       |     testas"
    11 | ERROR | [ ] Expected no blank lines after an opening brace, 1
    |       |     found
    13 | ERROR | [ ] Missing function doc comment
    15 | ERROR | [x] Expected 1 space after "*"; 2 found
    16 | ERROR | [ ] Missing blank line before return statement
    16 | ERROR | [x] Expected 1 space before "+"; 0 found
    16 | ERROR | [x] Expected 1 space after "+"; 0 found
    18 | ERROR | [x] Expected no blank lines before closing function
    |       |     brace; 1 found
    ----------------------------------------------------------------------
    PHPCBF CAN FIX THE 5 MARKED SNIFF VIOLATIONS AUTOMATICALLY
    ----------------------------------------------------------------------
    
    Time: 68ms; Memory: 5.75Mb

Features
--------

- **Standardizes whitespace** almost **everywhere**. E.g. between methods, spaces around operators, indentation of statements, etc.
- Required **class short description** everywhere except for PHPUnit testing classes.
- Required **method short descriptions** except for setters / getters and some magic methods.
- Comments start with **capital letter** and ends with appropriate punctuation. This suggests proper documentation and **reduces laziness**.
- **Short array syntax** is required.
- Require **type hinting** in PHPDoc for all parameters. **Require @return and @throws tags** where necessary.
- Code **must not be aligned** in assignments and array definitions.
- Strings should be "double quoted" only with a reason ($variable interpolation inside, etc.)

Requirements
------------
- PHP >=5.4
- CodeSniffer 2.x

Acknowledgement
---------------

Our work is based solely on Squiz Labs [Squiz coding standard](https://github.com/squizlabs/PHP_CodeSniffer) and [opensky Symfony2 coding standard](https://github.com/escapestudios/Symfony2-coding-standard).

Installation
------------

Composer:

.. code:: json

    {
       "require-dev": {
          "ongr/ongr-strict-standard": "~1.0",
          "squizlabs/php_codesniffer": "~1"
       }
    }


Or optionally you can install globally to all projects at `~/.composer/composer.json`.

Then: `composer global update`.

.. warning::
    If you are planing on developing, then sources should be located in `ONGR` directory.

For example, when cloning add target directory:

.. code:: bash

    git clone git@github.com:<username>/ongr-strict-standard.git ONGR


Running
-------
.. code:: bash

    vendor/bin/phpcs -p --standard=/home/<user>/.composer/vendor/ongr/ongr-strict-standard/ONGR --ignore=vendor/,Tests/app/,Resources/public/ ./

.. note::
    Do not use `~` for HOME parameter, as PHPCS will not expand it.

.. note::
    **IDEs also support running Code Sniffer** and adding error annotations directly on editor's source code (e.g. PHPStorm). Please see your IDE's documentation on how to add standard from custom directory.

PHPStorm Helper
---------------

Configure PHPCS: http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html

Configure this standard:

1. Go to Settings > Inspections > PHP > PHP Code Sniffer Validation.
1. Mark checkbox ON for "PHP Code Sniffer Validation".
1. Select Custom standard.
1. Locate `vendor/ongr/ongr-strict-standard/ONGR` standard directory, press OK.

Code should be validated automatically on each PHP file edit.

License
-------

This bundle is under the MIT license. Please, see the complete license
in the bundle ``LICENSE`` file.
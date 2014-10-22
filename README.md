# PSR2 strict standard

Strict PHPCS standard for PSR-2 code based on Squiz standards.

## Installation

Run command:
````bash
composer global require "ongr/psr2-strict-standard=*"
````

OR

Add to your global `~/.composer/composer.json`:

```json
{
    "repositories": [
        {
            "type": "vcs",
            "url": "git@github.com:ongr-io/psr2-strict-standard.git"
        }
    ],
    "require": {
        "ongr/psr2-strict-standard": "~1.0-alpha",
        "squizlabs/php_codesniffer": "~1"
    }
}
```

After `composer global update`, run:

## Running

```sh
vendor/bin/phpcs -p --standard=/home/<user>/.composer/vendor/ongr/psr2-strict-standard/ONGR --ignore=vendor/,Tests/app/,Resources/public/ ./
```

Note: do not use ~ for HOME parameter, as PHPCS will not expand it.

IDEs also support running Code Sniffer and adding error annotations directly on editor's source code (e.g. PHPStorm). Please see your IDE's documentation on how to add standard from custom directory.

## PHPStorm helper

Configure PHPCS: http://www.jetbrains.com/phpstorm/webhelp/using-php-code-sniffer-tool.html

Configure this standard:

1. Go to Settings > Inspections > PHP > PHP Code Sniffer Validation.
1. Mark checkbox ON for "PHP Code Sniffer Validation".
1. Select Custom standard.
1. Locate `vendor/ongr/psr2-strict-standard/ONGR` standard directory, press OK.

Code should be validated automatically on each PHP file edit.

# Licensing

This repository contains copied and or modified files from other packages. Their licenses are defined and clearly stated in those files and must be followed.

THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

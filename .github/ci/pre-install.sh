#!/usr/bin/env bash
# Runs inside the ExtDN containers after the local-source repository is configured and before Composer installs
# the Magento project. Keep it idempotent: the same script serves the unit, integration and PHPStan jobs.
set -e

# Until iranimij/module-base is on Packagist, CI resolves it from GitHub.
composer config repositories.iranimij-base vcs https://github.com/iranimij/module-base

# PHPStan job only: the Magento-aware PHPStan extension (factories, proxies, generated classes).
if [ -n "${INPUT_PHPSTAN_LEVEL:-}" ]; then
    composer require --dev --no-interaction --no-progress --with-all-dependencies bitexpert/phpstan-magento
fi

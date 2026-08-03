#!/usr/bin/env bash

set -euo pipefail

php artisan migrate --force
php artisan queue:restart

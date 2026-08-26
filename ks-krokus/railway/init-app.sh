#!/usr/bin/env bash

set -euo pipefail

if ! php artisan storage:link; then
    if [ ! -L public/storage ]; then
        echo "Nie udało się utworzyć public/storage." >&2
        exit 1
    fi
fi

php artisan migrate --force
php artisan queue:restart

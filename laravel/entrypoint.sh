#!/bin/sh
set -e

rm -rf storage/framework/views/* storage/framework/cache/data/* bootstrap/cache/*.php 2>/dev/null || true

chmod -R a+rwX storage bootstrap/cache

exec "$@"
#!/bin/sh
set -e

CERT_DIR="$(dirname "$0")/certs"
mkdir -p "$CERT_DIR"

if [ -f "$CERT_DIR/meetly.crt" ]; then
  echo "Сертификат уже есть пропускаю генерацию"
  exit 0
fi

openssl req -x509 -nodes -newkey rsa:2048 \
  -keyout "$CERT_DIR/meetly.key" \
  -out    "$CERT_DIR/meetly.crt" \
  -days 825 \
  -subj "/CN=meetly.ru" \
  -addext "subjectAltName=DNS:meetly.ru,DNS:api.meetly.ru"

echo "Готово"
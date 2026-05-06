#!/usr/bin/env bash
set -euo pipefail

ROOT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
ARTIFACT_DIR="${ROOT_DIR}/deploy-artifacts"
TIMESTAMP="$(date '+%Y%m%d-%H%M%S')"
ARTIFACT_NAME="ditib-ahlen-portal-${TIMESTAMP}.tar.gz"
ARTIFACT_PATH="${ARTIFACT_DIR}/${ARTIFACT_NAME}"

cd "${ROOT_DIR}"

echo "Installing production PHP dependencies..."
composer install --no-dev --optimize-autoloader

echo "Building Vite assets..."
npm ci
npm run build

mkdir -p "${ARTIFACT_DIR}"

echo "Creating artifact: ${ARTIFACT_PATH}"
COPYFILE_DISABLE=1 tar -czf "${ARTIFACT_PATH}" \
  --exclude='./.git' \
  --exclude='./.codex' \
  --exclude='./.cursor' \
  --exclude='./.idea' \
  --exclude='./.vscode' \
  --exclude='./==logs' \
  --exclude='./*.md' \
  --exclude='./**/*.md' \
  --exclude='./.env' \
  --exclude='./.env.bak' \
  --exclude='./.env.backup' \
  --exclude='./.env.production' \
  --exclude='./auth.json' \
  --exclude='./node_modules' \
  --exclude='./deploy-artifacts' \
  --exclude='./database/database.sqlite' \
  --exclude='./public/hot' \
  --exclude='./public/storage' \
  --exclude='./storage/logs/*.log' \
  --exclude='./storage/framework/cache/data/*' \
  --exclude='./storage/framework/sessions/*' \
  --exclude='./storage/framework/testing/*' \
  --exclude='./storage/framework/views/*.php' \
  --exclude='./.DS_Store' \
  .

echo "Done."
du -h "${ARTIFACT_PATH}"

#!/usr/bin/env bash
# Vercel build: compile the React portfolio, then gather everything served
# statically into public/. api/ stays out so its PHP runs as functions.
set -euo pipefail
cd "$(dirname "$0")/.."

(cd web && npm ci && npm run build)
rm -rf web/node_modules

rm -rf public
mkdir -p public
cp -r site public/site
cp -r assets uploads public/
find . -maxdepth 1 -type f \( -iname '*.png' -o -iname '*.jpg' -o -iname '*.ico' -o -iname '*.mp4' \) -exec cp {} public/ \;

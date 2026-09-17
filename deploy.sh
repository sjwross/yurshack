#!/bin/bash
set -euo pipefail

# Deploy Yur Shack main site to Namecheap cPanel (yurshack.com).
# Main domain document root is ~/public_html.
# Add yurshack.co.uk as a parked/alias domain in cPanel to share this tree.
#
# Usage:
#   ./deploy.sh
#   ./deploy.sh user@host [port]
#
# Defaults match the Namecheap Stellar account. Override via args or env:
#   YURSHACK_SSH_HOST, YURSHACK_SSH_USER, YURSHACK_SSH_PORT, YURSHACK_SSH_KEY

ROOT="$(cd "$(dirname "$0")" && pwd)"
SRC="${ROOT}/yurshack.com"
REMOTE_DIR="public_html"

HOST="${YURSHACK_SSH_HOST:-premium139.web-hosting.com}"
USER="${YURSHACK_SSH_USER:-yursgcra}"
PORT="${YURSHACK_SSH_PORT:-21098}"
KEY="${YURSHACK_SSH_KEY:-$HOME/.ssh/yurshack_namecheap}"

if [[ "${1:-}" == *@* ]]; then
  USER="${1%@*}"
  HOST="${1#*@}"
  PORT="${2:-$PORT}"
elif [[ -n "${1:-}" ]]; then
  HOST="$1"
  PORT="${2:-$PORT}"
fi

TARGET="${USER}@${HOST}"

SSH=(ssh -p "$PORT" -o BatchMode=yes)
RSYNC_SSH="ssh -p ${PORT} -o BatchMode=yes"
if [[ -f "$KEY" ]]; then
  SSH+=(-i "$KEY")
  RSYNC_SSH="ssh -i ${KEY} -p ${PORT} -o BatchMode=yes"
fi

if [[ ! -d "$SRC" ]]; then
  echo "Missing source dir: $SRC" >&2
  exit 1
fi

echo "Deploying $SRC -> ${TARGET}:~/${REMOTE_DIR}"
"${SSH[@]}" "$TARGET" "mkdir -p '${REMOTE_DIR}'"

if command -v rsync >/dev/null 2>&1; then
  rsync -avz \
    --exclude '.DS_Store' \
    --exclude '._*' \
    --exclude '.git' \
    --exclude 'deploy.sh' \
    --exclude '.env' \
    --exclude '.env.*' \
    --exclude 'README.md' \
    --exclude 'vendor/' \
    --exclude 'composer.phar' \
    --exclude 'composer.json' \
    --exclude 'composer.lock' \
    --exclude '.well-known/' \
    -e "$RSYNC_SSH" \
    "$SRC/" "${TARGET}:~/${REMOTE_DIR}/"
else
  tar -C "$SRC" \
    --exclude='.DS_Store' \
    --exclude='._*' \
    --exclude='.git' \
    --exclude='deploy.sh' \
    --exclude='.env' \
    --exclude='.env.*' \
    --exclude='README.md' \
    --exclude='vendor' \
    --exclude='composer.phar' \
    --exclude='composer.json' \
    --exclude='composer.lock' \
    -czf - . \
    | "${SSH[@]}" "$TARGET" "tar -xzf - -C '${REMOTE_DIR}'"
fi

echo "Done. Visit https://yurshack.com/ after DNS/SSL settle."
echo "Secrets live in ~/.env.yurshack on the server (not overwritten by deploy)."
echo "Park yurshack.co.uk on this account in cPanel if it should share the same site."

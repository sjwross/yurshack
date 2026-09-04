#!/bin/bash
set -euo pipefail

# Deploy Yur Shack main site to projtoolbox.com (hosting.com / A2).
# While .com and .co.uk share one server tree, this publishes yurshack.com/
# to public_html/yurshack/ (both aliases serve that path).
#
# Domains must already be added in cPanel as aliases of projtoolbox.com.
#
# Usage:
#   ./deploy.sh [ssh-host]

HOST="${1:-projtoolbox.com}"
ROOT="$(cd "$(dirname "$0")" && pwd)"
SRC="${ROOT}/yurshack.com"
REMOTE_DIR="public_html/yurshack"

if [[ ! -d "$SRC" ]]; then
  echo "Missing source dir: $SRC" >&2
  exit 1
fi

echo "Deploying $SRC -> ${HOST}:~/${REMOTE_DIR}"
ssh "$HOST" "mkdir -p '${REMOTE_DIR}'"
tar -C "$SRC" \
  --exclude='.DS_Store' \
  --exclude='._*' \
  --exclude='.git' \
  --exclude='deploy.sh' \
  -czf - . \
  | ssh "$HOST" "tar -xzf - -C '${REMOTE_DIR}'"
echo "Done. Visit https://yurshack.com/ and https://yurshack.co.uk/ after DNS/SSL settle."
echo "Note: while domains share one tree, only yurshack.com/ is published; keep both folders in sync or update deploy when they diverge."

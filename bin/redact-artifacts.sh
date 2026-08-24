#!/usr/bin/env bash
set -euo pipefail

if [ -z "${CI_UA:-}" ]; then
  echo "CI_UA not set, nothing to redact."
  exit 0
fi

DIRS="${*:-test-results playwright-report}"

# A User-Agent is sent in plaintext, so it lands in traces and reports.
# Zips are handled separately; rewriting bytes inside an archive corrupts it.
redact_plain() {
  grep -rlaF -- "$CI_UA" "$1" 2>/dev/null | grep -v '\.zip$' || true
}

# grep -q closes the pipe early, unzip takes SIGPIPE, and pipefail turns a
# successful match into a failure. Count instead so the stream is consumed.
zip_has_ua() {
  local n
  n=$( { unzip -p "$1" 2>/dev/null | grep -acF -- "$CI_UA"; } 2>/dev/null || true )
  [ "${n:-0}" -gt 0 ]
}

contains_ua() {
  local found=0
  for d in $DIRS; do
    [ -d "$d" ] || continue
    if [ -n "$(redact_plain "$d")" ]; then found=1; fi
    while IFS= read -r -d '' z; do
      if unzip -p "$z" 2>/dev/null | grep -qaF -- "$CI_UA"; then found=1; fi
    done < <(find "$d" -name '*.zip' -print0)
  done
  return $((1 - found))
}

for d in $DIRS; do
  [ -d "$d" ] || continue

  redact_plain "$d" | while IFS= read -r f; do
    [ -n "$f" ] || continue
    perl -pi -e 's/\Q$ENV{CI_UA}\E/REDACTED-CI-UA/g' "$f"
    echo "redacted: $f"
  done

  while IFS= read -r -d '' z; do
    zip_has_ua "$z" || continue
    tmp=$(mktemp -d)
    unzip -q "$z" -d "$tmp"
    grep -rlaF -- "$CI_UA" "$tmp" 2>/dev/null | while IFS= read -r f; do
      perl -pi -e 's/\Q$ENV{CI_UA}\E/REDACTED-CI-UA/g' "$f"
    done
    rm -f "$z"
    (cd "$tmp" && zip -qr "$OLDPWD/$z" .)
    rm -rf "$tmp"
    echo "redacted inside: $z"
  done < <(find "$d" -name '*.zip' -print0)
done

if contains_ua; then
  echo "::error::CI_UA still present in artifacts after redaction. Refusing to upload."
  exit 1
fi
echo "Redaction complete."

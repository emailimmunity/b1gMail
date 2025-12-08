#!/usr/bin/env bash
# verify-sync.sh - Code-Sync Verification Script
# Vergleicht /var/www/html (Container App) mit /host-src (Host Mount)
set -euo pipefail

APP_DIR="/var/www/html"
HOST_DIR="/host-src"

echo "========================================"
echo "  b1gmail Code-Sync Verification"
echo "========================================"
echo ""
echo "App Directory:  ${APP_DIR}"
echo "Host Directory: ${HOST_DIR}"
echo ""

# Check if host directory is mounted
if [ ! -d "$HOST_DIR" ]; then
  echo "❌ ERROR: $HOST_DIR ist nicht gemountet!"
  echo ""
  echo "Lösung:"
  echo "  1. Füge in docker-compose.override.yml hinzu:"
  echo "     volumes:"
  echo "       - ./src:/host-src:ro"
  echo "  2. Restart: docker-compose restart b1gmail"
  echo ""
  exit 1
fi

echo "✅ Host-Mount vorhanden"
echo ""

# Step 1: Structure comparison
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  STRUKTUR-VERGLEICH (diff -rq)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

DIFF_OUTPUT=$(diff -rq "$APP_DIR" "$HOST_DIR" \
  --exclude=cache \
  --exclude=webdisk \
  --exclude=upload \
  --exclude=vendor \
  --exclude=node_modules \
  --exclude=.git \
  --exclude=plugins_all \
  --exclude=plugins_working \
  --exclude=plugins_broken \
  2>&1 || true)

if [ -z "$DIFF_OUTPUT" ]; then
  echo "✅ Struktur: IDENTISCH"
  echo "   Keine Unterschiede gefunden!"
else
  echo "⚠️  Struktur: UNTERSCHIEDE GEFUNDEN"
  echo ""
  echo "$DIFF_OUTPUT"
  echo ""
  echo "❌ WARNUNG: Container und Host sind NICHT synchron!"
  exit 2
fi

echo ""

# Step 2: Content verification (MD5)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  INHALT-VERGLEICH (md5sum)"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

TMPFILE_APP=$(mktemp)
TMPFILE_HOST=$(mktemp)

echo "Berechne MD5-Hashes (App)..."
(
  cd "$APP_DIR"
  find . -type f \
    ! -path "./cache/*" \
    ! -path "./webdisk/*" \
    ! -path "./upload/*" \
    ! -path "./vendor/*" \
    ! -path "./node_modules/*" \
    ! -path "./.git/*" \
    ! -path "./plugins_all/*" \
    ! -path "./plugins_working/*" \
    ! -path "./plugins_broken/*" \
    -print0 \
    | sort -z \
    | xargs -0 md5sum 2>/dev/null
) > "$TMPFILE_APP"

echo "Berechne MD5-Hashes (Host)..."
(
  cd "$HOST_DIR"
  find . -type f \
    ! -path "./cache/*" \
    ! -path "./webdisk/*" \
    ! -path "./upload/*" \
    ! -path "./vendor/*" \
    ! -path "./node_modules/*" \
    ! -path "./.git/*" \
    ! -path "./plugins_all/*" \
    ! -path "./plugins_working/*" \
    ! -path "./plugins_broken/*" \
    -print0 \
    | sort -z \
    | xargs -0 md5sum 2>/dev/null
) > "$TMPFILE_HOST"

echo ""
echo "Vergleiche Hashes..."
if diff -q "$TMPFILE_APP" "$TMPFILE_HOST" > /dev/null 2>&1; then
  echo "✅ Inhalt: IDENTISCH"
  echo "   Alle MD5-Hashes stimmen überein!"
  
  # Cleanup
  rm -f "$TMPFILE_APP" "$TMPFILE_HOST"
else
  echo "⚠️  Inhalt: UNTERSCHIEDE GEFUNDEN"
  echo ""
  echo "Detaillierte Unterschiede:"
  diff -u "$TMPFILE_APP" "$TMPFILE_HOST" | head -50
  echo ""
  echo "❌ WARNUNG: MD5-Hashes unterscheiden sich!"
  
  # Cleanup
  rm -f "$TMPFILE_APP" "$TMPFILE_HOST"
  exit 3
fi

echo ""

# Step 3: Plugin verification
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  PLUGIN-VERIFIKATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

PLUGIN_COUNT_APP=$(find "$APP_DIR/plugins" -maxdepth 1 -name "*.plugin.php" -type f 2>/dev/null | wc -l)
PLUGIN_COUNT_HOST=$(find "$HOST_DIR/plugins" -maxdepth 1 -name "*.plugin.php" -type f 2>/dev/null | wc -l)

echo "Plugins (App):  $PLUGIN_COUNT_APP"
echo "Plugins (Host): $PLUGIN_COUNT_HOST"
echo ""

if [ "$PLUGIN_COUNT_APP" -eq "$PLUGIN_COUNT_HOST" ]; then
  echo "✅ Plugin-Anzahl: IDENTISCH"
  
  # List plugins
  echo ""
  echo "Aktive Plugins:"
  find "$APP_DIR/plugins" -maxdepth 1 -name "*.plugin.php" -type f -exec basename {} \; | sort | nl
else
  echo "❌ Plugin-Anzahl: UNTERSCHIEDLICH!"
  exit 4
fi

echo ""

# Step 4: Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ ZUSAMMENFASSUNG"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✅ Struktur:       100% identisch"
echo "✅ Inhalt (MD5):   100% identisch"
echo "✅ Plugins:        $PLUGIN_COUNT_APP aktiv"
echo ""
echo "🎉 Container und Host sind PERFEKT SYNCHRON!"
echo ""
echo "Datum: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

exit 0

#!/usr/bin/env bash
# check-plugin-status.sh - Plugin Status Verification
# Prüft ob plugins-status.md und tatsächliche Plugin-Files übereinstimmen
set -euo pipefail

PLUGINS_DIR="/var/www/html/plugins"
PLUGINS_BROKEN_DIR="/var/www/html/plugins_broken"
STATUS_FILE="/var/www/html/docs/plugins-status.md"

echo "========================================"
echo "  Plugin-Status Verification"
echo "========================================"
echo ""

# Check if status file exists
if [ ! -f "$STATUS_FILE" ]; then
  echo "❌ ERROR: $STATUS_FILE nicht gefunden!"
  exit 1
fi

echo "Status-Datei: $STATUS_FILE"
echo ""

# Extract plugin names from markdown table
# Format: | 1 | `filename.php` | Name | ✅ aktiv | ...
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  LESE PLUGIN-STATUS AUS DOKU"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Parse "aktiv" plugins from markdown
ACTIVE_PLUGINS=$(grep -E '^\| [0-9]+ \| `.*\.plugin\.php` \|.*✅ aktiv' "$STATUS_FILE" \
  | sed -E 's/.*`([^`]+\.plugin\.php)`.*/ \1/' \
  | sort)

# Parse "geblockt" plugins from markdown
BLOCKED_PLUGINS=$(grep -E '^\| [0-9]+ \| `.*\.plugin\.php` \|.*❌ geblockt' "$STATUS_FILE" \
  | sed -E 's/.*`([^`]+\.plugin\.php)`.*/ \1/' \
  | sort)

ACTIVE_COUNT=$(echo "$ACTIVE_PLUGINS" | wc -w)
BLOCKED_COUNT=$(echo "$BLOCKED_PLUGINS" | wc -w)

echo "Dokumentierte Plugins:"
echo "  ✅ Aktiv:    $ACTIVE_COUNT"
echo "  ❌ Geblockt: $BLOCKED_COUNT"
echo ""

# Check filesystem
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  PRÜFE FILESYSTEM"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Count actual files
ACTUAL_ACTIVE=$(find "$PLUGINS_DIR" -maxdepth 1 -name "*.plugin.php" -type f 2>/dev/null | wc -l)
ACTUAL_BLOCKED=$(find "$PLUGINS_BROKEN_DIR" -maxdepth 1 -name "*.plugin.php" -type f 2>/dev/null | wc -l)

echo "Tatsächliche Plugins:"
echo "  ✅ In plugins/:        $ACTUAL_ACTIVE"
echo "  ❌ In plugins_broken/: $ACTUAL_BLOCKED"
echo ""

# Verification
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  VERIFIKATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

ERRORS=0

# Check each documented "aktiv" plugin
echo "Prüfe aktive Plugins..."
for plugin in $ACTIVE_PLUGINS; do
  plugin=$(echo "$plugin" | xargs)  # trim whitespace
  if [ -f "$PLUGINS_DIR/$plugin" ]; then
    echo "  ✅ $plugin"
  else
    echo "  ❌ $plugin - FEHLT in $PLUGINS_DIR!"
    ERRORS=$((ERRORS + 1))
  fi
done

echo ""
echo "Prüfe geblockte Plugins..."
for plugin in $BLOCKED_PLUGINS; do
  plugin=$(echo "$plugin" | xargs)  # trim whitespace
  
  # Should NOT be in active plugins
  if [ -f "$PLUGINS_DIR/$plugin" ]; then
    echo "  ❌ $plugin - FEHLERHAT: In plugins/ obwohl geblockt!"
    ERRORS=$((ERRORS + 1))
  else
    echo "  ✅ $plugin - Korrekt NICHT in plugins/"
  fi
  
  # Should be in broken plugins
  if [ -f "$PLUGINS_BROKEN_DIR/$plugin" ]; then
    echo "     ✅ Liegt in plugins_broken/"
  else
    echo "     ⚠️  Nicht in plugins_broken/ gefunden"
  fi
done

echo ""

# Check for undocumented plugins
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  NICHT-DOKUMENTIERTE PLUGINS"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

ALL_DOCUMENTED="$ACTIVE_PLUGINS $BLOCKED_PLUGINS"

UNDOCUMENTED=0
for file in "$PLUGINS_DIR"/*.plugin.php; do
  if [ ! -f "$file" ]; then
    continue
  fi
  
  filename=$(basename "$file")
  
  if ! echo "$ALL_DOCUMENTED" | grep -q "$filename"; then
    echo "  ⚠️  $filename - NICHT in plugins-status.md dokumentiert!"
    UNDOCUMENTED=$((UNDOCUMENTED + 1))
  fi
done

if [ $UNDOCUMENTED -eq 0 ]; then
  echo "  ✅ Alle Plugins sind dokumentiert"
fi

echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 ZUSAMMENFASSUNG"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $ERRORS -eq 0 ] && [ $UNDOCUMENTED -eq 0 ]; then
  echo "✅ ERFOLGREICH"
  echo ""
  echo "✅ Alle dokumentierten Plugins vorhanden"
  echo "✅ Keine geblockten Plugins in plugins/"
  echo "✅ Keine undokumentierten Plugins"
  echo ""
  echo "Status: plugins-status.md ist KORREKT"
  echo ""
  exit 0
else
  echo "❌ FEHLER GEFUNDEN"
  echo ""
  echo "Fehler:        $ERRORS"
  echo "Undokumentiert: $UNDOCUMENTED"
  echo ""
  echo "Aktion: plugins-status.md aktualisieren!"
  echo ""
  exit 1
fi

#!/usr/bin/env bash
# Git Pre-Commit Hook Template für b1gMail
# Führt automatische Verifikations-Checks durch bevor Commit durchgeht
set -euo pipefail

echo ""
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  b1gMail Pre-Commit Verification"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Stelle sicher, dass wir im Repo-Root sind
REPO_ROOT="$(git rev-parse --show-toplevel)"
cd "$REPO_ROOT"

FAILED=0

# Check 1: Container läuft?
echo "🔍 Prüfe Container-Status..."
if ! docker ps --filter "name=b1gmail$" --format "{{.Names}}" | grep -q "b1gmail"; then
  echo "❌ ERROR: Container 'b1gmail' läuft nicht!"
  echo ""
  echo "Aktion: docker-compose up -d"
  echo ""
  exit 1
fi
echo "✅ Container läuft"
echo ""

# Check 2: Code-Sync Verifikation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1/2  Code-Sync Verification"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if ! docker exec b1gmail bash /var/www/html/tools/verify-sync.sh 2>&1 | tail -20; then
  echo ""
  echo "❌ ERROR: verify-sync.sh failed!"
  echo ""
  echo "Container ↔ Host sind NICHT synchron!"
  echo ""
  echo "Mögliche Ursachen:"
  echo "  - Docker Bind-Mount funktioniert nicht"
  echo "  - Dateien wurden direkt im Container geändert"
  echo "  - /host-src Mount fehlt"
  echo ""
  echo "Lösung:"
  echo "  docker-compose down"
  echo "  docker-compose up -d"
  echo "  docker exec b1gmail bash /var/www/html/tools/verify-sync.sh"
  echo ""
  FAILED=1
else
  echo ""
  echo "✅ Code-Sync: OK"
fi

echo ""

# Check 3: Plugin-Status Verifikation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2/2  Plugin-Status Verification"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if ! docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh 2>&1 | tail -20; then
  echo ""
  echo "❌ ERROR: check-plugin-status.sh failed!"
  echo ""
  echo "Plugin-Status und Dokumentation sind INKONSISTENT!"
  echo ""
  echo "Mögliche Ursachen:"
  echo "  - Plugin hinzugefügt aber nicht in docs/plugins-status.md"
  echo "  - Plugin entfernt aber noch in docs/plugins-status.md"
  echo "  - Geblockte Plugins liegen noch in src/plugins/"
  echo ""
  echo "Lösung:"
  echo "  1. Prüfe: docker exec b1gmail ls -1 /var/www/html/plugins/*.php"
  echo "  2. Update: docs/plugins-status.md"
  echo "  3. Test: docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh"
  echo ""
  FAILED=1
else
  echo ""
  echo "✅ Plugin-Status: OK"
fi

echo ""

# Finale Entscheidung
if [ "$FAILED" -ne 0 ]; then
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo "❌ COMMIT ABGEBROCHEN"
  echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
  echo ""
  echo "Bitte behebe die obigen Fehler und versuche es erneut."
  echo ""
  echo "Zum Überspringen (NICHT empfohlen):"
  echo "  git commit --no-verify"
  echo ""
  exit 1
fi

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "✅ ALLE CHECKS BESTANDEN"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "✅ Code-Sync: 100% identisch"
echo "✅ Plugin-Status: Dokumentiert und korrekt"
echo ""
echo "Commit wird durchgeführt..."
echo ""

exit 0

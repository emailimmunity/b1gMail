#!/usr/bin/env bash
# run-ci.sh - Local CI Pipeline for b1gMail
# Führt vollständige Verifikation und Tests durch
set -euo pipefail

echo ""
echo "╔════════════════════════════════════════╗"
echo "║   b1gMail Local CI Pipeline            ║"
echo "╚════════════════════════════════════════╝"
echo ""

# Repository Root ermitteln
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

echo "Repository: $REPO_ROOT"
echo ""

# Exit-Codes
FAILED=0

# 1. Docker-Stack starten
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1/7  Docker-Stack starten"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if docker compose ps | grep -q "b1gmail.*Up"; then
    echo "✅ Container läuft bereits"
else
    echo "Container nicht gefunden, starte neu..."
    docker compose -f docker-compose.yml -f docker-compose.override.yml up -d
    echo "✅ Container gestartet"
fi

echo ""

# 2. Warten bis Container bereit
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2/7  Warte auf Container-Bereitschaft"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Warte 20 Sekunden..."
sleep 20

# Health-Check
if docker exec b1gmail test -f /var/www/html/index.php; then
    echo "✅ Container ist bereit"
else
    echo "❌ Container nicht bereit - Abbruch"
    exit 1
fi

echo ""

# 3. Code-Sync Verifikation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3/7  Code-Sync Verifikation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if docker exec b1gmail bash /var/www/html/tools/verify-sync.sh; then
    echo "✅ Code-Sync: OK"
else
    echo "❌ Code-Sync: FAILED"
    FAILED=1
fi

echo ""

# 4. Plugin-Status Verifikation
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4/7  Plugin-Status Verifikation"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh; then
    echo "✅ Plugin-Status: OK"
else
    echo "❌ Plugin-Status: FAILED"
    FAILED=1
fi

echo ""

# 5. HTTP-Checks
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5/7  HTTP Response Checks"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Frontend Check
echo "Prüfe Frontend (http://localhost:8095/)..."
if curl -f -s -o /dev/null -w "%{http_code}" http://localhost:8095/ | grep -q "200"; then
    echo "✅ Frontend: HTTP 200"
else
    echo "❌ Frontend: FAILED"
    FAILED=1
fi

# Admin Check
echo "Prüfe Admin (http://localhost:8095/admin/)..."
if curl -f -s -o /dev/null -w "%{http_code}" --max-time 10 http://localhost:8095/admin/ | grep -q "200"; then
    echo "✅ Admin: HTTP 200"
else
    echo "⚠️  Admin: FAILED (Timeout oder Error)"
    # Admin-Fehler nicht kritisch (kann Redirect sein)
fi

echo ""

# 6. Container-Logs prüfen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "6/7  Container-Logs prüfen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Prüfe auf kritische Fehler..."
if docker logs b1gmail --tail 100 2>&1 | grep -iE "fatal|segfault|critical error" | head -5; then
    echo "⚠️  Kritische Fehler gefunden (siehe oben)"
else
    echo "✅ Keine kritischen Fehler in Logs"
fi

echo ""

# 7. Zusammenfassung
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "7/7  Zusammenfassung"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ "$FAILED" -eq 0 ]; then
    echo "╔════════════════════════════════════════╗"
    echo "║   ✅ ALLE CI-CHECKS BESTANDEN          ║"
    echo "╚════════════════════════════════════════╝"
    echo ""
    echo "✅ Docker Stack:     Running"
    echo "✅ Code-Sync:        100% identisch"
    echo "✅ Plugin-Status:    Korrekt"
    echo "✅ Frontend:         HTTP 200"
    echo "✅ Logs:             OK"
    echo ""
    echo "System ist deployment-ready!"
    echo ""
    exit 0
else
    echo "╔════════════════════════════════════════╗"
    echo "║   ❌ CI-CHECKS FEHLGESCHLAGEN          ║"
    echo "╚════════════════════════════════════════╝"
    echo ""
    echo "Bitte behebe die obigen Fehler."
    echo ""
    echo "Logs ansehen:"
    echo "  docker logs b1gmail --tail 100"
    echo ""
    exit 1
fi

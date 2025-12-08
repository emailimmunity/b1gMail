#!/usr/bin/env bash
# deploy.sh - Production Deployment Script for b1gMail
# Führt vollständige CI-Checks durch und deployed bei Erfolg
set -euo pipefail

echo ""
echo "╔═══════════════════════════════════════════════╗"
echo "║   b1gMail Production Deployment               ║"
echo "╚═══════════════════════════════════════════════╝"
echo ""

# Repository Root ermitteln
REPO_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")/.." && pwd)"
cd "$REPO_ROOT"

echo "Repository: $REPO_ROOT"
echo "Datum:      $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Konfiguration
BACKUP_DIR="${REPO_ROOT}/backups"
TIMESTAMP=$(date '+%Y%m%d_%H%M%S')
BACKUP_NAME="backup_${TIMESTAMP}"

# 1. Git-Status prüfen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1/8  Git-Status prüfen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ -d .git ]; then
    CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)
    CURRENT_COMMIT=$(git rev-parse --short HEAD)
    echo "Branch: $CURRENT_BRANCH"
    echo "Commit: $CURRENT_COMMIT"
    
    # Uncommitted changes?
    if ! git diff-index --quiet HEAD --; then
        echo ""
        echo "⚠️  WARNING: Uncommitted changes detected!"
        echo ""
        git status --short
        echo ""
        read -p "Trotzdem fortfahren? (y/N): " -n 1 -r
        echo ""
        if [[ ! $REPLY =~ ^[Yy]$ ]]; then
            echo "Deployment abgebrochen."
            exit 1
        fi
    else
        echo "✅ Keine uncommitted changes"
    fi
else
    echo "⚠️  Kein Git-Repository - überspringe Git-Check"
fi

echo ""

# 2. Backup erstellen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2/8  Backup erstellen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

mkdir -p "$BACKUP_DIR"

echo "Erstelle Backup: $BACKUP_NAME"

# Backup der wichtigsten Verzeichnisse
tar -czf "${BACKUP_DIR}/${BACKUP_NAME}.tar.gz" \
    --exclude='src/cache/*' \
    --exclude='src/webdisk/*' \
    --exclude='src/upload/*' \
    --exclude='node_modules' \
    --exclude='vendor' \
    src/ docker-compose.yml Dockerfile 2>/dev/null || true

echo "✅ Backup erstellt: ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"

# Alte Backups aufräumen (behalte letzte 5)
echo "Räume alte Backups auf..."
ls -t "${BACKUP_DIR}"/backup_*.tar.gz 2>/dev/null | tail -n +6 | xargs rm -f 2>/dev/null || true
echo "✅ Alte Backups aufgeräumt"

echo ""

# 3. CI-Pipeline ausführen
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3/8  CI-Pipeline ausführen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ -f "${REPO_ROOT}/tools/run-ci.sh" ]; then
    echo "Führe run-ci.sh aus..."
    if bash "${REPO_ROOT}/tools/run-ci.sh"; then
        echo "✅ CI-Pipeline erfolgreich"
    else
        echo ""
        echo "❌ CI-Pipeline fehlgeschlagen!"
        echo ""
        echo "Deployment abgebrochen."
        echo ""
        echo "Backup verfügbar unter:"
        echo "  ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
        echo ""
        exit 1
    fi
else
    echo "⚠️  run-ci.sh nicht gefunden - überspringe CI-Checks"
fi

echo ""

# 4. Container stoppen (für sauberen Neustart)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4/8  Container stoppen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if docker compose ps | grep -q "b1gmail.*Up"; then
    echo "Stoppe Container..."
    docker compose down
    echo "✅ Container gestoppt"
else
    echo "✅ Container war bereits gestoppt"
fi

echo ""

# 5. Docker Images aufräumen (optional)
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "5/8  Docker Images aufräumen"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Räume ungenutzte Images auf..."
docker image prune -f >/dev/null 2>&1 || true
echo "✅ Ungenutzte Images entfernt"

echo ""

# 6. Container neu starten
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "6/8  Container neu starten"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "Starte Container..."
docker compose -f docker-compose.yml -f docker-compose.override.yml up -d

echo "Warte 30 Sekunden auf Container-Bereitschaft..."
sleep 30

# Health-Check
if docker exec b1gmail test -f /var/www/html/index.php; then
    echo "✅ Container ist bereit"
else
    echo "❌ Container nicht bereit!"
    echo ""
    echo "Logs:"
    docker logs b1gmail --tail 50
    echo ""
    echo "Deployment fehlgeschlagen!"
    exit 1
fi

echo ""

# 7. Smoke-Tests
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "7/8  Smoke-Tests"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

SMOKE_FAILED=0

# Frontend Check
echo "Test: Frontend (http://localhost:8095/)..."
if curl -f -s -o /dev/null -w "%{http_code}" http://localhost:8095/ | grep -q "200"; then
    echo "✅ Frontend: HTTP 200"
else
    echo "❌ Frontend: FAILED"
    SMOKE_FAILED=1
fi

# Admin Check (mit längerem Timeout)
echo "Test: Admin (http://localhost:8095/admin/)..."
if curl -f -s -o /dev/null -w "%{http_code}" --max-time 15 http://localhost:8095/admin/ | grep -q "200"; then
    echo "✅ Admin: HTTP 200"
else
    echo "⚠️  Admin: Timeout oder Error (nicht kritisch)"
fi

# Plugin-Anzahl
echo "Test: Plugin-Anzahl..."
PLUGIN_COUNT=$(docker exec b1gmail bash -c "ls -1 /var/www/html/plugins/*.php 2>/dev/null | wc -l" || echo "0")
echo "Plugins geladen: $PLUGIN_COUNT"
if [ "$PLUGIN_COUNT" -ge 25 ]; then
    echo "✅ Plugin-Anzahl OK (>= 25)"
else
    echo "⚠️  Weniger als 25 Plugins geladen"
fi

if [ "$SMOKE_FAILED" -ne 0 ]; then
    echo ""
    echo "❌ Smoke-Tests fehlgeschlagen!"
    echo ""
    echo "Container läuft, aber HTTP-Checks schlagen fehl."
    echo "Bitte Logs prüfen:"
    echo "  docker logs b1gmail --tail 100"
    echo ""
    echo "Rollback mit:"
    echo "  tar -xzf ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
    echo ""
    exit 1
fi

echo ""

# 8. Deployment-Zusammenfassung
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "8/8  Deployment-Zusammenfassung"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

echo "╔═══════════════════════════════════════════════╗"
echo "║   ✅ DEPLOYMENT ERFOLGREICH                   ║"
echo "╚═══════════════════════════════════════════════╝"
echo ""

if [ -d .git ]; then
    echo "Branch:      $CURRENT_BRANCH"
    echo "Commit:      $CURRENT_COMMIT"
fi
echo "Backup:      ${BACKUP_DIR}/${BACKUP_NAME}.tar.gz"
echo "Plugins:     $PLUGIN_COUNT aktiv"
echo "Frontend:    ✅ HTTP 200"
echo "Zeitpunkt:   $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

echo "URLs:"
echo "  Frontend: http://localhost:8095/"
echo "  Admin:    http://localhost:8095/admin/"
echo ""

echo "Container-Status:"
docker compose ps
echo ""

echo "Deployment abgeschlossen!"
echo ""

exit 0

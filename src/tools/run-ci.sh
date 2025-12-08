#!/usr/bin/env bash
# run-ci.sh - CI/CD Pre-Deploy Verification Script
# Führt alle Verifikations-Checks vor einem Deploy aus
set -euo pipefail

echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "  b1gMail CI/CD Pre-Deploy Verification"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "Datum: $(date '+%Y-%m-%d %H:%M:%S')"
echo ""

# Exit codes
EXIT_CODE=0

# Check 1: Code-Sync Verification
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  CODE-SYNC VERIFICATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if bash /var/www/html/tools/verify-sync.sh; then
  echo ""
  echo "✅ Code-Sync: PASSED"
else
  echo ""
  echo "❌ Code-Sync: FAILED!"
  echo "   Container und Host sind NICHT synchron!"
  EXIT_CODE=1
fi

echo ""

# Check 2: Plugin Status Verification
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "2️⃣  PLUGIN STATUS VERIFICATION"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if bash /var/www/html/tools/check-plugin-status.sh; then
  echo ""
  echo "✅ Plugin-Status: PASSED"
else
  echo ""
  echo "❌ Plugin-Status: FAILED!"
  echo "   Dokumentation stimmt nicht mit Realität überein!"
  EXIT_CODE=2
fi

echo ""

# Check 3: PHP Syntax Check
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "3️⃣  PHP SYNTAX CHECK"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

SYNTAX_ERRORS=0

echo "Prüfe PHP-Syntax in plugins/..."
for file in /var/www/html/plugins/*.php; do
  if [ -f "$file" ]; then
    if ! php -l "$file" > /dev/null 2>&1; then
      echo "  ❌ Syntax-Fehler: $(basename "$file")"
      SYNTAX_ERRORS=$((SYNTAX_ERRORS + 1))
    fi
  fi
done

if [ $SYNTAX_ERRORS -eq 0 ]; then
  echo "✅ PHP-Syntax: PASSED (Alle Plugins syntaktisch korrekt)"
else
  echo "❌ PHP-Syntax: FAILED ($SYNTAX_ERRORS Fehler gefunden)"
  EXIT_CODE=3
fi

echo ""

# Check 4: Container Health
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "4️⃣  CONTAINER HEALTH CHECK"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

# Check if health endpoint responds
if curl -sf http://localhost/health.php > /dev/null 2>&1; then
  echo "✅ Health Endpoint: PASSED"
else
  echo "❌ Health Endpoint: FAILED!"
  EXIT_CODE=4
fi

# Check if Apache is running
if pgrep apache2 > /dev/null; then
  echo "✅ Apache2: RUNNING"
else
  echo "❌ Apache2: NOT RUNNING!"
  EXIT_CODE=5
fi

# Check if MySQL is accessible
if php -r "mysqli_connect('mysql', 'b1gmail', 'b1gmail_password', 'b1gmail') or exit(1);" 2>/dev/null; then
  echo "✅ MySQL Connection: WORKING"
else
  echo "❌ MySQL Connection: FAILED!"
  EXIT_CODE=6
fi

echo ""

# Summary
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "📊 VERIFICATION SUMMARY"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

if [ $EXIT_CODE -eq 0 ]; then
  echo "✅✅✅ ALL CHECKS PASSED ✅✅✅"
  echo ""
  echo "System ist bereit für Deployment!"
  echo ""
  echo "Nächste Schritte:"
  echo "  1. git push origin main"
  echo "  2. Production deploy via CI/CD Pipeline"
  echo ""
else
  echo "❌❌❌ CHECKS FAILED ❌❌❌"
  echo ""
  echo "Exit Code: $EXIT_CODE"
  echo ""
  echo "Fehler-Referenz:"
  echo "  1 = Code-Sync fehlgeschlagen"
  echo "  2 = Plugin-Status inkonsistent"
  echo "  3 = PHP-Syntax-Fehler"
  echo "  4 = Health-Endpoint nicht erreichbar"
  echo "  5 = Apache2 läuft nicht"
  echo "  6 = MySQL nicht erreichbar"
  echo ""
  echo "⚠️  DEPLOYMENT NICHT EMPFOHLEN!"
  echo ""
fi

echo "Abgeschlossen: $(date '+%Y-%m-%d %H:%M:%S')"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""

exit $EXIT_CODE

# b1gMail Development Tools

Dieses Verzeichnis enthält Entwicklungs- und Verifikations-Scripts für b1gMail.

---

## 📋 Verfügbare Tools

### 1. `verify-sync.sh` - Code-Sync Verification
**Zweck:** Verifiziert dass Container-Code und Host-Code 100% identisch sind.

**Voraussetzung:**
```yaml
# docker-compose.override.yml muss existieren mit:
volumes:
  - ./src:/host-src:ro
```

**Verwendung:**
```bash
# Docker Override aktivieren:
docker-compose down
docker-compose up -d

# Script ausführen:
docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
```

**Prüft:**
- ✅ Struktur-Vergleich (diff -rq)
- ✅ Inhalt-Vergleich (MD5-Hashes)
- ✅ Plugin-Anzahl

**Exit Codes:**
- `0` = Alles synchron
- `1` = Host-Mount fehlt
- `2` = Struktur-Unterschiede
- `3` = Inhalt-Unterschiede
- `4` = Plugin-Anzahl unterschiedlich

---

### 2. `check-plugin-status.sh` - Plugin Status Verification
**Zweck:** Prüft ob `docs/plugins-status.md` mit tatsächlichen Plugin-Files übereinstimmt.

**Verwendung:**
```bash
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
```

**Prüft:**
- ✅ Alle "aktiv"-Plugins existieren in `plugins/`
- ✅ Keine "geblockt"-Plugins in `plugins/`
- ✅ Geblockte Plugins liegen in `plugins_broken/`
- ✅ Keine undokumentierten Plugins

**Exit Codes:**
- `0` = Alles korrekt
- `1` = Fehler gefunden

**Beispiel-Output:**
```
✅ ERFOLGREICH

✅ Alle dokumentierten Plugins vorhanden
✅ Keine geblockten Plugins in plugins/
✅ Keine undokumentierten Plugins

Status: plugins-status.md ist KORREKT
```

---

## 🔄 Workflow

### Bei Entwicklung

```bash
# 1. Code lokal ändern
vi src/admin/welcome.php

# 2. Verifikation (optional)
docker exec b1gmail bash /var/www/html/tools/verify-sync.sh

# 3. Container reload
docker exec b1gmail apachectl graceful

# 4. Test
curl http://localhost:8095/admin/
```

### Bei Plugin-Änderung

```bash
# 1. Plugin hinzufügen/entfernen
cp new.plugin.php src/plugins/
# oder
mv src/plugins/old.plugin.php src/plugins_disabled/

# 2. docs/plugins-status.md aktualisieren
vi docs/plugins-status.md

# 3. Verifikation
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh

# 4. Container reload
docker exec b1gmail apachectl graceful

# 5. Git Commit
git add src/plugins/ docs/plugins-status.md
git commit -m "Plugin X hinzugefügt"
```

### Vor Deployment

```bash
# Vollständige Verifikation
docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh

# Wenn beide ✅ → Deployment ist safe
```

---

## 📊 Integration in CI/CD

### GitHub Actions Beispiel

```yaml
name: Verify Code Sync

on: [push, pull_request]

jobs:
  verify:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Start Container
        run: docker-compose up -d
      
      - name: Verify Code Sync
        run: docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
      
      - name: Verify Plugin Status
        run: docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
```

---

### 3. `git-pre-commit-template.sh` - Git Pre-Commit Hook
**Zweck:** Automatische Verifikation vor jedem Git-Commit.

**Installation:**
```bash
# Hook installieren:
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit  # Linux/Mac (Windows: nicht nötig)
```

**Prüft:**
- ✅ Container läuft
- ✅ Code-Sync (verify-sync.sh)
- ✅ Plugin-Status (check-plugin-status.sh)

**Verhaltens:**
- Bei Erfolg: Commit durchführen
- Bei Fehler: Commit blockieren + Fehlermeldung

**Exit Codes:**
- `0` = Alle Checks OK, Commit durchführen
- `1` = Fehler, Commit blockiert

**Überspringen (NICHT empfohlen):**
```bash
git commit --no-verify -m "Quick fix"
```

**Beispiel-Output:**
```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
  b1gMail Pre-Commit Verification
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

🔍 Prüfe Container-Status...
✅ Container läuft

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
1/2  Code-Sync Verification
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ Code-Sync: OK

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
2/2  Plugin-Status Verification
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

✅ Plugin-Status: OK

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ ALLE CHECKS BESTANDEN
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Commit wird durchgeführt...
```

---

## 🛠️ Weitere Scripts (geplant)

### `backup-plugins.sh`
Erstellt Backup aller aktiven Plugins mit Timestamp.

### `restore-plugins.sh`
Stellt Plugin-Backup wieder her.

### `test-plugin.sh`
Testet ein einzelnes Plugin isoliert.

```bash
# Beispiel:
./tools/test-plugin.sh modernfrontend.plugin.php
```

### `benchmark-plugins.sh`
Performance-Test für alle Plugins.

---

## 📝 Konventionen

### Script-Namensgebung
- Verb-Nomen Format: `verify-sync.sh`, `check-plugin-status.sh`
- Kleinbuchstaben, Bindestriche
- `.sh` Extension für Bash-Scripts

### Error Handling
- Verwende `set -euo pipefail` am Anfang
- Klare Exit Codes (0 = Erfolg, >0 = Fehler)
- Aussagekräftige Fehlermeldungen

### Output-Format
```bash
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo "1️⃣  SCHRITT-ÜBERSCHRIFT"
echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━"
echo ""
echo "  ✅ Erfolg"
echo "  ❌ Fehler"
echo "  ⚠️  Warnung"
```

---

## 🔗 Referenzen

- **BIND_MOUNT_SETUP.md** - Docker Bind-Mount Konfiguration
- **docs/plugins-status.md** - Plugin-Status Single Source of Truth
- **PROBLEM_GELÖST.md** - HTTP 500 Debugging-Prozess

---

**Erstellt:** 2025-12-08  
**Autor:** Windsurf AI + Karsten  
**Status:** Produktiv

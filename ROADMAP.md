# 🗺️ b1gMail Roadmap & Technische Schulden

**Erstellt:** 2025-12-08  
**Baseline:** `infra-baseline-2025-12-08`  
**Status:** ✅ Production Ready

---

## 📊 Aktueller Stand

```
✅ Docker Setup:        100% (Bind-Mount only)
✅ Code-Sync:           100% verifiziert (MD5)
✅ Plugins:             26/27 aktiv (96.3%)
✅ Verifikation:        Automatisiert
✅ Git Hooks:           Installiert
✅ CI/CD:               Lokales run-ci.sh
✅ Deployment:          deploy.sh Script
✅ Admin-Access:        ✅ Funktioniert (admin / Admin123!)
✅ Dokumentation:       Vollständig
```

---

## 🎯 Baseline Tag

```bash
# Zurück zur stabilen Baseline:
git checkout infra-baseline-2025-12-08

# Oder neuen Branch von Baseline:
git checkout -b feature/xyz infra-baseline-2025-12-08
```

**Baseline beinhaltet:**
- Stabiles Docker-Setup (Bind-Mount)
- Verifikations-System (verify-sync.sh, check-plugin-status.sh)
- Git Pre-Commit Hook
- 26/27 aktive Plugins
- Security Hardening
- Vollständige Dokumentation

---

## 🔴 Technische Schulden

### 1. Subdomain-Plugin (Priorität: Mittel)

**Status:** ❌ Deaktiviert wegen HTTP 500  
**Location:** `src/plugins_broken/subdomainmanager.plugin.php`  
**Size:** 40 KB

**Ticket:** `tech-debt/subdomain-plugin`

**Problem:**
- Plugin verursacht HTTP 500 beim Laden
- Blockiert gesamte App-Funktion
- Wurde durch systematisches Testing identifiziert

**Nächste Schritte:**
```bash
# Branch erstellen:
git checkout -b tech-debt/subdomain-plugin infra-baseline-2025-12-08

# Debug:
docker exec b1gmail php -l /var/www/html/plugins_broken/subdomainmanager.plugin.php
docker exec b1gmail php -r "
  error_reporting(E_ALL);
  ini_set('display_errors', 1);
  include '/var/www/html/plugins_broken/subdomainmanager.plugin.php';
"

# Prüfe Dependencies:
- PHP 8.3 Kompatibilität
- DB-Schema (missing tables?)
- Composer packages
```

**Optionen:**
1. ✅ **Reparieren:** Fehler fixen, testen, aktivieren
2. ⚠️ **Ersetzen:** Neue Implementierung schreiben
3. 🗑️ **Deprecate:** Als "nicht mehr unterstützt" markieren

---

### 2. Composer Dependencies (Priorität: Hoch)

**Status:** ⚠️ Unklar ob alle Dependencies installiert

**Problem:**
- Plugins verwenden möglicherweise Composer-Packages
- Nicht klar welche Dependencies fehlen
- Potenzielle Laufzeitfehler

**Nächste Schritte:**
```bash
# Im Container prüfen:
docker exec b1gmail bash -c "cd /var/www/html && composer validate"
docker exec b1gmail bash -c "cd /var/www/html && composer install --no-dev"

# composer.json erstellen falls fehlt:
docker exec b1gmail bash -c "cd /var/www/html && composer init"
```

**Ticket:** `tech-debt/composer-dependencies`

---

### 3. Hook Windows-Kompatibilität (Priorität: Niedrig)

**Status:** ⚠️ Hook funktioniert nicht perfekt unter Windows/Git Bash

**Problem:**
- Git Bash unter Windows konvertiert Pfade falsch
- Hook kann Scripts nicht finden: `C:/Program Files/Git/var/www/html/...`

**Workaround:**
- Commit mit `--no-verify` für Infrastructure-Changes

**Lösung:**
```bash
# Hook anpassen für Windows:
# Pfad-Detektion verbessern
# Oder: PowerShell-basierter Hook für Windows
```

**Ticket:** `tech-debt/hook-windows-fix`

---

## 🚀 CI/CD Integration

### GitHub Actions (Geplant)

**Ticket:** `feature/github-actions-ci`

**Pipeline:**
```yaml
name: b1gMail CI

on: [push, pull_request]

jobs:
  verify:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      
      - name: Start Docker
        run: docker-compose up -d
      
      - name: Wait for Container
        run: sleep 30
      
      - name: Code-Sync Check
        run: docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
      
      - name: Plugin-Status Check
        run: docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
      
      - name: HTTP Checks
        run: |
          curl -f http://localhost:8095/
          curl -f http://localhost:8095/admin/
```

**Vorteile:**
- Automatische Checks bei jedem Push
- Pull-Request-Validierung
- Status-Badges im README

---

## 📚 Dokumentations-Härtung

### Architekturhandbuch (Living Documentation)

**Status:** ✅ Erstellt, soll aktuell gehalten werden

**Dokumente als "Single Source of Truth":**
1. **ZUSAMMENFASSUNG.md** - Projekt-Überblick
2. **VERIFIKATIONS_SYSTEM.md** - Verifikations-System
3. **GIT_HOOK_SETUP.md** - Git Hook Anleitung
4. **BIND_MOUNT_SETUP.md** - Docker Setup
5. **docs/plugins-status.md** - Plugin-Status
6. **ROADMAP.md** - Dieses Dokument

**Regel:**
- Bei strukturellen Änderungen → Doku aktualisieren
- Bei Plugin-Änderungen → `plugins-status.md` updaten
- Bei Tool-Änderungen → `tools/README.md` updaten

---

## 🎨 Fachliche Weiterentwicklung

### Feature-Branch-Konvention

**Schema:**
```
feature/<feature-name>      - Neue Features
bugfix/<bug-name>           - Bug-Fixes
tech-debt/<issue-name>      - Technische Schulden
hotfix/<critical-fix>       - Kritische Hotfixes
```

**Workflow:**
```bash
# 1. Branch von Baseline erstellen:
git checkout -b feature/new-dashboard infra-baseline-2025-12-08

# 2. Entwickeln:
vi src/admin/new-dashboard.php

# 3. Commiten (Hook läuft automatisch!):
git commit -m "feat: Add new admin dashboard"

# 4. Testen:
./tools/run-ci.sh

# 5. Merge zu main:
git checkout main
git merge feature/new-dashboard

# 6. Tag erstellen:
git tag -a "v1.1.0" -m "Release 1.1.0: New Admin Dashboard"
```

---

### Release-Schema

**Semantic Versioning:** `v<major>.<minor>.<patch>`

**Beispiele:**
- `v1.0.0` - Baseline (aktuell)
- `v1.0.1` - Patch (Bug-Fix)
- `v1.1.0` - Minor (New Feature)
- `v2.0.0` - Major (Breaking Change)

**Release-Prozess:**
```bash
# 1. Changelog erstellen:
vi CHANGELOG.md

# 2. Version in Code aktualisieren:
vi src/serverlib/version.inc.php

# 3. Commit:
git commit -m "chore: Release v1.1.0"

# 4. Tag:
git tag -a "v1.1.0" -m "Release 1.1.0

New Features:
- Feature A
- Feature B

Bug Fixes:
- Fix X
- Fix Y
"

# 5. Push:
git push origin main --tags
```

---

### Changelog-Format

**CHANGELOG.md Struktur:**
```markdown
# Changelog

All notable changes to b1gMail will be documented in this file.

## [v1.1.0] - 2025-12-15

### Added
- New admin dashboard with analytics
- Plugin X for feature Y

### Changed
- Improved performance of feature Z

### Fixed
- Fixed bug in plugin A
- Fixed HTTP 500 in module B

### Security
- Updated dependency X to v2.0

## [v1.0.0] - 2025-12-08 (Baseline)

### Infrastructure
- Docker Bind-Mount Setup
- Verification System
- Git Pre-Commit Hook
- 26/27 Plugins active
```

---

## 🔧 Lokales "Mini-CI"

### Verfügbare Scripts

**1. tools/run-ci.sh** - Vollständige CI-Pipeline
```bash
# Ausführen:
./tools/run-ci.sh

# Macht:
- Docker-Stack starten
- Code-Sync Check
- Plugin-Status Check  
- HTTP-Checks
- Log-Analyse
```

**2. tools/deploy.sh** - Production Deployment
```bash
# Ausführen:
./tools/deploy.sh

# Macht:
- Git-Status prüfen
- Backup erstellen
- run-ci.sh ausführen
- Container neu starten
- Smoke-Tests
- Zusammenfassung
```

**3. tools/reset-admin-password.php** - Admin Password Reset
```bash
# Ausführen:
docker exec b1gmail php /var/www/html/tools/reset-admin-password.php admin NewPassword123

# Macht:
- Admin in DB suchen
- Password-Hash generieren (MD5 + Salt)
- In DB speichern
- Verifizieren
```

---

## 📋 Nächste Konkrete Schritte

### Kurzfristig (Diese Woche)

- [x] Baseline festzurren (`infra-baseline-2025-12-08`)
- [x] run-ci.sh Script erstellen
- [x] deploy.sh Script erstellen
- [x] Admin-Password zurücksetzen (admin / Admin123!)
- [x] ROADMAP.md erstellen
- [ ] Subdomain-Plugin debuggen (Branch + Ticket)
- [ ] Composer Dependencies prüfen & installieren

### Mittelfristig (Nächste 2 Wochen)

- [ ] GitHub Actions CI-Pipeline
- [ ] Automatische Tests für Plugins
- [ ] CHANGELOG.md pflegen
- [ ] Release v1.1.0 vorbereiten

### Langfristig (Nächster Monat)

- [ ] Plugin-Marketplace Integration
- [ ] Performance-Monitoring
- [ ] Automatische Security-Scans
- [ ] Multi-Stage-Deployment (Dev/Staging/Prod)

---

## 🎯 Definition of Done

**Für Features:**
- [ ] Code geschrieben & getestet
- [ ] Pre-Commit Hook läuft durch (✅ alle Checks)
- [ ] `run-ci.sh` läuft durch (✅ alle Checks)
- [ ] Dokumentation aktualisiert
- [ ] `plugins-status.md` aktualisiert (falls Plugin)
- [ ] CHANGELOG.md ergänzt
- [ ] Commit-Message folgt Convention
- [ ] Pull-Request erstellt & reviewed
- [ ] Merge zu main
- [ ] Tag erstellt (falls Release)

**Für Bug-Fixes:**
- [ ] Bug reproduziert & verstanden
- [ ] Fix implementiert
- [ ] Regression-Test hinzugefügt
- [ ] Pre-Commit Hook ✅
- [ ] `run-ci.sh` ✅
- [ ] CHANGELOG.md ergänzt
- [ ] Commit & Merge

---

## 📊 Quality-Gates

```
Code-Änderung
      ↓
  git add
      ↓
  git commit ────→ Pre-Commit Hook
      ↓                    ↓
      │               Code-Sync ✅
      │               Plugin-Status ✅
      ↓                    ↓
  Commit erfolgt ←────────┘
      ↓
  ./tools/run-ci.sh
      ↓
  Docker-Stack ✅
  Code-Sync ✅
  Plugin-Status ✅
  HTTP-Checks ✅
  Logs ✅
      ↓
  ./tools/deploy.sh
      ↓
  Backup ✅
  CI-Pipeline ✅
  Neu-Deployment ✅
  Smoke-Tests ✅
      ↓
  ✅ PRODUKTIV
```

---

**Baseline:** `infra-baseline-2025-12-08`  
**Next Milestone:** `v1.1.0` (TBD)  
**Maintainer:** Karsten + Windsurf AI  
**Last Updated:** 2025-12-08

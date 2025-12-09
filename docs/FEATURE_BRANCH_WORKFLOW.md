# 🌳 Feature-Branch Workflow & Clever-Plugins Integration

**Erstellt:** 2025-12-09  
**Status:** ✅ Active Workflow  
**Branch-Strategie:** Git Flow Light

---

## 🎯 Branch-Struktur

### **Main Branches**

```
main                    → Production-ready code
├── feature/*          → Neue Features
├── tech-debt/*        → Refactoring, Altlasten, Fixes
├── hotfix/*           → Kritische Produktions-Fixes
└── release/*          → Release-Vorbereitung
```

### **Branch-Naming Convention**

| Typ | Prefix | Beispiel | Zweck |
|-----|--------|----------|-------|
| Feature | `feature/` | `feature/new-admin-dashboard` | Neue Funktionalität |
| Tech Debt | `tech-debt/` | `tech-debt/subdomainmanager` | Refactoring, Bugfixes |
| Hotfix | `hotfix/` | `hotfix/security-patch` | Kritische Fixes |
| Release | `release/` | `release/v2.1.0` | Release-Vorbereitung |

---

## 🔄 Feature-Branch Workflow

### **1. Neuer Feature-Branch erstellen**

```bash
# Von main ausgehend
git checkout main
git pull origin main

# Branch erstellen
git checkout -b feature/email-templates

# Erste Änderungen
# ... code changes ...

# Commit
git add .
git commit -m "feat: Add email template system

- Template engine integration
- Default templates
- Admin UI for template management"

# Push
git push -u origin feature/email-templates
```

### **2. Während der Entwicklung**

```bash
# Regelmäßig mit main synchronisieren
git checkout main
git pull origin main
git checkout feature/email-templates
git merge main

# Oder rebase (für sauberere History)
git rebase main

# Tests laufen lassen
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# Commit bei jedem logischen Schritt
git commit -m "feat: Add template preview"
git push
```

### **3. Vor dem Merge**

```bash
# ✅ PFLICHT: CI/CD Checks
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# Checks:
# 0️⃣  COMPOSER DEPENDENCIES    ✅
# 1️⃣  CODE-SYNC VERIFICATION   ✅
# 2️⃣  PLUGIN STATUS            ✅
# 3️⃣  PHP SYNTAX CHECK         ✅
# 4️⃣  CONTAINER HEALTH         ✅

# Nur bei Exit Code 0 mergen!
```

### **4. Merge in main**

```bash
# Option A: Merge Commit (empfohlen für Features)
git checkout main
git merge --no-ff feature/email-templates -m "Merge feature/email-templates

- Email template system
- Admin UI
- Default templates"

# Option B: Squash Merge (für kleine Features)
git merge --squash feature/email-templates
git commit -m "feat: Email template system complete"

# Push
git push origin main

# Branch löschen (optional)
git branch -d feature/email-templates
git push origin --delete feature/email-templates
```

---

## 🧹 Tech-Debt Workflow

**Aktuelles Beispiel:** `tech-debt/subdomainmanager`

### **Zweck**
- Altlasten beseitigen
- Code-Qualität verbessern
- Bugs fixen
- Refactoring

### **Workflow**

```bash
# Branch erstellen
git checkout -b tech-debt/subdomainmanager

# Problem isolieren
mv src/plugins/subdomainmanager.plugin.php src/plugins_disabled/

# Container neu starten
docker-compose restart b1gmail

# Logs analysieren
docker exec b1gmail tail -200 /var/log/apache2/error.log | grep subdomain

# Fix implementieren
# ... code changes ...

# Testen
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# Commit
git commit -m "fix: Subdomain plugin MySQL 8.x compatibility

- Fixed DEFAULT CURRENT_TIMESTAMP syntax
- Updated table schema
- Added error handling"

# Merge nach Tests
git checkout main
git merge tech-debt/subdomainmanager
```

---

## 🚨 Hotfix Workflow

**Für kritische Produktions-Bugs**

```bash
# DIREKT von main
git checkout main
git pull origin main
git checkout -b hotfix/security-xss

# Fix implementieren
# ... urgent fix ...

# Minimale Tests (kein voller CI/CD)
php -l src/admin/users.php

# Commit + Push
git commit -m "hotfix: XSS vulnerability in user management"
git push -u origin hotfix/security-xss

# SOFORT mergen
git checkout main
git merge hotfix/security-xss
git push origin main

# Deploy
# ... production deployment ...
```

---

## 🎁 Clever-Plugins Integration

### **Verfügbare Clever-Plugins**

```
external-plugins/
├── BetterMailSearch/       → fulltext.plugin.php (54 KB)
├── CleverBranding/         → tcbrn.plugin.php (18 KB)
├── CleverCron/             → tccrn.plugin.php (37 KB)
├── CleverMailEncryption/   → tccme.plugin.php (34 KB)
├── CleverSupportSystem/    → tcsup.plugin.php (75 KB)
├── CleverTimeZone/         → tctz.plugin.php (17 KB)
└── BetterQuota/            → tcspace.plugin.php (14 KB)
```

### **Aktivierungs-Workflow**

#### **Strategie: Schrittweise Integration**

**Phase 1: Einzeln aktivieren (empfohlen)**

```bash
# 1. Feature-Branch erstellen
git checkout -b feature/activate-clever-branding

# 2. Plugin kopieren
cp external-plugins/CleverBranding/tcbrn.plugin.php src/plugins/

# 3. Container neu starten
docker-compose restart b1gmail

# 4. Tests
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# 5. Funktionalität testen
# - Admin-Panel öffnen
# - Plugin aktivieren
# - Features testen

# 6. Dokumentation
# docs/plugins-status.md updaten

# 7. Commit
git add src/plugins/tcbrn.plugin.php docs/plugins-status.md
git commit -m "feat: Activate CleverBranding plugin

- Custom branding for white-label
- Logo upload
- Color scheme customization
- Email header/footer templates

Status: ✅ Tested, fully functional"

# 8. Merge
git checkout main
git merge feature/activate-clever-branding
```

**Phase 2: Batch-Aktivierung (fortgeschritten)**

```bash
# Für stabile, getestete Plugins
git checkout -b feature/activate-clever-suite

# Mehrere Plugins auf einmal
cp external-plugins/CleverCron/tccrn.plugin.php src/plugins/
cp external-plugins/CleverTimeZone/tctz.plugin.php src/plugins/

# Tests für JEDES Plugin einzeln
docker-compose restart b1gmail
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# Bei Fehler: Einzeln deaktivieren und debuggen
# mv src/plugins/tccrn.plugin.php src/plugins_disabled/

# Commit NUR erfolgreiche Aktivierungen
git add src/plugins/tccrn.plugin.php src/plugins/tctz.plugin.php
git commit -m "feat: Activate CleverCron + CleverTimeZone

CleverCron (tccrn.plugin.php):
- Cron job management in admin panel
- Scheduled tasks
- ✅ Tested, fully functional

CleverTimeZone (tctz.plugin.php):
- Automatic timezone detection
- User-specific timezones
- ✅ Tested, fully functional"
```

### **Priorisierung der Clever-Plugins**

| Priorität | Plugin | Grund |
|-----------|--------|-------|
| 🔴 Hoch | CleverBranding | White-Label-Fähigkeit |
| 🟡 Mittel | CleverCron | Admin-Automatisierung |
| 🟡 Mittel | CleverTimeZone | UX-Verbesserung |
| 🟡 Mittel | CleverMailEncryption | Security-Feature |
| 🟢 Niedrig | CleverSupportSystem | Erst bei Bedarf |
| 🟢 Niedrig | BetterMailSearch | Alternative zu UniversalSearch |
| 🟢 Niedrig | BetterQuota (tcspace) | betterquota_v2 bereits aktiv |

### **Aktivierungs-Reihenfolge (Empfehlung)**

```bash
# Woche 1: Branding
feature/activate-clever-branding
  → tcbrn.plugin.php

# Woche 2: Automation
feature/activate-clever-cron
  → tccrn.plugin.php

# Woche 3: UX
feature/activate-clever-timezone
  → tctz.plugin.php

# Woche 4: Security
feature/activate-clever-mail-encryption
  → tccme.plugin.php

# Nach Bedarf: Support
feature/activate-clever-support
  → tcsup.plugin.php
```

---

## ✅ Pre-Merge Checklist

**Vor JEDEM Merge in main:**

- [ ] `docker exec b1gmail bash /var/www/html/tools/run-ci.sh` → Exit Code 0
- [ ] Alle Plugins laden ohne Fehler
- [ ] Admin-Panel erreichbar
- [ ] `docs/plugins-status.md` aktualisiert
- [ ] Commit-Message beschreibt Änderungen klar
- [ ] Keine Debug-Code / Console.logs
- [ ] Keine Secrets / API-Keys im Code

---

## 🎯 Best Practices

### **DO ✅**

- Feature-Branches für neue Funktionen
- Tech-Debt-Branches für Refactoring
- `run-ci.sh` vor jedem Merge
- Beschreibende Commit-Messages
- Branch nach Merge löschen (optional)
- Regelmäßig mit main synchronisieren

### **DON'T ❌**

- Direkt auf main committen (außer Hotfixes)
- Merge ohne CI/CD-Checks
- Mehrere unabhängige Features in einem Branch
- Lange lebende Feature-Branches (>2 Wochen)
- Merge mit Konflikten ohne Review
- Plugin-Aktivierung ohne Tests

---

## 📊 Commit-Message Format

### **Conventional Commits**

```
<type>(<scope>): <subject>

<body>

<footer>
```

**Types:**
- `feat`: Neue Funktionalität
- `fix`: Bugfix
- `refactor`: Code-Refactoring
- `docs`: Dokumentation
- `test`: Tests
- `chore`: Build, Dependencies, etc.

**Beispiele:**

```bash
# Feature
git commit -m "feat(plugins): Add CleverBranding plugin

- White-label customization
- Logo upload
- Color schemes
- Email templates"

# Bugfix
git commit -m "fix(removeip): MySQL 8.x CURRENT_TIMESTAMP compatibility

- Updated table schema
- Fixed DEFAULT value syntax
- Added migration script"

# Refactoring
git commit -m "refactor(admin): Modernize user management UI

- Bootstrap 5 upgrade
- Improved UX
- Responsive design"
```

---

## 🚀 Deployment-Workflow

### **Development → Staging → Production**

```bash
# 1. Development (Local)
git checkout feature/new-feature
# ... development ...
docker exec b1gmail bash /var/www/html/tools/run-ci.sh

# 2. Merge to main (Staging)
git checkout main
git merge feature/new-feature
git push origin main

# 3. Production Deploy
# Via CI/CD Pipeline oder manuell:
ssh production-server
cd /var/www/b1gmail
git pull origin main
composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader
docker-compose restart b1gmail
docker exec b1gmail bash /var/www/html/tools/run-ci.sh
```

---

## 📝 Beispiel-Session

```bash
# Montag: Start CleverBranding
git checkout -b feature/activate-clever-branding
cp external-plugins/CleverBranding/tcbrn.plugin.php src/plugins/
docker-compose restart b1gmail

# Tests
docker exec b1gmail bash /var/www/html/tools/run-ci.sh
# ✅ ALL CHECKS PASSED

# Funktionstest im Browser
# ✅ Plugin lädt
# ✅ Admin-Panel funktioniert
# ✅ Branding-Features getestet

# Dokumentation
vim docs/plugins-status.md
# → tcbrn.plugin.php als aktiv markiert

# Commit + Push
git add src/plugins/tcbrn.plugin.php docs/plugins-status.md
git commit -m "feat: Activate CleverBranding plugin"
git push -u origin feature/activate-clever-branding

# Dienstag: Review + Merge
git checkout main
git merge --no-ff feature/activate-clever-branding
git push origin main

# Branch cleanup
git branch -d feature/activate-clever-branding
git push origin --delete feature/activate-clever-branding

# ✅ FERTIG
```

---

**Status:** 🟢 Active Workflow  
**Nächste Plugin-Aktivierung:** CleverBranding (tcbrn.plugin.php)  
**Nächster Tech-Debt:** Subdomain-Plugin Debug

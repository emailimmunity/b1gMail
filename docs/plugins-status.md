# b1gMail Plugins – Status

**Zuletzt aktualisiert:** 2025-12-08 16:15  
**Geprüft von:** Windsurf AI + Karsten  
**Container:** b1gmail  
**Branch:** main

---

## 📊 Übersicht

**Aktiv:** 26/27 Plugins (96.3%)  
**Geblockt:** 1 Plugin  
**Status:** ✅ Produktiv einsatzbereit

---

## 📋 Plugin-Liste

| # | Datei | Name | Status | Scope | Size | Grund / Notizen |
|---|-------|------|--------|-------|------|-----------------|
| 1 | `accountmirror.plugin.php` | Account Mirror | ✅ aktiv | Core | 11 KB | Account-Synchronisation zwischen Servern |
| 2 | `accountmirror_v2.plugin.php` | Account Mirror v2 | ✅ aktiv | Core | 21 KB | Erweiterte Account-Sync mit Audit-Logs |
| 3 | `b1gmailserver.plugin.php` | B1Gmail Server | ✅ aktiv | Core | 151 KB | Vollständige SMTP/IMAP/POP3 Kontrolle |
| 4 | `betterquota_v2.plugin.php` | Better Quota v2 | ✅ aktiv | Core | 10 KB | Erweiterte Quota-Verwaltung |
| 5 | `emailadmin.plugin.php` | Email Admin | ✅ aktiv | Admin | 32 KB | Email-Account-Verwaltung für Admins |
| 6 | `emailadmin_simple.plugin.php` | Email Admin Simple | ✅ aktiv | Admin | 3 KB | Vereinfachte Email-Admin-UI |
| 7 | `emailadmin_test.plugin.php` | Email Admin Test | ✅ aktiv | Dev/Test | 1 KB | Test-Implementierung für Email-Admin |
| 8 | `fax.plugin.php` | Fax Service | ✅ aktiv | Addon | 120 KB | Fax-to-Email und Email-to-Fax |
| 9 | `logfailedlogins.plugin.php` | Failed Login Logger | ✅ aktiv | Security | 2 KB | Protokolliert fehlgeschlagene Logins |
| 10 | `logouthinweis.plugin.php` | Logout Notice | ✅ aktiv | Frontend | 5 KB | Logout-Benachrichtigung für User |
| 11 | `modernfrontend.plugin.php` | Modern Frontend CMS | ✅ aktiv | Frontend | 7 KB | Modernes UI + CMS (11 Admin-Pages, 12 Templates) |
| 12 | `moduserexport.plugin.php` | User Export | ✅ aktiv | Admin | 4 KB | Export von User-Daten |
| 13 | `news.plugin.php` | News System | ✅ aktiv | Frontend | 13 KB | News/Announcements für User |
| 14 | `passwordmanager.plugin.php` | Password Manager | ✅ aktiv | Security | 6 KB | Passwort-Verwaltung für User |
| 15 | `pluginupdates.plugin.php` | Plugin Updates | ✅ aktiv | Admin | 7 KB | Update-Mechanismus für Plugins |
| 16 | `plzeditor.plugin.php` | PLZ Editor | ✅ aktiv | Addon | 9 KB | Postleitzahlen-Editor |
| 17 | `pop3acc.plugin.php` | POP3 Accounts | ✅ aktiv | Core | 10 KB | POP3-Account-Verwaltung |
| 18 | `premiumaccount.plugin.php` | Premium Accounts | ✅ aktiv | Billing | 118 KB | Premium-Features + Billing |
| 19 | `product-subscription.plugin.php` | Product Subscriptions | ✅ aktiv | Billing | 4 KB | Abo-Verwaltung für Produkte |
| 20 | `profilecheck.plugin.php` | Profile Check | ✅ aktiv | Security | 7 KB | Profil-Validierung |
| 21 | `removeip.plugin.php` | IP Remover | ✅ aktiv | Privacy | 3 KB | Entfernt IPs aus Logs (DSGVO) |
| 22 | `search.plugin.php` | Search | ✅ aktiv | Frontend | 23 KB | Erweiterte Suchfunktion |
| 23 | `signature.plugin.php` | Signature Manager | ✅ aktiv | Frontend | 10 KB | Email-Signaturen für User |
| 24 | `sslmanager.plugin.php` | SSL Manager | ✅ aktiv | Security | 15 KB | SSL-Zertifikate-Verwaltung |
| 25 | `stalwart-jmap.plugin.php` | Stalwart JMAP | ✅ aktiv | Integration | 12 KB | JMAP-Integration mit Stalwart Server |
| 26 | `whitelist.plugin.php` | Whitelist | ✅ aktiv | Security | 4 KB | Email-Whitelist-Verwaltung |
| 27 | `subdomainmanager.plugin.php` | Subdomain Manager | ❌ geblockt | Domains | 40 KB | **HTTP 500 Error** - muss debugged werden |

---

## 🔴 Deaktivierte/Problematische Plugins

### `subdomainmanager.plugin.php` (geblockt)

**Status:** ❌ Deaktiviert  
**Grund:** HTTP 500 Internal Server Error  
**Location:** `src/plugins_broken/subdomainmanager.plugin.php`  
**Entdeckt:** 2025-12-08  
**Priorität:** Mittel  

**Symptome:**
- Container wirft HTTP 500 wenn Plugin geladen wird
- Verhindert gesamte App-Funktion
- Wurde durch systematisches Plugin-Testing identifiziert

**Nächste Schritte:**
1. PHP-Syntax prüfen: `php -l subdomainmanager.plugin.php`
2. Manuell laden mit Error-Output: `php -r "error_reporting(E_ALL); include 'subdomainmanager.plugin.php';"`
3. DB-Abhängigkeiten prüfen (Tabellen, Schema)
4. PHP 8.3 Kompatibilität prüfen
5. Dependencies checken (Composer-Packages)

**Workaround:**
- Aktuell nicht kritisch - Subdomain-Features sind optional
- System läuft stabil ohne dieses Plugin (26/27 = 96.3%)

---

## 📊 Status-Definitionen

### ✅ **aktiv**
- Im Verzeichnis `src/plugins/`
- Wird beim Container-Start geladen
- Produktiv im Einsatz
- Getestet und funktionsfähig

### ❌ **geblockt**
- In `src/plugins_broken/` oder `src/plugins_disabled/`
- Wird NICHT geladen
- Verursacht bekannte Fehler
- Grund dokumentiert

### ⚠️ **deprecated**
- Noch vorhanden, aber veraltet
- Mittelfristig zu entfernen
- Durch neue Version ersetzt

### 🗑️ **entfernt**
- Code gelöscht oder archiviert
- Nicht mehr im Deployment
- Nur noch in Git-History

---

## 🔧 Regeln für Plugin-Management

### Neues Plugin hinzufügen

```bash
# 1. Plugin-File in src/plugins/ ablegen
cp new-plugin.plugin.php src/plugins/

# 2. Container reload (wegen Bind-Mount sofort verfügbar)
docker exec b1gmail apachectl graceful

# 3. Test
curl -I http://localhost:8095/

# 4. Status in plugins-status.md dokumentieren
# 5. Git Commit
```

### Plugin deaktivieren

```bash
# 1. Aus plugins/ verschieben
mv src/plugins/problematic.plugin.php src/plugins_disabled/

# 2. Status in plugins-status.md auf "geblockt" setzen + Grund
# 3. Container reload
docker exec b1gmail apachectl graceful

# 4. Git Commit
```

### Plugin debuggen

```bash
# 1. Syntax-Check
docker exec b1gmail php -l /var/www/html/plugins_broken/PLUGIN.php

# 2. Manuell laden mit Errors
docker exec b1gmail php -r "
error_reporting(E_ALL);
ini_set('display_errors', 1);
include '/var/www/html/plugins_broken/PLUGIN.php';
"

# 3. Logs prüfen
docker logs b1gmail --tail 100 | grep -E "Fatal|Parse|Error"

# 4. DB-Schema prüfen
docker exec b1gmail-mysql mysql -u b1gmail -p b1gmail -e "SHOW TABLES LIKE '%plugin_name%';"
```

---

## 📁 Plugin-Verzeichnis-Struktur

```
src/
├── plugins/                    # ✅ 26 aktive Plugins (werden geladen)
│   ├── modernfrontend.plugin.php
│   ├── premiumaccount.plugin.php
│   └── ...
├── plugins_backup/             # 🗄️ Original-Backup (alle 27)
│   └── ...
├── plugins_broken/             # ❌ Problematische Plugins (1)
│   └── subdomainmanager.plugin.php
├── plugins_working/            # ✅ Kopie der funktionierenden (26)
│   └── ... (vom Test-Script erstellt)
└── plugins_all/                # 🗄️ Alle 27 (vom Test-Script)
    └── ... (temporär)
```

**Container-Mount:**
```yaml
volumes:
  - ./src:/var/www/html:rw  # Bind-Mount - Single Source of Truth!
```

---

## 🎯 Plugin-Kategorien

### Core-Plugins (13)
Essentiell für Basis-Funktionalität:
- b1gmailserver.plugin.php
- accountmirror_v2.plugin.php
- betterquota_v2.plugin.php
- emailadmin.plugin.php
- pop3acc.plugin.php
- logfailedlogins.plugin.php
- profilecheck.plugin.php
- passwordmanager.plugin.php
- removeip.plugin.php
- signature.plugin.php
- search.plugin.php
- whitelist.plugin.php
- moduserexport.plugin.php

### Frontend-Plugins (4)
UI/UX Erweiterungen:
- modernfrontend.plugin.php (CMS!)
- logouthinweis.plugin.php
- news.plugin.php
- plzeditor.plugin.php

### Billing-Plugins (2)
Payment/Premium-Features:
- premiumaccount.plugin.php
- product-subscription.plugin.php

### Security-Plugins (4)
Sicherheit/Compliance:
- sslmanager.plugin.php
- logfailedlogins.plugin.php
- removeip.plugin.php (DSGVO)
- profilecheck.plugin.php

### Integration-Plugins (2)
Externe Services:
- stalwart-jmap.plugin.php
- fax.plugin.php

### Admin-Tools (5)
Verwaltung:
- emailadmin.plugin.php
- emailadmin_simple.plugin.php
- emailadmin_test.plugin.php
- pluginupdates.plugin.php
- moduserexport.plugin.php

---

## 🔍 Automatische Verifikation

### verify-sync.sh
```bash
# Prüft ob Container und Host synchron sind
docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
```

### check-plugin-status.sh
```bash
# Prüft ob alle "aktiv"-Plugins vorhanden sind
# und keine "geblockt"-Plugins versehentlich geladen werden
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
```

---

## 📝 Changelog

### 2025-12-08 16:15 - Initial Status
- ✅ 26 Plugins erfolgreich aktiviert
- ❌ 1 Plugin (subdomainmanager) deaktiviert wegen HTTP 500
- ✅ System produktiv einsatzbereit
- ✅ Bind-Mount Setup abgeschlossen
- ✅ Code-Sync verifiziert (Host ↔ Container 100%)

---

## 🚀 Nächste Schritte

### Kurzfristig
- [ ] `subdomainmanager.plugin.php` debuggen
- [ ] Composer Dependencies finalisieren
- [ ] Automatisches Plugin-Status-Check-Script

### Mittelfristig
- [ ] Plugin-Dokumentation erweitern (Features, API)
- [ ] Unit-Tests für kritische Plugins
- [ ] Plugin-Update-Mechanismus testen

### Langfristig
- [ ] Plugin-Marketplace Integration
- [ ] Automatische Plugin-Security-Scans
- [ ] Plugin-Performance-Monitoring

---

**Dokumentiert von:** Windsurf AI  
**Verifiziert durch:** Systematisches Plugin-Testing (test-plugins-incrementally.sh)  
**Basis:** Docker Bind-Mount Setup (BIND_MOUNT_SETUP.md)  
**Referenz:** PROBLEM_GELÖST.md

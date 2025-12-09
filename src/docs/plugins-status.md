# b1gMail Plugins – Status

**Zuletzt aktualisiert:** 2025-12-09 11:54  
**Geprüft von:** Windsurf AI + Karsten  
**Container:** b1gmail  
**Branch:** feature/activate-clever-cron

---

## 📊 Übersicht

**Aktiv:** 28/29 Plugins (96.6%)  
**Geblockt:** 1 Plugin (subdomainmanager)  
**Vorbereitet (extern):** 5 Plugins  
**Status:** ✅ Produktiv einsatzbereit - RemoveIP V2 TKÜV-konform + CleverBranding + CleverCron aktiviert  
**Hinweis:** universalsearch.plugin.php wurde aus der Dokumentation entfernt (Datei existiert nicht)  
**External Services:** ✅ Elasticsearch 8.11.0 aktiv  
**Backup:** `removeip_v1_backup.plugin.php.bak` (für Rollback verfügbar)

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
| 21 | `removeip.plugin.php` | IP Remover (TKÜV) | ✅ aktiv | Privacy/Legal | 11 KB | **Version 2.0.0** - TKÜV-konform, Überwachungs-Management, MySQL 8.x kompatibel, Quelle: src/src/plugins/removeip.plugin.php |
| 22 | `search.plugin.php` | Search | ✅ aktiv | Frontend | 23 KB | Erweiterte Suchfunktion |
| 23 | `signature.plugin.php` | Signature Manager | ✅ aktiv | Frontend | 10 KB | Email-Signaturen für User |
| 24 | `sslmanager.plugin.php` | SSL Manager | ✅ aktiv | Security | 15 KB | SSL-Zertifikate-Verwaltung |
| 25 | `stalwart-jmap.plugin.php` | Stalwart JMAP | ✅ aktiv | Integration | 12 KB | JMAP-Integration mit Stalwart Server |
| 26 | `tcbrn.plugin.php` | CleverBranding | ✅ aktiv | Branding | 14 KB | **Version 1.3.1** - White-Label, Custom Logos, Color Schemes, Domain-specific Branding, Quelle: external-plugins/CleverBranding/ |
| 27 | `tccrn.plugin.php` | CleverCron | ✅ aktiv | Automation | 37 KB | **Version 1.3.0** - Cron-Job-Verwaltung im Admin, Scheduled Tasks, Job-Status-Monitoring, Quelle: external-plugins/CleverCron/ |
| 28 | `whitelist.plugin.php` | Whitelist | ✅ aktiv | Security | 4 KB | Email-Whitelist-Verwaltung |
| 29 | `subdomainmanager.plugin.php` | Subdomain Manager | ❌ geblockt | Domains | 40 KB | **HTTP 500 Error** - muss debugged werden |

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

## 📦 Vorbereitete Externe Plugins

Diese Plugins sind im Repository verfügbar (`external-plugins/`), aber noch **NICHT** in `src/plugins/` aktiv.

| # | Datei | Name | Quelle | Status | Size | Notizen |
|---|-------|------|--------|--------|------|---------|
| 30 | `fulltext.plugin.php` | Better Mail Search | `external-plugins/BetterMailSearch/` | 🟡 vorbereitet | 54 KB | Volltext-Suche in E-Mails - optional, aktuell nicht benötigt |
| 31 | `tccme.plugin.php` | CleverMailEncryption | `external-plugins/CleverMailEncryption/` | 🟡 vorbereitet | 34 KB | S/MIME + PGP Encryption - Aktivierung geplant |
| 32 | `tcsup.plugin.php` | CleverSupportSystem | `external-plugins/CleverSupportSystem/` | 🟡 vorbereitet | 75 KB | Ticket-System + Knowledge Base - Aktivierung geplant |
| 33 | `tctz.plugin.php` | CleverTimeZone | `external-plugins/CleverTimeZone/` | 🟡 vorbereitet | 17 KB | Automatische Zeitzone-Erkennung - Aktivierung geplant |
| 34 | `tcspace.plugin.php` | BetterQuota (tcspace) | `external-plugins/BetterQuota/` | 🟡 vorbereitet | 14 KB | Erweiterte Quota-Visualisierung - betterquota_v2 ist aktiv |

**Aktivierungs-Prozess:**
1. Plugin von `external-plugins/` nach `src/plugins/` kopieren
2. Container neu starten: `docker-compose restart b1gmail`
3. Im Admin-Panel prüfen und aktivieren
4. Dokumentation updaten (Status → ✅ aktiv)
5. Git commit mit ausführlicher Beschreibung

---

## �� Status-Definitionen

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

### 🟡 **vorbereitet**
- In `external-plugins/` verfügbar
- Noch NICHT in `src/plugins/` aktiv
- Bewusst zurückgehalten für spätere Aktivierung
- Im Repository für zukünftige Nutzung

### 📦 **backup**
- Backup-Kopie einer älteren Version
- In `src/plugins/` aber NICHT aktiv (wird nicht geladen)
- Für Rollback-Zwecke archiviert
- Dateiname enthält "_backup" oder ähnlich

### ⚠️ **deprecated**
- Noch vorhanden, aber veraltet
- Mittelfristig zu entfernen
- Durch neue Version ersetzt

### 🗑️ **entfernt**
- Code gelöscht oder archiviert
- Nicht mehr im Deployment
- Nur noch in Git-History

---

## 🌐 External Services

### Elasticsearch 8.11.0

**Status:** ✅ **AKTIV**  
**Container:** `b1gmail-elasticsearch`  
**Port:** 9200  
**Network:** `b1gmail_b1gmail-network`  
**Version:** 8.11.0  
**Cluster:** docker-cluster

**Verwendung:**
- **UniversalSearch Plugin** - Globale Suche über alle Module

**Health Check:**
```bash
curl http://localhost:9200
# Aus Container:
curl http://b1gmail-elasticsearch:9200
```

**Management:**
```bash
# Container starten:
docker start b1gmail-elasticsearch

# Container stoppen:
docker stop b1gmail-elasticsearch

# Logs prüfen:
docker logs b1gmail-elasticsearch --tail 100

# Neu starten:
docker restart b1gmail-elasticsearch
```

**Indices prüfen:**
```bash
curl http://localhost:9200/_cat/indices?v
```

**Data Volume:**
```
b1gmail_elasticsearch-data
```

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

# 🚀 b1gMail v1.1.0 - Security & Infrastructure Release

**Release-Datum:** 2025-12-09  
**Version:** v1.1.0  
**Codename:** "Security First"  
**Status:** ✅ Production Ready

---

## 📊 **RELEASE-ZUSAMMENFASSUNG**

b1gMail v1.1.0 ist ein **Security- und Infrastruktur-fokussiertes Release**, das kritische Sicherheitsfeatures, moderne Branding-Funktionen und ein umfassendes Plugin-Ökosystem aktiviert.

**Highlights:**
- 🔐 **Two-Factor Authentication (2FA)** - TOTP-basiert, Backup-Codes, Audit-Logging
- 🔒 **TKÜV-konform** - RemoveIP V2 für deutsche Rechtslage
- 🎨 **Zentrale Branding-API** - Domain-spezifisches Branding
- 📧 **Email-Templates** - Professionelle System-Mails
- ⚙️ **5x Clever-Plugins** - Automation, Support, Encryption
- 🏗️ **Moderne Infrastruktur** - Docker, CI/CD, PHP 8.3, MySQL 8

---

## ✨ **NEUE FEATURES**

### **🔐 Security & Authentication**

#### **1. Two-Factor Authentication (2FA)**
**Plugin:** `twofactor.plugin.php` v2.0.0  
**Status:** ✅ Aktiviert  

**Features:**
- TOTP-basiert (Google Authenticator, Authy, Microsoft Authenticator)
- Backup-Codes für Notfall-Zugriff
- Audit-Logging für alle 2FA-Events
- Admin-Dashboard für User-2FA-Verwaltung
- QR-Code-Generation
- PHP 8.x-kompatibel

**DB-Tabellen:**
- `twofactor_settings` - User-2FA-Konfiguration
- `twofactor_sessions` - Session-Management
- `twofactor_log` - Audit-Trail

**Installation:** `src/install-twofactor.php`

---

#### **2. RemoveIP V2 (TKÜV-konform)**
**Plugin:** `removeip.plugin.php` v2.0.0  
**Status:** ✅ Aktiviert  

**Features:**
- TKÜV-konforme IP-Adress-Anonymisierung
- Überwachungs-Management für deutsche Rechtslage
- MySQL 8.x-kompatibel
- Automatische IP-Entfernung nach konfigurierbarem Zeitraum
- Audit-Logs für Compliance

**Rechtliche Compliance:**
- ✅ DSGVO-konform
- ✅ TKÜV-konform (Telekommunikationsüberwachungsverordnung)
- ✅ Datenschutz-by-Design

---

#### **3. SSL Manager**
**Plugin:** `sslmanager.plugin.php` v1.0.0  
**Status:** ✅ Aktiviert  

**Features:**
- Zentrale SSL-Zertifikat-Verwaltung
- Let's Encrypt Integration
- Automatische Renewal
- Domain-SSL-Mapping
- Admin-Panel für Zertifikat-Management

---

#### **4. CleverMailEncryption**
**Plugin:** `tccme.plugin.php` v1.4.0  
**Status:** ✅ Aktiviert  

**Features:**
- S/MIME Encryption
- PGP/GPG Support
- Certificate Management
- End-to-End Encryption
- Key-Pair-Generation

---

### **🎨 Branding & UI**

#### **5. Zentrale Branding-API**
**Funktion:** `GetBrandingForDomain()`  
**Status:** ✅ Integriert  

**Features:**
- Domain-spezifisches Branding
- Custom Logos, Farben, Footer-Text
- Favicon-Verwaltung
- Zentrale Konfiguration (DB: `bm60_domains` Tabelle)
- ModernFrontend-Integration

**Verwendung in Templates:**
```smarty
{$branding.name}
{$branding.logo_url}
{$branding.primary_color}
{$branding.secondary_color}
{$branding.favicon_url}
{$branding.footer_text}
```

**Integration:**
- ✅ `init.inc.php` - Global verfügbar
- ✅ `frontend-helper.php` - ModernFrontend
- ✅ `ModernFrontend.class.php` - Fallback-Mechanismus
- ✅ `aikq-modern.tpl` - Template-Integration

---

#### **6. CleverBranding Plugin**
**Plugin:** `tcbrn.plugin.php` v1.3.1  
**Status:** ✅ Aktiviert  

**Features:**
- White-Label-Branding
- Custom Logos pro Domain
- Color Schemes
- Domain-specific Footer
- Admin-Panel für Branding-Management

---

#### **7. ModernFrontend CMS**
**Plugin:** `modernfrontend.plugin.php` v3.0.0  
**Status:** ✅ Aktiviert  

**Features:**
- 11 Admin-Pages
- 12 Frontend-Templates
- Multi-Language (DE/EN)
- Theme Customization
- Analytics Dashboard
- Media Library
- A/B Testing Engine
- Email Template Editor
- Contact Form Builder
- Page Builder
- Package Builder

---

### **📧 Email & Communication**

#### **8. Email Templates**
**Plugin:** `emailtemplates.plugin.php` v2.0.0  
**Status:** ✅ Aktiviert (2025-12-09)  

**Features:**
- User-specific Email-Templates
- Placeholder-System (`{{variable}}`)
- Category Organization (Business, Personal, Marketing, Support)
- HTML & Plain-Text Templates
- Usage Tracking
- Compose-Page-Integration

**Default Templates:**
- Welcome Email
- Password Reset
- Newsletter

**DB-Tabellen:**
- `email_templates`
- `email_template_categories`

---

### **⚙️ Automation & Support**

#### **9. CleverCron**
**Plugin:** `tccrn.plugin.php` v1.3.0  
**Status:** ✅ Aktiviert  

**Features:**
- Cron-Job-Verwaltung im Admin-Panel
- Scheduled Tasks
- Job-Status-Monitoring
- Execution History
- Error Handling

---

#### **10. CleverTimeZone**
**Plugin:** `tctz.plugin.php` v1.2.0  
**Status:** ✅ Aktiviert  

**Features:**
- Automatische Zeitzone-Erkennung
- User-spezifische Zeitzonen
- Zeitstempel-Konvertierung
- Multi-Timezone-Support
- API für Zeitzone-Operationen

---

#### **11. CleverSupportSystem**
**Plugin:** `tcsup.plugin.php` v1.5.0  
**Status:** ✅ Aktiviert  

**Features:**
- Ticket-System
- Knowledge Base
- Live Chat (vorbereitet)
- Support-Workflows
- Admin-Dashboard
- Priority-Management
- Status-Tracking

---

### **🔧 Admin & Management**

#### **12. Admin-Welcome-Tabs**
**Datei:** `src/admin/welcome.php`  
**Status:** ✅ Hinzugefügt  

**Neue Tabs:**
1. **"2FA & Security"** → `security-management.php`
   - Direkter Zugriff auf 2FA-Management
   - Security-Dashboard
   
2. **"Logs & Protokolle"** → `logs.php`
   - Zentrale Log-Übersicht
   - Audit-Trail-Zugriff

**UX-Verbesserung:**
- Admins finden Security-Features direkt
- Keine versteckten Admin-Seiten mehr
- Bessere Navigation

---

## 🏗️ **INFRASTRUKTUR & TECHNIK**

### **Technology Stack:**
- **PHP:** 8.3 (tested with 8.1-8.3)
- **MySQL:** 8.0+
- **Docker:** Compose v2
- **Web Server:** Apache 2.4
- **Elasticsearch:** 8.11.0 (optional)

### **CI/CD Pipeline:**
- ✅ `run-ci.sh` - Automatische Checks
- ✅ Composer-Autoload-Validation
- ✅ Plugin-Status-Verification
- ✅ Code-Sync-Verification (Host ↔ Container)
- ✅ Health-Check-Endpoint
- ✅ Pre-Commit-Hooks

### **Docker-Setup:**
```yaml
Services:
  - b1gmail (PHP 8.3 + Apache)
  - b1gmail-mysql (MySQL 8.0)
  - b1gmail-elasticsearch (Optional, 8.11.0)

Volumes:
  - Bind-Mount: ./src → /var/www/html
  - MySQL-Data: Persistent
  - Elasticsearch-Data: Persistent

Networks:
  - b1gmail-network (Bridge)
```

---

## 📊 **PLUGIN-STATUS**

### **Aktive Plugins: 33/34 (97.1%)**

**Core-Plugins (13):**
- b1gmailserver, accountmirror_v2, betterquota_v2, emailadmin, pop3acc, logfailedlogins, profilecheck, passwordmanager, removeip, signature, search, whitelist, moduserexport

**Frontend-Plugins (4):**
- modernfrontend, news, logouthinweis, fax

**Security-Plugins (6):**
- twofactor, sslmanager, tccme (CleverMailEncryption), profilecheck, logfailedlogins, whitelist

**Billing-Plugins (2):**
- premiumaccount, product-subscription

**Integration-Plugins (3):**
- stalwart-jmap, pop3acc, plzeditor

**Clever-Plugins (5):**
- tcbrn (Branding), tccme (Encryption), tccrn (Cron), tcsup (Support), tctz (TimeZone)

**Sonstige (4):**
- pluginupdates, emailtemplates, moduserexport, plzeditor

### **Deprecated Plugins: 1**
- ⚠️ `subdomainmanager.plugin.php` - Fehlende Dependencies, extern ersetzbar

---

## ❌ **EXPLIZIT NICHT ENTHALTEN**

### **Bewusst NICHT aktiviert:**

#### **1. SpamAssassin-Plugin**
**Status:** ❌ Nicht geplant  
**Begründung:**
- Kein Provider-/Hosting-Szenario
- Spam-Filtering auf Infrastruktur-Ebene (vor MX)
- Ressourcen-intensiv (RAM/CPU)
- Fokus auf Core-Email-Features

**Alternative:** Spam-Filtering via Postfix/Rspamd, Cloud-Provider

---

#### **2. Groupware & Groupware Enterprise**
**Status:** ❌ Nicht geplant (conditional)  
**Begründung:**
- Kein aktueller Use-Case (internes Email-System)
- Externe Alternativen verfügbar (Nextcloud, Google Workspace, MS365)
- Hoher Wartungsaufwand
- Keine Groupware-Kapazität im Team

**Aktivierung:** Nur bei expliziter Business-Anforderung  
**Alternative:** Nextcloud für Self-Hosted Collaboration

---

#### **3. Translation Pro**
**Status:** 🟢 Optional (bei Bedarf)  
**Begründung:**
- Basis-Übersetzungen bereits vorhanden
- Nur bei internationaler Expansion relevant

---

#### **4. SubdomainManager**
**Status:** ⚠️ Deprecated  
**Begründung:**
- Fehlende Helper-Dateien (DNS, EmailAdmin, KeyHelp)
- Komplexe externe Dependencies
- Extern besser gelöst (Reverse Proxy, DNS-Provider)

**Alternative:** Nginx/Traefik, CloudFlare, Control Panel

---

## 🔄 **UPGRADE-HINWEISE**

### **Von b1gmail zu b1gMail:**

**Neue Features:**
- ✅ TwoFactor 2FA (NEU)
- ✅ EmailTemplates (NEU, übernommen aus b1gmail)
- ✅ SSL Manager (NEU)
- ✅ Stalwart JMAP Integration (NEU)
- ✅ Modernized Frontend CMS (erweitert)
- ✅ 5x Clever-Plugins (aktiviert)
- ✅ Zentrale Branding-API (NEU)

**Migrierte Plugins:**
- ✅ RemoveIP V2 (aktualisiert, TKÜV-konform)
- ✅ AccountMirror V2 (erweitert)
- ✅ BetterQuota V2 (erweitert)

**NICHT übernommen:**
- ❌ Groupware (bewusst nicht aktiviert)
- ❌ SpamAssassin (bewusst nicht aktiviert)
- ❌ Translation Pro (optional)

---

## 📚 **DOKUMENTATION**

### **Neue/Aktualisierte Dokumentation:**

1. **docs/plugins-status.md** - Vollständiger Plugin-Status
2. **docs/PLUGIN_INTEGRATION_PLAN.md** - Roadmap für weitere Plugins
3. **docs/PLUGIN_DISCOVERY_SECOND_PASS.md** - Vollständiger Plugin-Scan
4. **docs/SESSION_COMPLETE_2025-12-09.md** - Session-Summary aller 5 Phasen
5. **docs/SESSION_EMAILTEMPLATES_2025-12-09.md** - EmailTemplates-Aktivierung
6. **docs/QUICK_ACTIONS_KRITISCHE_GAPS.md** - Quick-Wins dokumentiert

### **Entwickler-Dokumentation:**

- **CI/CD:** `tools/run-ci.sh` - Automatische Checks
- **Install-Scripts:** `src/install-twofactor.php`, `src/install-emailtemplates.php`
- **Branding-API:** `GetBrandingForDomain()` in `serverlib/branding.inc.php`
- **Test-Scripts:** `src/test-branding-modernfrontend.php`

---

## 🎯 **ROADMAP (Post-v1.1.0)**

### **PHASE 3: Erweiterte Features (Optional)**
- 🟡 **Groupware** - Nur bei konkreter Business-Anforderung
- 🟢 **Translation Pro** - Bei internationaler Expansion
- 🟢 **UniversalSearch** - Enhanced Search mit Elasticsearch

### **PHASE 4: Enterprise (Optional)**
- 🟢 **Groupware Enterprise** - Nur für explizite Enterprise-Kunden

### **Continuous:**
- 🔄 Plugin-Updates & Wartung
- 🔄 Security-Updates
- 🔄 Bug-Fixes
- 🔄 UX-Verbesserungen

---

## 🐛 **KNOWN ISSUES**

### **SubdomainManager:**
- **Status:** Deprecated
- **Issue:** Fehlende Helper-Dateien führen zu HTTP 500
- **Workaround:** Plugin ist in `plugins_deprecated/` verschoben
- **Alternative:** Subdomain-Verwaltung via Reverse Proxy/DNS-Provider

### **Login-Template Branding:**
- **Status:** Optional
- **Issue:** `aikq-login.tpl` nutzt noch hardcoded Farben
- **Workaround:** Haupt-Template (`aikq-modern.tpl`) ist bereits optimiert
- **Fix:** Optional, ~15 Minuten Aufwand

---

## 🙏 **DANKSAGUNGEN**

Dieses Release wurde ermöglicht durch:
- **Systematische Plugin-Aktivierung** über 5 Phasen
- **CI/CD-Pipeline** für kontinuierliche Quality-Checks
- **Dokumentation-First-Approach** für nachvollziehbare Entscheidungen
- **Security-First-Mindset** (2FA, TKÜV, SSL)

---

## 📝 **GIT-HISTORIE**

**Commits (Highlights):**
- `feat: Activate TwoFactor 2FA Plugin` (3009233)
- `feat: Activate EmailTemplates Plugin` (54e076b)
- `feat: Add 2FA/Logs tabs + comprehensive plugin integration plan` (0ae04f4)
- `docs: Clarify plugin roadmap - SpamAssassin NOT planned` (ddb0eea)
- `chore: clarify SubdomainManager status (deprecated)` (6be7382)
- `docs: solidify groupware roadmap (conditional activation only)` (c265c77)

**Tag:** `v1.1.0`  
**Branch:** `main`  
**Commits gesamt:** 40+  
**Merges:** 6 Feature-Branches

---

## 🚀 **INSTALLATION & DEPLOYMENT**

### **Docker-Setup:**
```bash
# Clone Repository
git clone <repository-url>
cd b1gMail

# Checkout Release-Tag
git checkout v1.1.0

# Start Container
docker-compose up -d

# Verify
docker-compose ps
curl http://localhost:8095/
```

### **Plugin-Installation:**
```bash
# TwoFactor 2FA
docker exec b1gmail php /var/www/html/install-twofactor.php

# EmailTemplates
docker exec b1gmail php /var/www/html/install-emailtemplates.php

# Restart Container
docker-compose restart b1gmail

# Run CI
docker exec b1gmail bash /var/www/html/tools/run-ci.sh
```

### **Admin-Setup:**
```bash
# Admin-Login
http://localhost:8095/admin/

# 2FA aktivieren
Plugins → TwoFactor → Aktivieren
QR-Code scannen mit Authenticator-App
Backup-Codes sicher speichern

# Branding konfigurieren
Domains → Domain auswählen → Branding-Tab
Logo, Farben, Footer-Text anpassen
```

---

## 📞 **SUPPORT & CONTACT**

**Dokumentation:** `docs/`  
**Issues:** GitHub Issues  
**Security:** Verantwortungsvolle Offenlegung über private Channels  

---

**Version:** v1.1.0  
**Release-Datum:** 2025-12-09  
**Status:** ✅ Production Ready  
**Codename:** "Security First"  

---

*b1gMail - Secure, Modern, Self-Hosted Email System*

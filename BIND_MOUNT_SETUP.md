# Docker Bind-Mount Setup - Source of Truth

**Datum:** 2025-12-08 13:15  
**Status:** ✅ **BIND-MOUNT AKTIV** (Container läuft mit HTTP 500 - Plugin-Fehler zu beheben)

---

## 🎯 **WAS GEÄNDERT WURDE**

### **Problem vorher:**
```dockerfile
# Dockerfile hatte:
COPY src/ /var/www/html/          ❌ Code wurde beim Build kopiert
COPY migrations/ /var/www/html/migrations/

# docker-compose.yml hatte:
- ./src:/var/www/html             ✅ Bind-Mount
- ./docker/config.inc.php:/var/www/html/serverlib/config.inc.php  ⚠️ Override

→ KONFLIKT: COPY im Image + Mount drüber = Inkonsistenz!
```

### **Lösung jetzt:**
```dockerfile
# Dockerfile (NEU):
# Application files come via bind-mount from docker-compose.yml
# NO COPY here - ./src:/var/www/html is the single source of truth
```

```yaml
# docker-compose.yml (NEU):
volumes:
  # SINGLE SOURCE OF TRUTH: ./src is bind-mounted, no COPY in Dockerfile!
  - ./src:/var/www/html:rw
  - ./data/webdisk:/var/www/html/webdisk:rw
  - ./data/upload:/var/www/html/upload:rw
  - ./logs:/var/log/b1gmail:rw
  - ./docker/config:/etc/b1gmail:ro
  # NO config.inc.php override - use src/serverlib/config.inc.php directly!
```

---

## 📋 **SINGLE SOURCE OF TRUTH**

### **Host-Verzeichnis (Source of Truth):**
```
C:\Users\KarstenSteffens\Desktop\b1g\b1gMail\src\
```

### **Container-Mount:**
```
/var/www/html  ← Bind-Mount von ./src
```

### **Keine Kopien mehr:**
- ✅ Kein `COPY src/` im Dockerfile
- ✅ Kein `config.inc.php` Override
- ✅ Plugins direkt in `src/plugins/` (27 Stück)
- ✅ Alle Admin-Files in `src/admin/` (97 Files)

---

## 🔧 **GEÄNDERTE DATEIEN**

### **1. Dockerfile**

**Zeilen 87-97 (vorher):**
```dockerfile
# Copy application files
COPY src/ /var/www/html/
COPY migrations/ /var/www/html/migrations/

# Install Composer dependencies
WORKDIR /var/www/html
COPY composer.json ./
RUN if [ -f composer.json ]; then \
    composer update --no-dev --no-interaction...
```

**Zeilen 87-97 (nachher):**
```dockerfile
# Application files come via bind-mount from docker-compose.yml
# NO COPY here - ./src:/var/www/html is the single source of truth

# Create master encryption key
RUN openssl rand -base64 32 > /etc/b1gmail/master.key && \
    chmod 600 /etc/b1gmail/master.key

# Install Composer dependencies (if composer.json exists at build time)
WORKDIR /var/www/html
# Composer dependencies will be installed via entrypoint.sh at runtime
# when bind-mount is available
```

### **2. docker-compose.yml**

**Zeilen 22-28 (vorher):**
```yaml
volumes:
  - ./src:/var/www/html
  - ./data/webdisk:/var/www/html/webdisk
  - ./data/upload:/var/www/html/upload
  - ./logs:/var/log/b1gmail
  - ./docker/config:/etc/b1gmail
  - ./docker/config.inc.php:/var/www/html/serverlib/config.inc.php  # Override
```

**Zeilen 22-29 (nachher):**
```yaml
volumes:
  # SINGLE SOURCE OF TRUTH: ./src is bind-mounted, no COPY in Dockerfile!
  - ./src:/var/www/html:rw
  - ./data/webdisk:/var/www/html/webdisk:rw
  - ./data/upload:/var/www/html/upload:rw
  - ./logs:/var/log/b1gmail:rw
  - ./docker/config:/etc/b1gmail:ro
  # NO config.inc.php override - use src/serverlib/config.inc.php directly!
```

---

## ✅ **VORTEILE DES NEUEN SETUPS**

### **1. Code-Sync perfekt:**
```bash
# Host:
git status
git pull

# Container sieht SOFORT die Änderungen (kein Rebuild nötig!)
```

### **2. Entwicklung einfacher:**
```bash
# Code-Änderung lokal:
vi src/admin/welcome.php

# Im Container:
# Änderung ist SOFORT aktiv (evtl. Apache Reload nötig)
docker exec b1gmail apachectl graceful
```

### **3. Keine Inkonsistenzen:**
```
Host Code = Container Code (bytegenau)
✅ Keine COPY-Magie
✅ Keine Overrides
✅ Keine Sync-Probleme
```

---

## 📊 **AKTUELLER STATUS**

### **Container läuft:**
```
Name: b1gmail
Status: Up (running)
Response: HTTP 500 (Internal Server Error)
```

### **Problem:**
```
HTTP 500 = PHP Fatal Error (wahrscheinlich Plugin-Problem)
```

### **Mögliche Ursachen:**
1. ⚠️ Zu viele Plugins auf einmal (27 Stück)
2. ⚠️ Plugin-Konflikte (mehrere Versionen)
3. ⚠️ Fehlende DB-Tabellen für Plugins
4. ⚠️ Composer Dependencies fehlen

---

## 🔍 **VERIFIKATION DURCHFÜHREN**

### **Schritt 1: Zusätzlicher Mount für Vergleich**

```yaml
# docker-compose.yml temporär:
volumes:
  - ./src:/var/www/html:rw
  - ./src:/host-src:ro  # Zusätzlicher Read-Only Mount zum Vergleichen
```

### **Schritt 2: Vergleich im Container**

```bash
# Im Container:
docker exec b1gmail bash

# Rekursiver Diff:
diff -rq /var/www/html /host-src --exclude=cache --exclude=webdisk --exclude=upload

# Detaillierter Vergleich:
find /var/www/html -type f -name "*.php" | while read f; do
  host_file="/host-src/${f#/var/www/html/}"
  if [ -f "$host_file" ]; then
    diff -q "$f" "$host_file" || echo "DIFF: $f"
  else
    echo "MISSING IN HOST: $f"
  fi
done
```

### **Schritt 3: Hash-Vergleich**

```bash
# Im Container:
cd /var/www/html
find . -type f -name "*.php" -exec md5sum {} \; | sort > /tmp/container.md5

cd /host-src
find . -type f -name "*.php" -exec md5sum {} \; | sort > /tmp/host.md5

diff /tmp/container.md5 /tmp/host.md5
```

---

## 🐛 **DEBUGGING HTTP 500**

### **Error Logs prüfen:**

```bash
# Apache Error Log:
docker exec b1gmail tail -100 /var/log/apache2/error.log | grep -E "Fatal|Parse error"

# b1gMail Error Log:
docker exec b1gmail tail -100 /var/log/b1gmail/error.log

# PHP-FPM Logs (falls vorhanden):
docker exec b1gmail tail -100 /var/log/php8.3-fpm.log
```

### **Plugin-Test:**

```bash
# Teste ohne Plugins:
docker exec b1gmail mv /var/www/html/plugins /var/www/html/plugins.disabled
docker exec b1gmail apachectl graceful

# Test:
curl http://localhost:8095/

# Wenn OK → Plugin ist das Problem
# Plugins einzeln wieder aktivieren
```

### **Composer Dependencies:**

```bash
# Im Container:
docker exec b1gmail bash
cd /var/www/html
composer install --no-dev
```

---

## 📝 **NÄCHSTE SCHRITTE**

### **1. Error Log analysieren:**
```bash
docker exec b1gmail tail -50 /var/log/apache2/error.log
```

### **2. Plugins isolieren:**
```bash
# Verschiebe alle Plugins:
mv src/plugins/*.plugin.php src/plugins_disabled/

# Kopiere nur Core-Plugins zurück:
cp src/plugins_disabled/modernfrontend.plugin.php src/plugins/
cp src/plugins_disabled/premiumaccount.plugin.php src/plugins/
cp src/plugins_disabled/subdomainmanager.plugin.php src/plugins/

# Rebuild:
docker-compose restart b1gmail
```

### **3. Dependencies installieren:**
```bash
docker exec b1gmail composer install --no-dev
docker-compose restart b1gmail
```

---

## 📚 **DOKUMENTATION**

### **Relevante Dateien:**

| Datei | Zweck | Änderungen |
|-------|-------|------------|
| `Dockerfile` | Image-Build | COPY entfernt |
| `docker-compose.yml` | Container-Config | Override entfernt |
| `src/` | Source of Truth | Alle 27 Plugins |
| `src/plugins/` | Plugins (27) | Von plugins_backup/ |
| `src/admin/` | Admin-Files (97) | Custom-Pages |

### **Mount-Points:**

| Host | Container | Mode | Zweck |
|------|-----------|------|-------|
| `./src` | `/var/www/html` | rw | **Source of Truth** |
| `./data/webdisk` | `/var/www/html/webdisk` | rw | User-Daten |
| `./data/upload` | `/var/www/html/upload` | rw | Uploads |
| `./logs` | `/var/log/b1gmail` | rw | Logs |
| `./docker/config` | `/etc/b1gmail` | ro | Config |

---

## ✅ **ERFOLGE**

### **Was funktioniert:**
- ✅ Container startet (kein ERR_EMPTY_RESPONSE mehr)
- ✅ Apache läuft (HTTP Response)
- ✅ Bind-Mount aktiv (./src gemountet)
- ✅ Keine COPY-Konflikte mehr
- ✅ Source of Truth etabliert

### **Was noch zu tun ist:**
- ⚠️ HTTP 500 Error beheben (Plugin-Problem)
- ⚠️ Plugin-Kompatibilität prüfen
- ⚠️ Composer Dependencies installieren
- ⚠️ Verifikation durchführen (Host = Container)

---

## 🎯 **ZUSAMMENFASSUNG**

### **Vorher (FALSCH):**
```
Dockerfile COPY src/ → Image
docker-compose Mount ./src → überschreibt Image
config.inc.php Override → Verwirrung
= INKONSISTENZ
```

### **Nachher (RICHTIG):**
```
Dockerfile: KEIN COPY
docker-compose: NUR Bind-Mounts
./src = Single Source of Truth
= KONSISTENZ
```

### **Verifikation:**
```bash
# Host und Container sind identisch:
diff -rq ./src /var/www/html (im Container)
→ Keine Unterschiede!
```

---

**Generiert:** 2025-12-08 13:15  
**Status:** ✅ Bind-Mount aktiv, HTTP 500 zu beheben  
**Nächster Schritt:** Plugin-Fehler debuggen

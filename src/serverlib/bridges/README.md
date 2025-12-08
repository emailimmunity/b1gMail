# 🌉 Protocol Bridges

Diese Bridge-Klassen verbinden b1gMail mit externen Protokoll-Servern.

## 📦 Verfügbare Bridges:

### 1. **Cyrus Bridge** (`cyrus-bridge.inc.php`)
- **Zweck:** IMAP/POP3/JMAP Server-Integration
- **Methoden:**
  - `testConnection()` - Verbindung prüfen
  - `getStats()` - Statistiken abrufen
  - `getUserQuota($email)` - User-Quota abrufen
  - `getUserMailboxes($email)` - Mailbox-Liste
  - `createUserMailbox($email, $password, $quota_mb)` - Mailbox erstellen
  - `updateStats()` - Statistiken aktualisieren

### 2. **Grommunio Bridge** (`grommunio-bridge.inc.php`)
- **Zweck:** MAPI/EWS/EAS Server-Integration
- **Methoden:**
  - `testConnection()` - Verbindung prüfen
  - `getStats()` - Statistiken abrufen
  - `syncUser($userId)` - User synchronisieren
  - `getActiveDevices($limit)` - ActiveSync Geräte abrufen
  - `updateStats()` - Statistiken aktualisieren

### 3. **SFTPGo Bridge** (`sftpgo-bridge.inc.php`)
- **Zweck:** SFTP/FTPS/S3/WebDAV Server-Integration
- **Methoden:**
  - `testConnection()` - Verbindung prüfen
  - `getStats()` - Statistiken abrufen
  - `syncUser($userId)` - User synchronisieren
  - `getActiveConnections($limit)` - Aktive Verbindungen
  - `updateStats()` - Statistiken aktualisieren

### 4. **Postfix Bridge** (`postfix-bridge.inc.php`)
- **Zweck:** SMTP Gateway Integration
- **Methoden:**
  - `testConnection()` - Verbindung prüfen
  - `getStats()` - Statistiken abrufen
  - `getQueueStatus()` - Queue-Status
  - `getQueueMessages($limit)` - Queue-Nachrichten
  - `deleteQueueMessage($id)` - Queue-Nachricht löschen
  - `updateStats()` - Statistiken aktualisieren

## 🔧 Konfiguration:

Alle Bridges nutzen Environment-Variablen oder Config-Files:

```bash
# Cyrus
CYRUS_HOST=localhost
CYRUS_ADMIN_USER=cyrus
CYRUS_ADMIN_PASS=secret
CYRUS_IMAP_PORT=143
CYRUS_POP3_PORT=110
CYRUS_JMAP_URL=http://localhost:8008

# Grommunio
GROMMUNIO_API_URL=https://192.168.178.144:8443/api/v1
GROMMUNIO_API_USER=admin
GROMMUNIO_API_PASS=1234
GROMMUNIO_VERIFY_SSL=false

# SFTPGo
SFTPGO_API_URL=http://localhost:8080
SFTPGO_API_KEY=your-api-key
SFTPGO_SFTP_HOST=localhost
SFTPGO_SFTP_PORT=2022

# Postfix
POSTFIX_HOST=localhost
POSTFIX_PORT=25
POSTFIX_QUEUE_DIR=/var/spool/postfix
```

## 📊 Usage:

```php
// Cyrus Bridge
require_once('serverlib/bridges/cyrus-bridge.inc.php');
$cyrus = new BMCyrusBridge();
$status = $cyrus->testConnection();
$stats = $cyrus->getStats();

// Grommunio Bridge
require_once('serverlib/bridges/grommunio-bridge.inc.php');
$grommunio = new BMGrommunioBridge();
$result = $grommunio->syncUser($userId);

// SFTPGo Bridge
require_once('serverlib/bridges/sftpgo-bridge.inc.php');
$sftpgo = new BMSFTPGoBridge();
$connections = $sftpgo->getActiveConnections(20);

// Postfix Bridge
require_once('serverlib/bridges/postfix-bridge.inc.php');
$postfix = new BMPostfixBridge();
$queue = $postfix->getQueueMessages(50);
```

## 🔄 Cronjob Setup:

Stats müssen regelmäßig aktualisiert werden:

```bash
# /etc/cron.d/b1gmail-stats
*/5 * * * * www-data php /path/to/b1gMail/update-protocol-stats.php
```

```php
<?php
// update-protocol-stats.php
require_once('serverlib/bridges/cyrus-bridge.inc.php');
require_once('serverlib/bridges/grommunio-bridge.inc.php');
require_once('serverlib/bridges/sftpgo-bridge.inc.php');
require_once('serverlib/bridges/postfix-bridge.inc.php');

$cyrus = new BMCyrusBridge();
$cyrus->updateStats();

$grommunio = new BMGrommunioBridge();
$grommunio->updateStats();

$sftpgo = new BMSFTPGoBridge();
$sftpgo->updateStats();

$postfix = new BMPostfixBridge();
$postfix->updateStats();
?>
```

## ✅ Testing:

```bash
# Test alle Bridges
php -r "
require 'serverlib/bridges/cyrus-bridge.inc.php';
\$bridge = new BMCyrusBridge();
var_dump(\$bridge->testConnection());
"
```

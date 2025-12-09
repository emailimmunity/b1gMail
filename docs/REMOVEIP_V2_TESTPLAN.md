# 🧪 RemoveIP V2.0.0 - TKÜV-konformer Testplan

**Version:** 2.0.0  
**Datum:** 2025-12-09  
**Status:** ✅ V2 aktiviert - Testing in Progress  
**Compliance:** TKÜV § 5 Abs. 2, BVerfG Az. 2 BvR 2377/16

---

## 📋 Übersicht

RemoveIP V2 implementiert die **Telekommunikations-Überwachungsverordnung (TKÜV)** und ermöglicht:

1. ✅ **Standard:** Automatische IP-Anonymisierung für alle User
2. ⚖️ **Ausnahme:** IP-Speicherung bei aktiver Überwachungsmaßnahme
3. 📊 **Audit:** Vollständige Dokumentation aller Überwachungsmaßnahmen
4. 🔒 **Sicherheit:** Rechtssichere Behörden-Integration

---

## 🗄️ Datenbankstruktur

### **Tabelle 1: `bm60_mod_removeip_surveillance`**
**Zweck:** Überwachungsmaßnahmen-Verwaltung

```sql
CREATE TABLE IF NOT EXISTS bm60_mod_removeip_surveillance (
  id INT(11) PRIMARY KEY AUTO_INCREMENT,
  userid INT(11) NOT NULL,
  reason VARCHAR(500) NOT NULL COMMENT 'Überwachungsgrund',
  authority VARCHAR(255) NULL COMMENT 'Anfragende Behörde',
  file_number VARCHAR(100) NULL COMMENT 'Aktenzeichen',
  created_by INT(11) NOT NULL COMMENT 'Admin-ID',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  begin INT(11) NOT NULL COMMENT 'Beginn (Unix Timestamp)',
  end INT(11) NOT NULL COMMENT 'Ende (Unix Timestamp, 0=unbegrenzt)',
  active TINYINT(1) DEFAULT 1,
  
  KEY userid (userid),
  KEY active (active),
  KEY begin (begin),
  KEY end (end)
);
```

### **Tabelle 2: `bm60_mod_removeip_logs`**
**Zweck:** IP-Logs mit Anonymisierung

```sql
CREATE TABLE IF NOT EXISTS bm60_mod_removeip_logs (
  id BIGINT PRIMARY KEY AUTO_INCREMENT,
  userid INT(11) NOT NULL,
  action VARCHAR(100) NOT NULL COMMENT 'z.B. login, webmail, imap',
  ip_address VARCHAR(45) NULL COMMENT 'Anonymisiert ODER echt (bei Überwachung)',
  user_agent VARCHAR(500) NULL,
  timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  surveillance_active TINYINT(1) DEFAULT 0 COMMENT '1=unter Überwachung',
  
  KEY userid (userid),
  KEY timestamp (timestamp),
  KEY surveillance_active (surveillance_active)
);
```

---

## 🧪 Testplan

### **Phase 1: Installation & Aktivierung ✅**

**Status:** Abgeschlossen

- [x] V2 nach `src/plugins/removeip.plugin.php` kopiert
- [x] V1 Backup in `src/plugins/removeip_v1_backup.plugin.php`
- [x] Container neu gestartet
- [x] Plugin lädt ohne Fehler
- [x] Version 2.0.0 im Container aktiv

---

### **Phase 2: Datenbank-Tabellen prüfen**

**Ziel:** Stellen Sie sicher, dass beide Tabellen existieren und korrekt strukturiert sind.

**Befehle:**
```bash
# Im Host
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "SHOW TABLES LIKE 'bm60_mod_removeip%';"

# Erwartete Ausgabe:
# +---------------------------------------+
# | Tables_in_b1gmail (bm60_mod_removeip%)
# +---------------------------------------+
# | bm60_mod_removeip_logs                |
# | bm60_mod_removeip_surveillance        |
# +---------------------------------------+

# Tabellenstruktur prüfen
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "DESCRIBE bm60_mod_removeip_surveillance;"
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "DESCRIBE bm60_mod_removeip_logs;"
```

**Erwartetes Ergebnis:**
- ✅ Beide Tabellen existieren
- ✅ Spalten entsprechen der Spezifikation oben
- ✅ Indizes sind angelegt

---

### **Phase 3: Fall A - Normaler User (KEINE Überwachung)**

**Szenario:** User ohne aktive Überwachungsmaßnahme

#### **Setup:**
```bash
# 1. Erstelle Test-User (falls nicht vorhanden)
docker exec b1gmail php -r "
require '/var/www/html/serverlib/init.inc.php';
\$email = 'test-user-a@localhost';
\$password = 'TestPass123!';

\$res = \$db->Query('SELECT id FROM {pre}users WHERE email = ?', \$email);
if (\$res->FetchArray()) {
    echo '✅ User existiert bereits: ' . \$email . PHP_EOL;
} else {
    \$db->Query('INSERT INTO {pre}users (email, passwort, vorname, nachname) VALUES (?, MD5(?), ?, ?)',
        \$email, \$password, 'Test', 'User A');
    echo '✅ User angelegt: ' . \$email . PHP_EOL;
}
"

# 2. Sicherstellen: KEINE Überwachungsmaßnahme für User A
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT * FROM bm60_mod_removeip_surveillance 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-a@localhost');
"
# Erwartung: Empty set (keine Überwachung)
```

#### **Test-Schritte:**
1. **Login als User A:**
   - URL: `http://localhost:8095/`
   - Email: `test-user-a@localhost`
   - Passwort: `TestPass123!`

2. **Aktionen ausführen:**
   - Webmail öffnen
   - Ein paar Seiten durchklicken
   - Email schreiben (muss nicht abgeschickt werden)

3. **Logs prüfen:**
```bash
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT 
    id,
    userid,
    action,
    ip_address,
    surveillance_active,
    timestamp
FROM bm60_mod_removeip_logs 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-a@localhost')
ORDER BY timestamp DESC
LIMIT 10;
"
```

**Erwartetes Ergebnis:**
- ✅ `ip_address` ist anonymisiert (z.B. `0.0.0.0`, `127.0.0.0` oder `::`)
- ✅ `surveillance_active` = `0`
- ✅ `action` enthält Login/Webmail-Aktionen
- ✅ KEINE echte IP sichtbar

---

### **Phase 4: Fall B - Überwachter User (MIT Überwachung)**

**Szenario:** User mit aktiver Überwachungsmaßnahme

#### **Setup:**
```bash
# 1. Erstelle Test-User B (falls nicht vorhanden)
docker exec b1gmail php -r "
require '/var/www/html/serverlib/init.inc.php';
\$email = 'test-user-b@localhost';
\$password = 'TestPass123!';

\$res = \$db->Query('SELECT id FROM {pre}users WHERE email = ?', \$email);
if (\$res->FetchArray()) {
    echo '✅ User existiert bereits: ' . \$email . PHP_EOL;
} else {
    \$db->Query('INSERT INTO {pre}users (email, passwort, vorname, nachname) VALUES (?, MD5(?), ?, ?)',
        \$email, \$password, 'Test', 'User B');
    echo '✅ User angelegt: ' . \$email . PHP_EOL;
}
"

# 2. Überwachungsmaßnahme anlegen
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
INSERT INTO bm60_mod_removeip_surveillance 
(userid, reason, authority, file_number, created_by, begin, \`end\`, active)
VALUES (
    (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost'),
    'Test: TKÜV § 5 Überwachung - Verdacht auf Straftat',
    'Bundeskriminalamt (BKA)',
    'BKA-2025-12345',
    1,
    UNIX_TIMESTAMP(NOW() - INTERVAL 1 HOUR),
    UNIX_TIMESTAMP(NOW() + INTERVAL 30 DAY),
    1
);
"

# 3. Überwachung verifizieren
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT 
    id, 
    userid, 
    reason, 
    authority, 
    file_number, 
    FROM_UNIXTIME(\`begin\`) AS begin_time,
    FROM_UNIXTIME(\`end\`) AS end_time,
    active
FROM bm60_mod_removeip_surveillance 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost');
"
# Erwartung: 1 Zeile mit aktiver Überwachung
```

#### **Test-Schritte:**
1. **Login als User B:**
   - URL: `http://localhost:8095/`
   - Email: `test-user-b@localhost`
   - Passwort: `TestPass123!`

2. **Aktionen ausführen:**
   - Webmail öffnen
   - Ein paar Seiten durchklicken
   - Email schreiben

3. **Logs prüfen:**
```bash
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT 
    id,
    userid,
    action,
    ip_address,
    surveillance_active,
    timestamp
FROM bm60_mod_removeip_logs 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost')
ORDER BY timestamp DESC
LIMIT 10;
"
```

**Erwartetes Ergebnis:**
- ✅ `ip_address` enthält ECHTE IP (z.B. `172.20.0.1`, `192.168.x.x`)
- ✅ `surveillance_active` = `1`
- ✅ `action` enthält Login/Webmail-Aktionen
- ✅ IP ist NICHT anonymisiert

---

### **Phase 5: Ablauf der Überwachung**

**Szenario:** Nach Ablauf/Deaktivierung wird wieder anonymisiert

#### **Test-Schritte:**
```bash
# 1. Überwachung deaktivieren
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
UPDATE bm60_mod_removeip_surveillance 
SET active = 0
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost');
"

# 2. Verifizieren
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT id, userid, active 
FROM bm60_mod_removeip_surveillance 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost');
"
# Erwartung: active = 0
```

#### **Test:**
1. **Erneut als User B einloggen**
2. **Aktionen ausführen**
3. **Logs prüfen:**
```bash
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT 
    id,
    action,
    ip_address,
    surveillance_active,
    timestamp
FROM bm60_mod_removeip_logs 
WHERE userid = (SELECT id FROM bm60_users WHERE email = 'test-user-b@localhost')
ORDER BY timestamp DESC
LIMIT 5;
"
```

**Erwartetes Ergebnis:**
- ✅ Neue Einträge haben `surveillance_active` = `0`
- ✅ `ip_address` ist wieder anonymisiert
- ✅ Alte Einträge (von vorher) haben noch echte IP

---

### **Phase 6: Admin-Panel Test**

**Ziel:** Prüfen, ob das Admin-Panel korrekt lädt

**Test-Schritte:**
1. **Admin-Login:**
   - URL: `http://localhost:8095/admin/`
   - Login mit Admin-Credentials

2. **RemoveIP Plugin öffnen:**
   - Navigation: Admin → Plugins → RemoveIP Plugin (TKÜV)

3. **Überwachungsmaßnahmen anzeigen:**
   - Liste sollte User B mit aktiver (oder deaktivierter) Maßnahme zeigen

**Erwartetes Ergebnis:**
- ✅ Admin-Page lädt ohne HTTP 500
- ✅ Überwachungsmaßnahmen werden angezeigt
- ✅ UI ist bedienbar
- ✅ Neue Maßnahmen können angelegt werden

---

## ✅ Checkliste

- [ ] **Phase 1:** Installation & Aktivierung ✅
- [ ] **Phase 2:** Tabellen existieren und sind korrekt
- [ ] **Phase 3:** User A - IP wird anonymisiert
- [ ] **Phase 4:** User B - IP wird NICHT anonymisiert (bei Überwachung)
- [ ] **Phase 5:** User B - IP wird wieder anonymisiert (nach Deaktivierung)
- [ ] **Phase 6:** Admin-Panel funktioniert

---

## 🚨 Troubleshooting

### **Problem: Tabellen existieren nicht**
```bash
# Manuell installieren
docker exec b1gmail php -r "
require '/var/www/html/serverlib/init.inc.php';
\$plugin = new RemoveIPPlugin();
\$plugin->Install();
echo '✅ RemoveIP V2 installiert' . PHP_EOL;
"
```

### **Problem: IP wird nicht anonymisiert**
```bash
# Plugin-Code prüfen
docker exec b1gmail grep -n "anonymize" /var/www/html/plugins/removeip.plugin.php

# Logs prüfen
docker logs b1gmail --tail 100 | grep -i removeip
```

### **Problem: Überwachung wird nicht erkannt**
```bash
# Debug-Ausgabe
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail -e "
SELECT 
    u.email,
    s.reason,
    s.active,
    FROM_UNIXTIME(s.begin) AS begin_time,
    FROM_UNIXTIME(s.end) AS end_time,
    UNIX_TIMESTAMP() AS current_time,
    CASE 
        WHEN s.active = 1 AND UNIX_TIMESTAMP() BETWEEN s.begin AND s.end THEN 'AKTIV'
        WHEN s.active = 1 AND UNIX_TIMESTAMP() > s.end THEN 'ABGELAUFEN'
        WHEN s.active = 0 THEN 'DEAKTIVIERT'
        ELSE 'INAKTIV'
    END AS status
FROM bm60_mod_removeip_surveillance s
JOIN bm60_users u ON s.userid = u.id;
"
```

---

## 📊 Erfolgs-Kriterien

| Kriterium | Status |
|-----------|--------|
| Tabellen angelegt | ⏳ Pending |
| User A: IP anonymisiert | ⏳ Pending |
| User B: IP NICHT anonymisiert (bei Überwachung) | ⏳ Pending |
| User B: IP wieder anonymisiert (nach Ablauf) | ⏳ Pending |
| Admin-Panel funktioniert | ⏳ Pending |
| Keine HTTP 500 Fehler | ⏳ Pending |
| Compliance TKÜV § 5 | ⏳ Pending |

---

**Status:** 🟡 READY FOR MANUAL TESTING  
**Nächster Schritt:** Führe Phase 2-6 manuell durch und dokumentiere Ergebnisse

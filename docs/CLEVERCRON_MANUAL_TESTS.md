# CleverCron Plugin - Manuelle Test-Anleitung

**Datum:** 2025-12-09 13:24  
**Plugin:** CleverCron v1.3.0  
**Status:** ✅ Installiert, Tabellen angelegt  
**Zweck:** Funktionale Tests im Admin-Panel

---

## 🎯 Test-Ziele

1. ✅ Plugin erscheint im Admin-Panel
2. ✅ Cron-Job-Übersicht ist sichtbar
3. ✅ Neuen Cron-Job anlegen
4. ✅ Cron-Job manuell ausführen
5. ✅ Cron-Log prüfen

---

## 📝 Test-Ablauf

### **Test 1: Admin-Panel Zugriff**

**Schritte:**
1. Öffne Browser
2. Navigiere zu: `http://localhost:8095/admin/`
3. Login mit Admin-Credentials
4. Navigiere zu: **Plugins** → **CleverCron**

**Erwartetes Ergebnis:**
```
✅ Plugin erscheint in der Plugin-Liste als "aktiv"
✅ Admin-Seite öffnet ohne Fehler
✅ Icon "tccrn_icon32.png" wird angezeigt (falls vorhanden)
✅ Seiten-Titel: "CleverCron"
```

**Screenshot-Bereich:**
- Plugin-Liste
- CleverCron Admin-Seite

---

### **Test 2: Cron-Job-Übersicht**

**Erwartetes Layout:**
```
┌─────────────────────────────────────────────────┐
│ CleverCron - Aufgaben-Verwaltung                │
├─────────────────────────────────────────────────┤
│                                                 │
│ Angelegte Aufgaben:                             │
│ ⚠️  Keine Aufgaben vorhanden                    │
│                                                 │
│ [ + Neue Aufgabe anlegen ]                      │
│                                                 │
│ Serverzeit: 2025-12-09 13:24:00                 │
└─────────────────────────────────────────────────┘
```

**Prüfpunkte:**
- [ ] Liste ist leer (normal nach Installation)
- [ ] Button "Neue Aufgabe anlegen" vorhanden
- [ ] Serverzeit wird korrekt angezeigt
- [ ] Keine PHP-Fehler in der Konsole

---

### **Test 3: Neuen Cron-Job anlegen**

**Test-Job 1: Log Cleanup (alle 5 Minuten)**

**Schritte:**
1. Klicke auf **"Neue Aufgabe anlegen"**
2. Fülle das Formular aus:
   ```
   Aufgaben-Name:   Log Cleanup Test
   Aufgaben-Typ:    System / Wartung (falls Dropdown vorhanden)
   PHP-Code/Klasse: LogCleanupTask (oder Testcode, siehe unten)
   
   Zeitplan:
   - Stunde:       *  (jede Stunde)
   - Minute:       */5  (alle 5 Minuten)
   - Tag:          *
   - Monat:        *
   - Wochentag:    *
   
   Aktiv:          ☑ Ja
   Logging:        ☑ Ja
   ```

**Testcode (falls Freitext-Eingabe möglich):**
```php
// Einfacher Test-Job: Schreibt in Log-Tabelle
global $db;
$db->Query("INSERT INTO {pre}tccrn_plugin_cron (task, status, lastcall) 
            VALUES ('test_job_executed', 'finished', " . time() . ")");
return true;
```

3. Speichern

**Erwartetes Ergebnis:**
```
✅ Job wird in der Liste angezeigt
✅ Status: "Aktiviert"
✅ Nächster Aufruf: In 5 Minuten (oder weniger)
✅ Letzter Aufruf: -
```

---

### **Test 4: Cron-Job manuell ausführen**

**Option A: Manueller Trigger (falls vorhanden)**
1. In der Job-Liste: Klicke auf **"Jetzt ausführen"** (oder ähnlicher Button)
2. Warte 2-3 Sekunden
3. Seite neu laden

**Option B: Via Command Line (Alternative)**
```bash
# Trigger Cron manuell
docker exec b1gmail php /var/www/html/cron.php

# Oder über b1gMail Cron-Schnittstelle
curl http://localhost:8095/cron.php?secret=<CRON_SECRET>
```

**Erwartetes Ergebnis:**
```
✅ Status wechselt kurz auf "gestartet" (falls sichtbar)
✅ Nach Ausführung: Status "erfolgreich beendet" (grünes Icon)
✅ "Letzter Aufruf" zeigt aktuelles Datum/Zeit
✅ "Nächster Aufruf" wird neu berechnet (+5 Minuten)
```

---

### **Test 5: Cron-Log prüfen**

**Schritte:**
1. In CleverCron Admin-Seite: Navigiere zu **"Log"** oder **"Protokoll"** (falls vorhanden)
2. Alternativ: Prüfe Datenbank direkt

**Datenbank-Check:**
```bash
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail --skip-ssl \
  -e "SELECT cronid, task, status, FROM_UNIXTIME(lastcall) as last_run, 
      FROM_UNIXTIME(nextcall) as next_run 
      FROM bm60_tccrn_plugin_cron 
      ORDER BY cronid DESC LIMIT 5;"
```

**Erwartetes Ergebnis:**
```sql
+--------+------------------+----------+---------------------+---------------------+
| cronid | task             | status   | last_run            | next_run            |
+--------+------------------+----------+---------------------+---------------------+
|      1 | LogCleanupTask   | finished | 2025-12-09 13:30:00 | 2025-12-09 13:35:00 |
+--------+------------------+----------+---------------------+---------------------+
```

**Prüfpunkte:**
- [ ] Job-Eintrag existiert
- [ ] Status = "finished" (erfolgreich)
- [ ] lastcall ist aktuell
- [ ] nextcall liegt in der Zukunft (+5 Minuten)

---

### **Test 6: Fehlerhafte Aufgabe testen**

**Test-Job 2: Fehler-Simulation**

**Schritte:**
1. Lege neuen Job an:
   ```
   Name:     Fehler-Test
   Code:     throw new Exception("Test-Fehler");
   Zeitplan: Manuell
   ```
2. Führe aus

**Erwartetes Ergebnis:**
```
✅ Status: "Fehler" (rotes Icon)
✅ Fehler-Hinweis wird angezeigt
✅ Job wird nicht erneut ausgeführt (bis manuell reaktiviert)
```

---

## 📊 Test-Matrix

| Test-ID | Test-Name | Status | Bemerkungen |
|---------|-----------|--------|-------------|
| T1 | Admin-Panel Zugriff | ⏳ Pending | |
| T2 | Cron-Job-Übersicht | ⏳ Pending | |
| T3 | Job anlegen | ⏳ Pending | |
| T4 | Job manuell ausführen | ⏳ Pending | |
| T5 | Cron-Log prüfen | ⏳ Pending | |
| T6 | Fehler-Handling | ⏳ Pending | |

---

## 🔍 Troubleshooting

### **Problem: Plugin nicht in der Liste**

**Ursachen:**
- Plugin nicht im Container: `docker exec b1gmail ls -la /var/www/html/plugins/tccrn.plugin.php`
- PHP-Syntax-Fehler: `docker exec b1gmail php -l /var/www/html/plugins/tccrn.plugin.php`

**Lösung:**
```bash
# Container neu starten
docker-compose restart b1gmail

# Plugin-Status prüfen
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
```

---

### **Problem: Tabellen fehlen**

**Symptom:** Fehler beim Öffnen der Admin-Seite

**Check:**
```bash
docker exec b1gmail mysql -u b1gmail -pb1gmail_password b1gmail --skip-ssl \
  -e "SHOW TABLES LIKE 'bm60_tccrn%';"
```

**Lösung:** Installation erneut ausführen
```bash
docker exec b1gmail php /var/www/html/install-clevercron.php
```

---

### **Problem: Cron wird nicht ausgeführt**

**Ursachen:**
- Cron-Daemon nicht aktiv
- Zeitplan-Syntax falsch
- PHP-Fehler im Job-Code

**Debug:**
```bash
# Logs prüfen
docker exec b1gmail tail -50 /var/log/apache2/error.log | grep tccrn

# Cron manuell ausführen
docker exec b1gmail php /var/www/html/cron.php
```

---

## ✅ Test-Abschluss

**Nach erfolgreicher Durchführung:**

1. **Dokumentation aktualisieren:**
   - `docs/COMPLETED_TASKS_2025-12-09.md` → Test-Ergebnisse eintragen
   - Screenshots in `docs/screenshots/clevercron/` ablegen

2. **Status-Update:**
   ```
   CleverCron: ✅ Aktiviert & Getestet
   - Admin-UI: ✅ Funktionsfähig
   - Job-Erstellung: ✅ Erfolgreich
   - Job-Ausführung: ✅ Erfolgreich
   - Logging: ✅ Funktioniert
   ```

3. **Nächster Schritt:**
   - CleverTimeZone aktivieren (Woche 3)
   - Produktive Cron-Jobs einrichten (z.B. Email-Queue, Log-Rotation)

---

## 📋 Produktive Cron-Jobs (Empfehlungen)

Nach erfolgreichen Tests kannst du folgende produktive Jobs einrichten:

| Job-Name | Aufgabe | Zeitplan | Prio |
|----------|---------|----------|------|
| Email Queue Processor | Verarbeitet ausgehende Emails | */2 * * * * | 🔴 Hoch |
| Log Rotation | Rotiert/löscht alte Logs | 0 2 * * * | 🟡 Mittel |
| Session Cleanup | Löscht abgelaufene Sessions | 0 */4 * * * | 🟡 Mittel |
| Backup Check | Prüft Backup-Status | 0 6 * * * | 🟢 Niedrig |
| Analytics Aggregation | Aggregiert Analytics-Daten | 0 3 * * * | 🟢 Niedrig |

---

**Erstellt am:** 2025-12-09 13:24 Uhr  
**Autor:** Windsurf AI  
**Basis:** CleverCron v1.3.0 Plugin-API  
**Status:** Ready for User Testing

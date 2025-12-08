# 🎣 Git Pre-Commit Hook Setup

**Automatische Code-Verifikation vor jedem Commit**

---

## ✅ Was der Hook macht

Der Pre-Commit Hook führt **automatisch vor jedem Git-Commit** folgende Checks durch:

1. **Container-Status:** Prüft ob `b1gmail` Container läuft
2. **Code-Sync:** Führt `verify-sync.sh` aus → Host = Container?
3. **Plugin-Status:** Führt `check-plugin-status.sh` aus → Dokumentation korrekt?

**Bei Erfolg:** ✅ Commit wird durchgeführt  
**Bei Fehler:** ❌ Commit wird blockiert + Fehlermeldung

---

## 📦 Installation

### Schritt 1: Hook installieren

```bash
# Im Projekt-Root:
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit

# Linux/Mac: Executable-Rechte setzen
chmod +x .git/hooks/pre-commit

# Windows: Git Bash erkennt Shebang automatisch (kein chmod nötig)
```

### Schritt 2: Container starten

```bash
# Hook benötigt laufenden Container:
docker-compose up -d
```

### Schritt 3: Testen

```bash
# Test-Commit:
echo "# Test" > test.txt
git add test.txt
git commit -m "Test pre-commit hook"

# Hook läuft automatisch:
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
#   b1gMail Pre-Commit Verification
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# 
# 🔍 Prüfe Container-Status...
# ✅ Container läuft
# 
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# 1/2  Code-Sync Verification
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# ✅ Code-Sync: OK
# 
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# 2/2  Plugin-Status Verification
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# ✅ Plugin-Status: OK
# 
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# ✅ ALLE CHECKS BESTANDEN
# ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
# Commit wird durchgeführt...

# Aufräumen:
git reset HEAD~1
rm test.txt
```

---

## 🔧 Verwendung

### Normal-Fall (alles OK)

```bash
# Code ändern:
vi src/admin/welcome.php

# Committen:
git add src/admin/welcome.php
git commit -m "Admin Welcome aktualisiert"

# → Hook läuft automatisch
# → Alle Checks ✅
# → Commit erfolgreich
```

### Fehler-Fall 1: Code nicht synchron

```bash
# Szenario: Dateien direkt im Container geändert
docker exec b1gmail vi /var/www/html/admin/test.php

# Commit versuchen:
git commit -m "Update"

# → Hook blockiert:
# ❌ ERROR: verify-sync.sh failed!
# Container ↔ Host sind NICHT synchron!
# 
# COMMIT ABGEBROCHEN

# Lösung:
docker-compose restart b1gmail
# Oder: Änderung auf Host machen statt im Container
```

### Fehler-Fall 2: Plugin-Status inkorrekt

```bash
# Szenario: Plugin hinzugefügt aber nicht dokumentiert
cp new-plugin.php src/plugins/

# Commit versuchen:
git add src/plugins/new-plugin.php
git commit -m "New plugin"

# → Hook blockiert:
# ❌ ERROR: check-plugin-status.sh failed!
# Plugin-Status und Dokumentation sind INKONSISTENT!
# 
# COMMIT ABGEBROCHEN

# Lösung:
vi docs/plugins-status.md
# → Plugin in Tabelle hinzufügen
git add docs/plugins-status.md
git commit -m "New plugin + Dokumentation"
# → ✅ Commit erfolgreich
```

---

## 🚫 Hook überspringen (NICHT EMPFOHLEN!)

```bash
# In Notfällen Hook überspringen:
git commit --no-verify -m "Urgent hotfix"

# ⚠️ WARNUNG: Umgeht alle Sicherheitschecks!
# Nur in echten Notfällen verwenden!
```

---

## 🔍 Hook debuggen

### Hook manuell ausführen

```bash
# Ohne Commit, nur Hook testen:
.git/hooks/pre-commit

# Oder im Container:
docker exec b1gmail bash /var/www/html/tools/verify-sync.sh
docker exec b1gmail bash /var/www/html/tools/check-plugin-status.sh
```

### Hook-Ausgabe sehen

```bash
# Verbose Commit (zeigt Hook-Output):
git commit -v -m "Test"
```

### Hook deaktivieren (temporär)

```bash
# Hook umbenennen:
mv .git/hooks/pre-commit .git/hooks/pre-commit.disabled

# Später wieder aktivieren:
mv .git/hooks/pre-commit.disabled .git/hooks/pre-commit
```

---

## 📊 Hook-Verhalten

### Erfolgs-Szenario

```
Commit → Hook Start
           ↓
        Container läuft? → ✅ Ja
           ↓
        verify-sync.sh → ✅ Exit 0
           ↓
        check-plugin-status.sh → ✅ Exit 0
           ↓
        Hook Exit 0 → Commit durchführen
```

### Fehler-Szenario

```
Commit → Hook Start
           ↓
        Container läuft? → ✅ Ja
           ↓
        verify-sync.sh → ❌ Exit 1
           ↓
        Hook Exit 1 → Commit ABGEBROCHEN
```

---

## 🔄 Hook aktualisieren

```bash
# Template wurde im Repo aktualisiert?
# Neu installieren:
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit  # Linux/Mac

# Oder: Template editieren und neu kopieren
vi tools/git-pre-commit-template.sh
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit
```

---

## 👥 Team-Setup

### Hook für alle Team-Mitglieder

```bash
# Im Projekt-README dokumentieren:
# "Nach Git-Clone ausführen:"
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit

# Optional: Setup-Script erstellen
# tools/setup-git-hooks.sh:
#!/bin/bash
cp tools/git-pre-commit-template.sh .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
echo "✅ Git hooks installiert"
```

### Warum kein `.git/hooks/` im Repo?

Git ignoriert `.git/hooks/` im Repository (by design).  
**Deshalb:** Template in `tools/` + Installations-Anleitung

---

## 📝 Best Practices

### DO ✅

- Hook nach jedem `git clone` installieren
- Hook-Template im Repo updaten bei Änderungen
- Hook-Fehler beheben statt überspringen
- Dokumentation in `plugins-status.md` pflegen

### DON'T ❌

- Hook nicht mit `--no-verify` umgehen (außer Notfall)
- Code nicht direkt im Container ändern
- Plugins nicht ohne Dokumentation hinzufügen
- Container nicht stoppen während Entwicklung

---

## 🎯 Vorteile

### Ohne Hook

```
Developer committet Code
  ↓
Plugin fehlt in Doku? → ⚠️ Nicht bemerkt
  ↓
Code-Sync kaputt? → ⚠️ Nicht bemerkt
  ↓
Push zu Git
  ↓
Andere Developer pullen
  ↓
❌ Probleme!
```

### Mit Hook

```
Developer committet Code
  ↓
Hook prüft automatisch
  ↓
Plugin fehlt in Doku? → ❌ Commit blockiert!
  ↓
Developer fixt Doku
  ↓
Hook prüft erneut
  ↓
Alles OK? → ✅ Commit erfolgreich
  ↓
Push zu Git
  ↓
Andere Developer pullen
  ↓
✅ Alles synchron!
```

---

## 📚 Referenzen

- **Template:** `tools/git-pre-commit-template.sh`
- **Dokumentation:** `VERIFIKATIONS_SYSTEM.md`
- **Verifikations-Scripts:**
  - `tools/verify-sync.sh`
  - `tools/check-plugin-status.sh`
- **Plugin-Status:** `docs/plugins-status.md`

---

## ❓ FAQ

### Q: Hook läuft nicht?
**A:** Prüfe:
```bash
# Hook existiert?
ls -la .git/hooks/pre-commit

# Hook ist Bash-Script?
head .git/hooks/pre-commit

# Container läuft?
docker ps | grep b1gmail
```

### Q: Hook dauert zu lange?
**A:** Normal! Hook führt vollständige MD5-Verifikation durch (~5-10 Sekunden).  
Schnellere Alternative: Hook anpassen, nur Struktur-Check ohne MD5.

### Q: Hook in CI/CD nutzen?
**A:** Ja! Script kann direkt aufgerufen werden:
```yaml
# GitHub Actions:
- run: bash tools/git-pre-commit-template.sh
```

### Q: Hook deaktivieren für bestimmte Branches?
**A:** Hook anpassen:
```bash
# In pre-commit Hook:
BRANCH=$(git rev-parse --abbrev-ref HEAD)
if [ "$BRANCH" = "experimental" ]; then
  echo "⚠️ Skipping checks on experimental branch"
  exit 0
fi
```

---

**Erstellt:** 2025-12-08  
**Autor:** Windsurf AI + Karsten  
**Status:** ✅ Produktiv

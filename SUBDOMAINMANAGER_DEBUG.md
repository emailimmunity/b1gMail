# 🔧 SubdomainManager Plugin - Debug System

**Branch:** `tech-debt/subdomainmanager`  
**Status:** Ready for isolated debugging  
**Created:** 2025-12-08 21:30  

---

## 📋 Overview

This document describes the isolated debug environment for the SubdomainManager plugin, which was previously causing HTTP 500 errors in the admin panel.

---

## 🏗️ Architecture

### Separation of Concerns

```
┌─────────────────────────────────────────┐
│  PRODUCTION ENVIRONMENT                 │
│  - All plugins active                   │
│  - Full b1gMail initialization          │
│  - Used by: Frontend, Admin Panel       │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  HEALTH CHECK ENDPOINT                  │
│  - NO plugins loaded                    │
│  - Minimal initialization               │
│  - Prevents infinite loops              │
│  - URL: /health.php                     │
└─────────────────────────────────────────┘

┌─────────────────────────────────────────┐
│  DEBUG SANDBOX                          │
│  - SubdomainManager ONLY                │
│  - Isolated from other plugins          │
│  - Maximum error visibility             │
│  - URL: /tools/subdomain-debug.php      │
└─────────────────────────────────────────┘
```

---

## 📁 File Structure

```
b1gMail/
├─ src/
│  ├─ health.php                          # Health check (NO plugins)
│  ├─ tools/
│  │  └─ subdomain-debug.php              # Debug sandbox
│  ├─ plugins/                            # Active plugins
│  │  └─ [other plugins]                  # SubdomainManager NOT here
│  ├─ plugins_disabled/                   # Debug staging
│  │  ├─ subdomainmanager.plugin.php      # Main plugin
│  │  ├─ subdomainmanager.dns.helper.php
│  │  ├─ subdomainmanager.emailadmin.helper.php
│  │  └─ subdomainmanager.keyhelp.helper.php
│  └─ plugins_backup/                     # Safe backup
│     └─ [backup copies]
├─ docker-compose.yml                     # Updated with healthcheck
└─ logs/
   └─ subdomain-debug.log                 # Dedicated debug log
```

---

## 🚀 Usage

### 1. Health Check (Always Safe)

```bash
# From host
curl http://localhost:8095/health.php

# Expected output:
OK
status: healthy
php: ok
disk: ok
config: ok
database: ok
```

**Purpose:**
- Used by Docker healthcheck
- No plugins loaded
- Fast response (<100ms)
- Cannot crash due to plugin errors

---

### 2. Debug URL (Isolated Testing)

```bash
# Open in browser
http://localhost:8095/tools/subdomain-debug.php
```

**What it does:**
1. ✅ Environment check (PHP version, paths)
2. ✅ Load core system (autoloader, config)
3. ✅ Test database connection
4. ✅ Locate plugin files
5. ✅ PHP syntax check
6. ✅ Check for compatibility issues
7. ✅ Verify database schema

**Features:**
- Visual HTML output with color coding
- Step-by-step execution
- Stops at first error
- Logs to `/var/log/b1gmail/subdomain-debug.log`

---

### 3. Manual Testing Workflow

#### Step 1: Verify System is Stable

```bash
# Check healthcheck
docker-compose ps
# b1gmail should show "healthy"

# Test frontend
curl -I http://localhost:8095/
# Should return HTTP 200
```

#### Step 2: Run Debug Session

```bash
# Open debug URL in browser
http://localhost:8095/tools/subdomain-debug.php

# Or via curl for logs
curl http://localhost:8095/tools/subdomain-debug.php > debug-output.html
```

#### Step 3: Review Results

Look for:
- ❌ **Syntax errors** → Fix PHP code
- ⚠️ **Compatibility warnings** → Update deprecated functions
- ⚠️ **Missing tables** → Run database migrations
- ✅ **All green** → Plugin ready for activation

#### Step 4: Check Logs

```bash
# Debug log
docker exec b1gmail tail -100 /var/log/b1gmail/subdomain-debug.log

# Apache error log
docker exec b1gmail tail -100 /var/log/apache2/error.log | grep -i subdomain
```

---

## 🔍 Debugging Common Issues

### Issue 1: "Class not found"

**Symptom:**
```
Fatal error: Class 'SomeClass' not found
```

**Solution:**
- Check `require_once` statements
- Verify helper files are loaded
- Check autoloader paths

---

### Issue 2: "Call to undefined function"

**Symptom:**
```
Fatal error: Call to undefined function mysql_query()
```

**Solution:**
- Replace deprecated `mysql_*` with `mysqli_*`
- Use PDO instead
- Check PHP version compatibility (8.3)

---

### Issue 3: "Table doesn't exist"

**Symptom:**
```
Table 'b1gmail.bm60_subdomains' doesn't exist
```

**Solution:**
1. Check if plugin has SQL schema file
2. Run database migration:
   ```bash
   docker exec b1gmail mysql -ub1gmail -pb1gmail_password b1gmail < schema.sql
   ```
3. Verify table creation in debug output

---

### Issue 4: "HTTP 500 in Admin"

**Symptom:**
- Admin panel loads normally
- Clicking SubdomainManager → blank page or 500

**Debug:**
```bash
# Enable error display
docker exec b1gmail bash -c "echo 'display_errors=On' >> /usr/local/etc/php/conf.d/b1gmail.ini"
docker-compose restart b1gmail

# Try accessing admin subdomain page directly
curl -v http://localhost:8095/admin/?action=subdomain

# Check logs
docker exec b1gmail tail -50 /var/log/apache2/error.log
```

---

## 📊 Docker Healthcheck

### Configuration

```yaml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost/health.php"]
  interval: 30s       # Check every 30 seconds
  timeout: 10s        # Max 10s per check
  retries: 3          # 3 failures = unhealthy
  start_period: 60s   # Grace period after start
```

### Why This Prevents Loops

**OLD (Problematic):**
```
Healthcheck → index.php → Load ALL plugins → SubdomainManager error → 500
→ Retry → Error → Retry → Error → LOOP
```

**NEW (Safe):**
```
Healthcheck → health.php → NO plugins → Quick checks → OK
```

---

## 🛠️ Plugin Activation Checklist

Before activating SubdomainManager in production:

- [ ] ✅ Debug URL shows all green
- [ ] ✅ No syntax errors
- [ ] ✅ No compatibility warnings
- [ ] ✅ All required tables exist
- [ ] ✅ Tested in `plugins_disabled/` first
- [ ] ✅ No errors in logs
- [ ] ✅ Healthcheck remains stable
- [ ] ✅ Admin panel loads without errors
- [ ] ✅ Created backup before activation

---

## 📝 Activation Steps

### Safe Activation Process

```bash
# 1. Verify system is healthy
docker-compose ps
curl http://localhost:8095/health.php

# 2. Run final debug check
curl http://localhost:8095/tools/subdomain-debug.php > final-check.html

# 3. If all green, move to active plugins
mv src/plugins_disabled/subdomainmanager.plugin.php src/plugins/
mv src/plugins_disabled/subdomainmanager.*.helper.php src/plugins/

# 4. Restart container
docker-compose restart b1gmail

# 5. Wait for healthy status
watch docker-compose ps

# 6. Test admin panel
curl -I http://localhost:8095/admin/
```

### Rollback (If Issues)

```bash
# Quick rollback
mv src/plugins/subdomainmanager* src/plugins_disabled/
docker-compose restart b1gmail

# Verify health restored
curl http://localhost:8095/health.php
```

---

## 📈 Monitoring

### Log Files

| Log File | Purpose | Command |
|----------|---------|---------|
| `subdomain-debug.log` | Debug session output | `docker exec b1gmail tail -f /var/log/b1gmail/subdomain-debug.log` |
| `error.log` | Apache errors | `docker exec b1gmail tail -f /var/log/apache2/error.log` |
| `access.log` | HTTP requests | `docker exec b1gmail tail -f /var/log/apache2/access.log` |

### Real-time Monitoring

```bash
# Watch healthcheck status
watch -n 5 'docker-compose ps | grep b1gmail'

# Monitor logs during testing
docker exec b1gmail tail -f /var/log/apache2/error.log | grep -i subdomain
```

---

## 🔐 Security Notes

1. **Debug URL is PUBLIC** - Remove in production or add auth
2. **Logs may contain sensitive data** - Review before sharing
3. **plugins_disabled/** is still accessible via web - add `.htaccess` deny

### Production Security

```apache
# Add to src/plugins_disabled/.htaccess
Order deny,allow
Deny from all
```

---

## 📚 Related Documentation

- [ROADMAP.md](./ROADMAP.md) - Technical debt overview
- [docs/plugins-status.md](./docs/plugins-status.md) - Plugin inventory
- [VERIFIKATIONS_SYSTEM.md](./VERIFIKATIONS_SYSTEM.md) - Testing procedures

---

## 🎯 Success Criteria

Plugin is considered **fixed** when:

1. ✅ Debug URL shows no errors
2. ✅ Admin panel loads SubdomainManager page
3. ✅ No HTTP 500 errors
4. ✅ Healthcheck remains green during operation
5. ✅ No infinite loops or crashes
6. ✅ Plugin functionality works as expected

---

## 📞 Troubleshooting Contact

If you encounter issues not covered here:

1. Check logs first
2. Run debug URL
3. Review error messages
4. Create issue with:
   - Debug URL output
   - Relevant log excerpts
   - Steps to reproduce

---

**Last Updated:** 2025-12-08 21:30  
**Branch:** tech-debt/subdomainmanager  
**Status:** Documentation Complete ✅

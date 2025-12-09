<?php
/**
 * Branding Configuration API
 * 
 * Zentrale API für Domain-spezifisches Branding.
 * Integriert CleverBranding Plugin als Config-Layer ohne Template-Überschreibungen.
 * 
 * @package    b1gMail
 * @subpackage Branding
 * @author     b1gMail Project
 * @version    1.0.0
 * @since      2025-12-09
 */

if (!defined('B1GMAIL_INIT')) {
    die('Prohibited');
}

/**
 * Liefert Branding-Konfiguration für eine gegebene Domain.
 * 
 * Auflösungs-Reihenfolge:
 * 1. Exakte Domain (z.B. "mail.example.com")
 * 2. Basisdomain (z.B. "example.com")
 * 3. Fallback-Profil (z.B. "default")
 * 4. Statisches Default-Branding
 *
 * @param string      $domain   Domain (z.B. "mail.example.com")
 * @param string|null $fallback Fallback-Profil-Name oder null
 * @return array                Assoziatives Array mit Branding-Infos
 * 
 * @example
 * $branding = GetBrandingForDomain($_SERVER['HTTP_HOST']);
 * echo $branding['logo_url'];
 */
function GetBrandingForDomain(string $domain, ?string $fallback = 'default'): array
{
    // Domain normalisieren (Kleinbuchstaben, Port entfernen)
    $normalized = NormalizeDomain($domain);
    
    // 1. Exakte Domain im CleverBranding suchen
    $branding = LookupBrandingProfile($normalized);
    
    // 2. Basisdomain versuchen (z.B. example.com statt mail.example.com)
    if (!$branding) {
        $baseDomain = ExtractBaseDomain($normalized);
        if ($baseDomain !== $normalized) {
            $branding = LookupBrandingProfile($baseDomain);
        }
    }
    
    // 3. Fallback-Profil
    if (!$branding && $fallback) {
        $branding = LookupBrandingProfile($fallback);
    }
    
    // 4. Statisches Default-Branding
    if (!$branding) {
        $branding = GetDefaultBranding();
        $branding['is_default'] = true;
    } else {
        $branding['is_default'] = false;
    }
    
    // Domain setzen
    $branding['domain'] = $normalized;
    
    return $branding;
}

/**
 * Normalisiert einen Domain-Namen.
 * 
 * - Konvertiert zu Kleinbuchstaben
 * - Entfernt Port-Nummer
 * - Entfernt führende/nachfolgende Whitespaces
 *
 * @param string $domain Roher Domain-String
 * @return string        Normalisierter Domain-String
 */
function NormalizeDomain(string $domain): string
{
    // Whitespaces entfernen
    $domain = trim($domain);
    
    // Kleinbuchstaben
    $domain = strtolower($domain);
    
    // Port entfernen (z.B. "localhost:8095" -> "localhost")
    if (strpos($domain, ':') !== false) {
        $parts = explode(':', $domain);
        $domain = $parts[0];
    }
    
    return $domain;
}

/**
 * Extrahiert die Basisdomain aus einem FQDN.
 * 
 * Beispiele:
 * - "mail.example.com" -> "example.com"
 * - "sub.mail.example.com" -> "example.com"
 * - "localhost" -> "localhost"
 * - "example.com" -> "example.com"
 *
 * @param string $domain FQDN
 * @return string        Basisdomain
 */
function ExtractBaseDomain(string $domain): string
{
    // Spezialfälle: localhost, IP-Adressen
    if ($domain === 'localhost' || filter_var($domain, FILTER_VALIDATE_IP)) {
        return $domain;
    }
    
    // Domain in Teile zerlegen
    $parts = explode('.', $domain);
    $count = count($parts);
    
    // Wenn nur 1 oder 2 Teile: bereits Basisdomain
    if ($count <= 2) {
        return $domain;
    }
    
    // Letzte 2 Teile nehmen (z.B. "example.com")
    return $parts[$count - 2] . '.' . $parts[$count - 1];
}

/**
 * Sucht Branding-Profil in der Datenbank (CleverBranding).
 * 
 * @param string $domain Domain oder Profil-Name
 * @return array|null    Branding-Daten oder null wenn nicht gefunden
 */
function LookupBrandingProfile(string $domain): ?array
{
    global $db;
    
    // Prüfen ob tcbrn_plugin_domains Tabelle existiert
    $res = $db->Query("SHOW TABLES LIKE '{pre}tcbrn_plugin_domains'");
    if ($res->RowCount() == 0) {
        // CleverBranding nicht installiert
        return null;
    }
    
    // Domain in Datenbank suchen
    $res = $db->Query("SELECT * FROM {pre}tcbrn_plugin_domains WHERE `domain` = ? AND `active` = 1",
        $domain
    );
    
    if ($res->RowCount() == 0) {
        return null;
    }
    
    $row = $res->FetchArray(MYSQLI_ASSOC);
    
    // CleverBranding v1.3.1 Schema: domainid, domain, title, language, country, xmailer, template
    // Keine Farben/Logos direkt in DB -> Template-basiert
    
    // Branding-Array aufbauen (mit Defaults für fehlende Felder)
    return [
        'profile_id'      => (int)$row['domainid'],
        'name'            => $row['title'] ?: $domain,
        'logo_url'        => '/branding/' . $domain . '/logo.png',  // Template-basiert
        'favicon_url'     => '/branding/' . $domain . '/favicon.ico',
        'primary_color'   => '#004080',  // Default (muss vom Template kommen)
        'secondary_color' => '#0080ff',
        'accent_color'    => '#00aa55',
        'background'      => '#ffffff',
        'css_class'       => 'branding-' . preg_replace('/[^a-z0-9]+/', '-', $domain),
        'footer_text'     => '© ' . date('Y') . ' ' . ($row['title'] ?: $domain),
        'login_title'     => 'Willkommen bei ' . ($row['title'] ?: $domain),
        'language'        => $row['language'] ?: 'de',
        'country'         => GetCountryCode($row['country']),
        'xmailer'         => $row['xmailer'] ?: 'b1gMail',
        'template'        => $row['template'] ?: null,
    ];
}

/**
 * Konvertiert Country-ID zu ISO-Code.
 * 
 * CleverBranding speichert Länder als INT (z.B. 25 = Deutschland).
 * Diese Funktion mapped INT -> ISO 3166-1 alpha-2.
 * 
 * @param int|string|null $countryId Country-ID oder ISO-Code
 * @return string                    ISO 3166-1 alpha-2 Code
 */
function GetCountryCode($countryId): string
{
    // Wenn bereits String: direkt zurückgeben
    if (is_string($countryId) && strlen($countryId) == 2) {
        return strtolower($countryId);
    }
    
    // Mapping von CleverBranding IDs zu ISO-Codes
    // (Aus tcbrn.plugin.php Zeile 63-74)
    $countryMap = [
        89  => 'at',  // Österreich
        105 => 'ch',  // Schweiz
        22  => 'cn',  // China
        25  => 'de',  // Deutschland
        112 => 'es',  // Spanien
        32  => 'fr',  // Frankreich
        48  => 'jp',  // Japan
        85  => 'nl',  // Niederlande
        101 => 'ru',  // Russland
        104 => 'se',  // Schweden
        37  => 'uk',  // UK
        133 => 'us',  // USA
    ];
    
    return $countryMap[$countryId] ?? 'de';  // Default: Deutschland
}

/**
 * Leitet Favicon-URL vom Logo ab.
 * 
 * @param string|null $logoUrl Logo-URL
 * @return string              Favicon-URL
 */
function GetFaviconFromLogo(?string $logoUrl): string
{
    if (!$logoUrl) {
        return '/branding/default/favicon.ico';
    }
    
    // Versuche favicon.ico im gleichen Verzeichnis
    $dir = dirname($logoUrl);
    return $dir . '/favicon.ico';
}

/**
 * Liefert statisches Default-Branding.
 * 
 * Wird verwendet wenn:
 * - CleverBranding nicht installiert ist
 * - Keine Branding-Profile in DB vorhanden
 * - Domain nicht gefunden
 *
 * @return array Default-Branding-Array
 */
function GetDefaultBranding(): array
{
    global $bm_prefs;
    
    return [
        'profile_id'      => null,
        'name'            => 'b1gMail',
        'logo_url'        => '/images/b1gmail_logo.png',
        'favicon_url'     => '/favicon.ico',
        'primary_color'   => '#004080',
        'secondary_color' => '#0080ff',
        'accent_color'    => '#00aa55',
        'background'      => '#ffffff',
        'css_class'       => 'branding-default',
        'footer_text'     => '© ' . date('Y') . ' b1gMail',
        'login_title'     => 'Willkommen bei b1gMail',
        'language'        => $bm_prefs['language'] ?? 'de',
        'country'         => 'de',
        'xmailer'         => 'b1gMail',
    ];
}

/**
 * Liefert alle verfügbaren Branding-Profile.
 * 
 * Nützlich für Admin-Übersichten und Debugging.
 *
 * @return array Array von Branding-Profilen
 */
function GetAllBrandingProfiles(): array
{
    global $db;
    
    // Prüfen ob Tabelle existiert
    $res = $db->Query("SHOW TABLES LIKE '{pre}tcbrn_plugin_domains'");
    if ($res->RowCount() == 0) {
        return [];
    }
    
    $profiles = [];
    $res = $db->Query("SELECT * FROM {pre}tcbrn_plugin_domains ORDER BY `domain`");
    
    while ($row = $res->FetchArray(MYSQLI_ASSOC)) {
        $profiles[] = [
            'profile_id'   => (int)$row['domainid'],
            'domain'       => $row['domain'],
            'name'         => $row['title'],
            'language'     => $row['language'],
            'country'      => GetCountryCode($row['country']),
            'active'       => (bool)$row['active'],
        ];
    }
    
    return $profiles;
}

/**
 * Prüft ob CleverBranding-Plugin aktiv ist.
 *
 * @return bool True wenn aktiv, sonst false
 */
function IsBrandingPluginActive(): bool
{
    global $plugins;
    
    if (!is_array($plugins)) {
        return false;
    }
    
    foreach ($plugins as $plugin) {
        if (is_object($plugin) && isset($plugin->name) && $plugin->name === 'CleverBranding') {
            return true;
        }
    }
    
    return false;
}

/**
 * Generiert CSS-Variablen-String aus Branding-Daten.
 * 
 * Kann direkt in <style> Tag oder CSS-File geschrieben werden.
 *
 * @param array $branding Branding-Daten von GetBrandingForDomain()
 * @return string         CSS mit CSS Custom Properties
 */
function GenerateBrandingCSS(array $branding): string
{
    $css = ":root {\n";
    $css .= "  /* Branding für: " . htmlspecialchars($branding['domain']) . " */\n";
    $css .= "  --brand-primary: " . htmlspecialchars($branding['primary_color']) . ";\n";
    $css .= "  --brand-secondary: " . htmlspecialchars($branding['secondary_color']) . ";\n";
    $css .= "  --brand-accent: " . htmlspecialchars($branding['accent_color']) . ";\n";
    $css .= "  --brand-background: " . htmlspecialchars($branding['background']) . ";\n";
    $css .= "  --brand-name: '" . htmlspecialchars($branding['name']) . "';\n";
    $css .= "}\n\n";
    
    // Body-Klasse für Domain-spezifisches Styling
    $css .= "body." . htmlspecialchars($branding['css_class']) . " {\n";
    $css .= "  background-color: var(--brand-background);\n";
    $css .= "}\n";
    
    return $css;
}

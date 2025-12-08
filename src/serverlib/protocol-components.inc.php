<?php
/**
 * Protocol Component Definitions
 * Erweiterte Component-Flags für neue Protokolle
 * 
 * Datum: 31. Oktober 2025
 */

// ============================================================================
// LEGACY COMPONENTS (behalten für Backwards-Kompatibilität)
// ============================================================================
if(!defined('BMS_CMP_CORE'))       define('BMS_CMP_CORE',       1);
if(!defined('BMS_CMP_POP3'))       define('BMS_CMP_POP3',       2);      // LEGACY
if(!defined('BMS_CMP_IMAP'))       define('BMS_CMP_IMAP',       4);      // LEGACY
if(!defined('BMS_CMP_HTTP'))       define('BMS_CMP_HTTP',       8);
if(!defined('BMS_CMP_FTP'))        define('BMS_CMP_FTP',        9);
if(!defined('BMS_CMP_SMTP'))       define('BMS_CMP_SMTP',       16);     // LEGACY
if(!defined('BMS_CMP_MSGQUEUE'))   define('BMS_CMP_MSGQUEUE',   32);
if(!defined('BMS_CMP_PLUGIN'))     define('BMS_CMP_PLUGIN',     64);
if(!defined('BMS_CMP_CALDAV'))     define('BMS_CMP_CALDAV',     128);

// ============================================================================
// NEUE PROTOCOL COMPONENTS
// ============================================================================

// Cyrus IMAP Server
define('BMS_CMP_CYRUS_IMAP',     256);    // 2^8  - Cyrus IMAP
define('BMS_CMP_CYRUS_POP3',     512);    // 2^9  - Cyrus POP3
define('BMS_CMP_CYRUS_JMAP',     1024);   // 2^10 - Cyrus JMAP
define('BMS_CMP_CYRUS_SIEVE',    2048);   // 2^11 - Cyrus Sieve

// Grommunio
define('BMS_CMP_GROMMUNIO_MAPI', 4096);   // 2^12 - Grommunio MAPI
define('BMS_CMP_GROMMUNIO_EWS',  8192);   // 2^13 - Grommunio EWS
define('BMS_CMP_GROMMUNIO_EAS',  16384);  // 2^14 - Grommunio ActiveSync
define('BMS_CMP_GROMMUNIO_API',  32768);  // 2^15 - Grommunio Admin API

// SFTPGo
define('BMS_CMP_SFTPGO_SFTP',    65536);  // 2^16 - SFTPGo SFTP
define('BMS_CMP_SFTPGO_FTPS',    131072); // 2^17 - SFTPGo FTPS
define('BMS_CMP_SFTPGO_S3',      262144); // 2^18 - SFTPGo S3
define('BMS_CMP_SFTPGO_WEBDAV',  524288); // 2^19 - SFTPGo WebDAV

// Postfix
define('BMS_CMP_POSTFIX_SMTP',   1048576); // 2^20 - Postfix SMTP
define('BMS_CMP_POSTFIX_SMTPS',  2097152); // 2^21 - Postfix SMTPS
define('BMS_CMP_POSTFIX_SUBMISSION', 4194304); // 2^22 - Postfix Submission

// ============================================================================
// FAIL-BAN TYPE FLAGS (erweitert)
// ============================================================================

// Legacy Fail-Ban Types
if(!defined('BMS_FB_POP3'))    define('BMS_FB_POP3',    1);   // 2^0 - Legacy POP3
if(!defined('BMS_FB_IMAP'))    define('BMS_FB_IMAP',    2);   // 2^1 - Legacy IMAP
if(!defined('BMS_FB_SMTP'))    define('BMS_FB_SMTP',    4);   // 2^2 - Legacy SMTP
if(!defined('BMS_FB_RCPT'))    define('BMS_FB_RCPT',    8);   // 2^3 - SMTP RCPT
if(!defined('BMS_FB_FTP'))     define('BMS_FB_FTP',     16);  // 2^4 - FTP

// Neue Fail-Ban Types
define('BMS_FB_CYRUS_IMAP',     32);   // 2^5 - Cyrus IMAP Login
define('BMS_FB_CYRUS_POP3',     64);   // 2^6 - Cyrus POP3 Login
define('BMS_FB_GROMMUNIO_MAPI', 128);  // 2^7 - Grommunio MAPI Login
define('BMS_FB_GROMMUNIO_EWS',  256);  // 2^8 - Grommunio EWS Login
define('BMS_FB_GROMMUNIO_EAS',  512);  // 2^9 - Grommunio EAS Login
define('BMS_FB_SFTPGO_SFTP',    1024); // 2^10 - SFTPGo SFTP Login
define('BMS_FB_SFTPGO_FTPS',    2048); // 2^11 - SFTPGo FTPS Login
define('BMS_FB_POSTFIX_SMTP',   4096); // 2^12 - Postfix SMTP Auth

// ============================================================================
// COMPONENT NAME MAPPING
// ============================================================================

/**
 * Get component name by ID
 */
function getComponentName($componentID)
{
    static $componentNames = null;
    
    if($componentNames === null)
    {
        $componentNames = array(
            // Legacy
            BMS_CMP_CORE       => 'Core',
            BMS_CMP_POP3       => 'POP3 (Legacy)',
            BMS_CMP_IMAP       => 'IMAP (Legacy)',
            BMS_CMP_HTTP       => 'HTTP',
            BMS_CMP_FTP        => 'FTP',
            BMS_CMP_SMTP       => 'SMTP (Legacy)',
            BMS_CMP_MSGQUEUE   => 'Message Queue',
            BMS_CMP_PLUGIN     => 'Plugin',
            BMS_CMP_CALDAV     => 'CalDAV',
            
            // Cyrus
            BMS_CMP_CYRUS_IMAP  => 'Cyrus IMAP',
            BMS_CMP_CYRUS_POP3  => 'Cyrus POP3',
            BMS_CMP_CYRUS_JMAP  => 'Cyrus JMAP',
            BMS_CMP_CYRUS_SIEVE => 'Cyrus Sieve',
            
            // Grommunio
            BMS_CMP_GROMMUNIO_MAPI => 'Grommunio MAPI',
            BMS_CMP_GROMMUNIO_EWS  => 'Grommunio EWS',
            BMS_CMP_GROMMUNIO_EAS  => 'Grommunio ActiveSync',
            BMS_CMP_GROMMUNIO_API  => 'Grommunio API',
            
            // SFTPGo
            BMS_CMP_SFTPGO_SFTP   => 'SFTPGo SFTP',
            BMS_CMP_SFTPGO_FTPS   => 'SFTPGo FTPS',
            BMS_CMP_SFTPGO_S3     => 'SFTPGo S3',
            BMS_CMP_SFTPGO_WEBDAV => 'SFTPGo WebDAV',
            
            // Postfix
            BMS_CMP_POSTFIX_SMTP       => 'Postfix SMTP',
            BMS_CMP_POSTFIX_SMTPS      => 'Postfix SMTPS',
            BMS_CMP_POSTFIX_SUBMISSION => 'Postfix Submission'
        );
    }
    
    return isset($componentNames[$componentID]) ? $componentNames[$componentID] : 'Unknown';
}

/**
 * Get Fail-Ban type name by bit
 */
function getFailBanTypeName($typeBit)
{
    static $typeNames = null;
    
    if($typeNames === null)
    {
        $typeNames = array(
            // Legacy
            BMS_FB_POP3    => 'POP3 Login (Legacy)',
            BMS_FB_IMAP    => 'IMAP Login (Legacy)',
            BMS_FB_SMTP    => 'SMTP Auth (Legacy)',
            BMS_FB_RCPT    => 'SMTP RCPT',
            BMS_FB_FTP     => 'FTP Login',
            
            // Neu
            BMS_FB_CYRUS_IMAP     => 'Cyrus IMAP Login',
            BMS_FB_CYRUS_POP3     => 'Cyrus POP3 Login',
            BMS_FB_GROMMUNIO_MAPI => 'Grommunio MAPI Login',
            BMS_FB_GROMMUNIO_EWS  => 'Grommunio EWS Login',
            BMS_FB_GROMMUNIO_EAS  => 'Grommunio EAS Login',
            BMS_FB_SFTPGO_SFTP    => 'SFTPGo SFTP Login',
            BMS_FB_SFTPGO_FTPS    => 'SFTPGo FTPS Login',
            BMS_FB_POSTFIX_SMTP   => 'Postfix SMTP Auth'
        );
    }
    
    return isset($typeNames[$typeBit]) ? $typeNames[$typeBit] : 'Unknown';
}

/**
 * Get all active protocol components (non-legacy)
 */
function getActiveProtocolComponents()
{
    return array(
        'cyrus' => array(
            BMS_CMP_CYRUS_IMAP,
            BMS_CMP_CYRUS_POP3,
            BMS_CMP_CYRUS_JMAP,
            BMS_CMP_CYRUS_SIEVE
        ),
        'grommunio' => array(
            BMS_CMP_GROMMUNIO_MAPI,
            BMS_CMP_GROMMUNIO_EWS,
            BMS_CMP_GROMMUNIO_EAS,
            BMS_CMP_GROMMUNIO_API
        ),
        'sftpgo' => array(
            BMS_CMP_SFTPGO_SFTP,
            BMS_CMP_SFTPGO_FTPS,
            BMS_CMP_SFTPGO_S3,
            BMS_CMP_SFTPGO_WEBDAV
        ),
        'postfix' => array(
            BMS_CMP_POSTFIX_SMTP,
            BMS_CMP_POSTFIX_SMTPS,
            BMS_CMP_POSTFIX_SUBMISSION
        )
    );
}

/**
 * Check if component is legacy
 */
function isLegacyComponent($componentID)
{
    $legacyComponents = array(
        BMS_CMP_POP3,
        BMS_CMP_IMAP,
        BMS_CMP_SMTP
    );
    
    return in_array($componentID, $legacyComponents);
}

?>

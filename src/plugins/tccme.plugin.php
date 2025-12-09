<?php
/**
 * TCMailEncryptionPlugin
 * 
 * @link     http://www.thinkclever.ch/
 * @copyright  2007-2010 ThinkClever IT Solutions
 * @version  $Id: tccme.plugin.php 99 2011-11-14 08:52:30Z caspar $
 */

/**
 * TCMailEncryptionPlugin
 *
 */
class TCMailEncryptionPlugin extends BMPlugin {
  
  function TCMailEncryptionPlugin() {
    $this->name = 'CleverMailEncryption';
    $this->author = 'ThinkClever GmbH';
    $this->web = 'http://www.thinkclever.ch/';
    $this->mail = 'info@thinkclever.ch';
    $this->version = '1.1.0';
    $this->designedfor = '7.3.0';
    $this->type = BMPLUGIN_DEFAULT;
    $this->order = 100;
    
    $this->admin_pages = true;
    $this->admin_page_title = $this->name;
    $this->admin_page_icon = 'tccme_icon48.png';
    
    $this->RegisterGroupOption('tccme_eingeschaltet', FIELD_CHECKBOX, $this->name . '?');
    
    $this->website = 'http://my.b1gmail.com/details/105/';
    $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
  }
  
  function Install() {
    global $db, $mysql;
    $databaseStructure = // Tables: 3; Hash: ba54333ef3addca3d20566dfae1b41cd
        'rZPBbsIwDIZfZcppuzVtKcg97ThpsAlph52qqPVKtBBYk0ibUN99TkGohcBWiUvVOI4/+7ctIIG'
      . 'dgTgGtts22NqyXGOxVa6WulgLqVguIPYuGbAPiaoyB4uEiH6y/Y+BKTDvXsiK5RI4WTi9kNrec/'
      . '5w57SRtcbuLqY7wi1e/CGhQwLsdfnkT2m+oO+EbBEwlrc+0JDhDDY9xvSIuBR5/vYcitwe3lb4j'
      . 'eZY47TLZP64fPcmHqqtHaYx9DqaWw+Ik5CoXw4dhlXNzlSlam4kKE+BCWc3hdRlg2vU9j/6jkFe'
      . 'UNo79xicrJWwWIiqwvGcLmYIkgwgk33HTLFtNiUac0tS2ifNfP+0NKsegRyt1D8d5e+5pAfAInY'
      . 'CmfQhlLLalJ/437G/kPrZzCdXZv4wee1JiT2PgX3MWmShtTBoSbTahDeDn22G76MpV8r57iolUN'
      . 'c4Vh4DtBRxlM7YdZ0i2FHmvw==';
    $databaseStructure = unserialize(gzinflate(base64_decode($databaseStructure)));
    $structure = array();
    foreach ($databaseStructure as $tableName => $data) {
      $tableName = str_replace('{pre}', $mysql['prefix'], $tableName);
      $structure[$tableName] = $data;
    }
    SyncDBStruct($structure);
    
    // prefs row?
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tccme_plugin_settings');
    list ($rowCount) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    
    // insert prefs row
    if ($rowCount < 1) {
      $db->Query("INSERT INTO `{pre}tccme_plugin_settings` () VALUES ()");
    }
    
    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', 'TCMailEncryptionPlugin', '_TCMailEncryptionPlugin');
    
    $this->_cleanUpMails();
    
    // log
    PutLog(sprintf('%s v%s installed', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    
    return true;
  }
  
  function Uninstall() {
    global $db;
    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', '_TCMailEncryptionPlugin', 'TCMailEncryptionPlugin');
    // log
    PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    return true;
  }
  
  function OnDeleteUser($userId) {
    global $db;
    $db->Query('DELETE FROM {pre}tccme_plugin_queue WHERE user_id = ?', $userId);
    $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE user_id = ?', $userId);
  }
  
  function AfterDeleteMail($mailID, &$mailbox) {
    global $db;
    $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE mail_id = ?', $mailID);
   }
  
  function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
    if (strpos($lang, 'deutsch') !== false) {      
      $lang_admin['tccme.warnung'] = 'Warnung';
      $lang_admin['tccme.keine_gruppe_aktiviert'] = 'Bitte aktivieren Sie ' . $this->name . ' f&uuml;r mindestens eine Gruppe. Sie finden diese Einstellung in den Gruppeneinstellungen von b1gMail.';
      $lang_admin['tccme.verschluesselte_mails'] = $lang_user['tccme.verschluesselte_mails'] = 'Verschl&uuml;sselte E-Mails';
      $lang_admin['tccme.openssl_support'] = 'OpenSSL-Support';
      $lang_admin['tccme.hinweis_warteschlange'] = 'In der Warteschlange befinden sich E-Mails, die auf die Verschl&uuml;sselung warten.';
      $lang_admin['tccme.warteschlange_abarbeiten_beschreibung'] = $lang_admin['tccme.hinweis_warteschlange'] . ' Sie k&ouml;nnen den Vorgang durch Klicken der untenstehenden Schaltfl&auml;che beschleunigen.';
      $lang_admin['tccme.warteschlange_abarbeiten'] = 'E-Mails verschl&uuml;sseln';
      $lang_admin['tccme.javascript_aktivieren'] = 'Damit Sie diese Funktion nutzen k&ouml;nnen, m&uuml;ssen Sie JavaScript im Browser aktivieren.';
      $lang_admin['tccme.mails_verarbeitet'] = 'E-Mails verschl&uuml;sselt';
      
      $lang_admin['tccme.schluessellaenge'] = 'Schl&uuml;ssell&auml;nge (Bit)';
      $lang_admin['tccme.schluessellaenge_empfohlen'] = 'empfohlen';
      $lang_admin['tccme.maxgroesse'] = 'Maximale E-Mail-Gr&ouml;sse';
      $lang_admin['tccme.passwort_aendern'] = 'Die E-Mails dieses Benutzers wurden verschl&uuml;sselt abgelegt. Sollte der Benutzer kein Backup des Private Keys haben, gehen beim &Auml;ndern des Passworts durch den Administrator <b>s&auml;mtliche Nachrichten verloren</b>.';
      $lang_admin['tccme.passwort_aendern_bestaetigen'] = 'Ich habe die Warnung verstanden und m&ouml;chte fortfahren.';
      
      $lang_user['tccme_mod'] = 'Verschl&uuml;sselung';
      $lang_user['tccme_mod2'] = 'Verschl&uuml;sselung';
      $lang_user['prefs_d_tccme_mod'] = '&Uuml;bersicht &uuml;ber die Sicherheit Ihrer E-Mails.';
      $lang_user['tccme.backup_hinweis'] = 'Ihre E-Mails werden verschl&uuml;sselt auf unseren Servern abgelegt. Sollten Sie Ihr Passwort vergessen, haben Sie keinen Zugriff mehr auf Ihre alten Nachrichten. Wir empfehlen Ihnen deshalb, ein Backup des Keys herunterzuladen und sicher aufzubewahren.';
      $lang_user['tccme.status'] = 'Status';
      $lang_user['tccme.status_fertig'] = 'Verschl&uuml;sselung abgeschlossen';
      $lang_user['tccme.status_laeuft'] = 'Verschl&uuml;sselung l&auml;uft';
      $lang_user['tccme.status_key_fehlt'] = 'Privater Schl&uuml;ssel fehlt';
      $lang_user['tccme.private_key'] = 'Privater Schl&uuml;ssel';
      $lang_user['tccme.backup_herunterladen'] = 'Backup herunterladen';
      
      $lang_user['tccme.key_nicht_geladen'] = 'Ihre E-Mails wurden verschl&uuml;sselt auf unseren Servern abgelegt. Der notwendige Sch&uuml;ssel konnte nicht geladen werden. Bitte spielen Sie ein Backup Ihres Schl&uuml;ssels ein oder generieren Sie ein neues Schl&uuml;sselpaar.';
      $lang_user['tccme.backup_hochladen'] = 'Backup';
      $lang_user['tccme.weiter'] = 'Weiter';
      $lang_user['tccme.neue_schluessel'] = 'Neues Schl&uuml;sselpaar generieren';
      $lang_user['tccme.neue_schluessel_warnung'] = 'Wenn Sie ein neues Schl&uuml;sselpaar generieren, gehen <b>s&auml;mtliche Nachrichten verloren</b>. Ihre Nachrichten werden unwiderruflich gel&ouml;scht.';
      $lang_user['tccme.neue_schluessel_bestaetigen'] = 'Ich habe die Warnung verstanden und m&ouml;chte fortfahren.';
      
      $lang_user['tccme.passwort_verloren'] = 'Ihre E-Mails wurden verschl&uuml;sselt auf unseren Servern abgelegt. Sollten Sie kein Backup des privaten Schl&uuml;ssels haben, gehen beim Zur&uuml;cksetzen des Passworts <b>s&auml;mtliche Nachrichten verloren</b>. Wenn Sie wirklich Fortfahren m&ouml;chten, setzen Sie sich bitte mit uns in Verbindung.';
    } else {
      $lang_admin['tccme.warnung'] = 'Warning';
      $lang_admin['tccme.keine_gruppe_aktiviert'] = 'Please activate ' . $this->name . ' for at least one group. You can do so by changing the group settings of b1gMail.';
      $lang_admin['tccme.verschluesselte_mails'] = $lang_user['tccme.verschluesselte_mails'] = 'Encrypted e-mails';
      $lang_admin['tccme.openssl_support'] = 'OpenSSL support';
      $lang_admin['tccme.hinweis_warteschlange'] = 'There are unencrypted e-mails waiting in the queue.';
      $lang_admin['tccme.warteschlange_abarbeiten_beschreibung'] = $lang_admin['tccme.hinweis_warteschlange'] . ' You can speed up this process by clicking the button below.';
      $lang_admin['tccme.warteschlange_abarbeiten'] = 'Encrypt e-mails';
      $lang_admin['tccme.javascript_aktivieren'] = 'You need to enable JavaScript in order to use this function.';
      $lang_admin['tccme.mails_verarbeitet'] = 'e-mails encrypted';

      $lang_admin['tccme.schluessellaenge'] = 'Keylength (bits)';
      $lang_admin['tccme.schluessellaenge_empfohlen'] = 'recommended';
      $lang_admin['tccme.maxgroesse'] = 'Maximum e-mail size';
      $lang_admin['tccme.passwort_aendern'] = 'This user has encrypted e-mails. By changing the user\'s password, <strong>all of his e-mails</strong> will be lost in case he doesn\'t have a backup of his privatekey!';
      $lang_admin['tccme.passwort_aendern_bestaetigen'] = 'I have read and understood the warning and would still like to proceed.';


      $lang_user['tccme_mod'] = 'Encryption';
      $lang_user['tccme_mod2'] = 'Encryption';
      $lang_user['prefs_d_tccme_mod'] = 'Encryption status of your e-mails.';
      $lang_user['tccme.backup_hinweis'] = 'Your e-mails will be stored encrypted on our servers. If you should ever forget your password, you cannot access your e-mails anymore. Therefore, we strongly recommend to download a copy of your key and store it in a safe location.';
      $lang_user['tccme.status'] = 'Status';
      $lang_user['tccme.status_fertig'] = 'Encryption completed';
      $lang_user['tccme.status_laeuft'] = 'Encryption pending';
      $lang_user['tccme.status_key_fehlt'] = 'Missing privatekey';
      $lang_user['tccme.private_key'] = 'Privatekey';
      $lang_user['tccme.backup_herunterladen'] = 'Download backup';

      $lang_user['tccme.key_nicht_geladen'] = 'Your e-mails have been stored encrypted on our servers. The necessary keypair could not be found. Please upload a backup of your key (recommended) or generate a new keypair.';
      $lang_user['tccme.backup_hochladen'] = 'Backup';
      $lang_user['tccme.weiter'] = 'Continue';
      $lang_user['tccme.neue_schluessel'] = 'Generate new keypair';
      $lang_user['tccme.neue_schluessel_warnung'] = 'If you generate a new keypair, <strong>all of your e-mails</strong> will be deleted immediately and irrevocably!';
      $lang_user['tccme.neue_schluessel_bestaetigen'] = 'I have read and understood the warning and would still like to proceed.';

      $lang_user['tccme.passwort_verloren'] = 'Your e-mails have been stored encrypted on our servers. If you don\'t have a backup of your privatekey, <strong>all of your e-mails</strong> will be lost after resetting the password. Please contact us, if you\'d still like to proceed.';
    }
  }
  
  function OnLogin($userId) {
    global $db, $bm_prefs;
    if(isset($_REQUEST['passwordMD5']) && $_REQUEST['passwordMD5'] == md5(md5($bm_prefs['adminpw']).$_SERVER['HTTP_USER_AGENT'].$_SERVER['REMOTE_ADDR'])) {
      $_SESSION['tccme_isAdmin'] = true;
    }
    $this->_cleanUpUserMails($userId);
    $this->_loadPrivateKey($userId);
  }
  
  function _cleanUpUserMails($userId) {
    global $db;
    $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE user_id = ? AND mail_id NOT IN (SELECT id FROM {pre}mails WHERE userid = ?)', $userId, $userId);
  }
  
  function _cleanUpMails() {
    global $db;
    $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE user_id NOT IN (SELECT id FROM {pre}users)');
    $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE mail_id NOT IN (SELECT id FROM {pre}mails)');
  }
  
  function _loadPrivateKey($userId) {
    if(empty($_SESSION['tccme_privateKey']) && empty($_SESSION['tccme_isAdmin'])) {
      $privateKey = $this->_getPrivateKey($userId, $_SESSION['bm_xorCryptKey']);
      $_SESSION['tccme_privateKey'] = $privateKey;
    }
  }
  
  function _deleteAllMessages($userId) {
    // load classes, if needed
    if(!class_exists('BMMailbox')) {
      include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');
    }
    if(!class_exists('BMUser')) {
      include(B1GMAIL_DIR . 'serverlib/user.class.php');
    }
    
    // create objects...
    $bUser = _new('BMUser', array($userId));
    /* @var $bMailbox BMMailbox */
    $bMailbox = _new('BMMailbox', array($userId, '', $bUser));
    
    $folders = $bMailbox->GetFolderList();
    foreach(array_keys($folders) as $folderId) {
      $mails = $bMailbox->GetMailIDList($folderId);
      foreach($mails as $mailId) {
        $bMailbox->DeleteMail($mailId, true);
      }
    }
  }
  
  function FileHandler($file, $action) {
    global $db, $thisUser, $cacheManager;
    if($thisUser) {
      if($this->GetGroupOptionValue('tccme_eingeschaltet')) {
        $userId = $thisUser->_id;
        $this->_loadPrivateKey($userId);
        if(empty($_SESSION['tccme_privateKey']) && empty($_SESSION['tccme_isAdmin']) && (!isset($_REQUEST['action']) || $_REQUEST['action'] != 'logout')) {
          global $tpl;
          $showTemplate = true;
          if(isset($_POST['tccme_deletion_confirmed'])) {
            $this->_deleteAllMessages($userId);
            unset($_SESSION['tccme_privateKey']);
            $thisUser->SetPref('tccme_privateKey', '');
            $this->_loadPrivateKey($userId);
            $showTemplate = false;
          } elseif(isset($_FILES['tccme_key']) && !$_FILES['tccme_key']['error']) {
            $privateKey = file_get_contents($_FILES['tccme_key']['tmp_name']);
            $privateKey = @gzinflate($privateKey);
            if($privateKey) {
              $privateKey = openssl_pkey_get_private($privateKey);
              $keyPairDetails = openssl_pkey_get_details($privateKey);
              $publicKey = $keyPairDetails['key'];
              if($publicKey && $publicKey == $this->_getPublicKey($userId, $thisUser)) {
                openssl_pkey_export($privateKey, $privateKey, $_SESSION['bm_xorCryptKey']);
                $thisUser->SetPref('tccme_privateKey', $privateKey);
                $this->_loadPrivateKey($userId);
                $showTemplate = false;
              }
            }
          }
          if($showTemplate) {
            $tpl->assign('pageContent', $this->_templatePath('tccme.user.nokey.tpl'));
            $tpl->display('li/index.tpl');
            exit;
          }
        }
        if ($file == 'prefs.php') {
          $GLOBALS['prefsItems']['tccme_mod'] = true;
          $GLOBALS['prefsImages']['tccme_mod'] = 'plugins/templates/images/tccme_icon48.png';
          $GLOBALS['prefsIcons']['tccme_mod'] = 'plugins/templates/images/tccme_icon16.png';
        }
        $res = $db->Query('SELECT id FROM {pre}tccme_plugin_queue WHERE user_id = ? AND finished != 1 LIMIT 1', $userId);
        if($res->FetchArray() === false) {
          $mailRes = $db->Query('SELECT id FROM {pre}mails WHERE userid = ? AND id NOT IN (SELECT mail_id FROM {pre}tccme_plugin_mail WHERE user_id = ?) LIMIT 1', $userId, $userId);
          if($mailRes->FetchArray() !== false) {
            $db->Query('INSERT INTO {pre}tccme_plugin_queue (user_id, date_added) VALUES (?, ?)', $userId, time());
          }
          $res->Free();
          $mailRes->Free();
        }
      }
    }
    if($file == 'index.php' && $action == 'lostPassword') {
      if(!isset($_REQUEST['email_full']) && !(isset($_REQUEST['email_local']) && isset($_REQUEST['email_domain']))) {
        return;
      }
      $userMail = isset($_REQUEST['email_full'])
        ? trim($_REQUEST['email_full'])
        : trim($_REQUEST['email_local']) . '@' . $_REQUEST['email_domain'];
      $userId = BMUser::GetID($userMail, true);
      
      if($this->_getPublicKey($userId)) {
        global $tpl, $lang_user;
        $tpl->assign('title', $lang_user['tccme_mod']);
        $tpl->assign('msg', $lang_user['tccme.passwort_verloren']);
        $tpl->assign('page', 'nli/msg.tpl');
        $tpl->display('nli/index.tpl');
        exit;
      }
    }
  }
  
  function AfterInit() {
    global $cacheManager;
    if(is_a($cacheManager, 'BMCache_b1gMail')) {
      $cacheManager = new TCMailEncryptionPlugin_BMCache_b1gMail();
    } elseif(is_a($cacheManager, 'BMCache_memcache')) {
      $cacheManager = new TCMailEncryptionPlugin_BMCache_memcache();
    }
    if(!IsPOSTRequest()) {
      return;
    }
    $file = (basename($_SERVER['SCRIPT_FILENAME']));
    if($file == 'users.php' && isset($_REQUEST['save']) && isset($_REQUEST['do']) && $_REQUEST['do'] == 'edit') {
      if(!empty($_REQUEST['passwort'])) {
        if(!isset($_REQUEST['__tccme_deletion_confirmed'])) {
          RequestPrivileges(PRIVILEGES_ADMIN);
          global $tpl, $lang_admin;
          if(!$this->_getPublicKey($_REQUEST['id'])) {
            return;
          }
          $tpl->assign('page', $this->_templatePath('tccme.admin.password.tpl'));
          $tpl->assign('title', $lang_admin['usersgroups'] . ' &raquo; ' . $lang_admin['users']);
          $tpl->display('page.tpl');
          exit;
        }
      }
    }
  }
  
  function OnUserPasswordChange($userId, $password1, $password2, $passwordPlain) {
    if(empty($_SESSION['tccme_privateKey']) || empty($_SESSION['bm_xorCryptKey'])) {
      PutLog(sprintf('Session variables for user #%d not set... This may result in data loss!', $userId), PRIO_ERROR, __FILE__, __LINE__);
      return;
    }
    $pkey = @openssl_pkey_get_private($_SESSION['tccme_privateKey']);
    if(!$pkey) {
      PutLog(sprintf('Could not load private key for user #%d... This may result in data loss! %s', $userId, openssl_error_string()), PRIO_ERROR, __FILE__, __LINE__);
      return;
    }
    $privateKey = false;
    $newXorCryptKey = BMUser::GenerateXORCryptKey($userId, $passwordPlain);
    if(!@openssl_pkey_export($pkey, $privateKey, $newXorCryptKey)) {
      PutLog(sprintf('Could not export private key for user #%d... This may result in data loss! %s', $userId, openssl_error_string()), PRIO_ERROR, __FILE__, __LINE__);
      return;
    }
    $user = _new('BMUser', array($userId));
    $user->SetPref('tccme_privateKey', $privateKey);
  }

  function UserPrefsPageHandler($action) {
    if ($action != 'tccme_mod' || defined('TCCME_PREFS_SHOWN') || !$this->GetGroupOptionValue('tccme_eingeschaltet')) {
      return false;
    }
    global $tpl, $db, $userRow, $groupRow;
    define('TCCME_PREFS_SHOWN', true);
    
    if(isset($_POST['tccme_backup'])) {
      $fileName = isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] . '-' : '';
      $fileName .= 'private_key.tccme';
      $key = gzdeflate($_SESSION['tccme_privateKey']);
      header('Pragma: public');
      header('Content-Disposition: attachment; filename="' . $fileName . '"');
      header('Content-Type: application/octet-stream');
      header(sprintf('Content-Length: %d', strlen($key)));
      echo $key;
      return;
    }
    
    $this->_cleanUpUserMails($userRow['id']);
    
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tccme_plugin_mail WHERE user_id = ?', $userRow['id']);
    list ($mailCount) = $res->FetchArray(MYSQL_NUM);
    $tpl->assign('tccme_mailCount', $mailCount);
    
    $res = $db->Query('SELECT COUNT(*) FROM {pre}mails WHERE userid = ?', $userRow['id']);
    list ($totalMailcount) = $res->FetchArray(MYSQL_NUM);
    $tpl->assign('tccme_totalMailCount', $totalMailcount);
    if($totalMailcount == 0) {
      $percent = 100;
    } else {
      $percent = round(100/$totalMailcount*$mailCount, 2);
      if($percent == 100 && $totalMailcount != $mailCount) {
        $percent = 99.99;
      }
    }
    $tpl->assign('tccme_mailCountPercent', $percent);
    $tpl->assign('tccme_hasKey', !empty($_SESSION['tccme_privateKey']));
    
    $tpl->assign('pageContent', $this->_templatePath('tccme.user.prefs.tpl'));
    $tpl->display('li/index.tpl');
  }
  
  function OnCron() {
    $this->_workOffQueue();
    if(rand(1, 10) == 1) {
      global $db;
      $db->Query('DELETE FROM {pre}tccme_plugin_queue WHERE finished = 1 AND date_added < ' . (time() - TIME_ONE_DAY));
      $this->_cleanUpMails();
     }
  }
  
  function _workOffQueue() {
    global $db;
    /* @var $db DB */
    $mailLimit = 20;
    $userLimit = 5;
    $res = $db->Query('SELECT id, user_id, mails_processed FROM {pre}tccme_plugin_queue WHERE finished = 0 AND locked < ' . (time() - 60) . ' ORDER BY RAND() LIMIT ' . $userLimit);
    $userCount = $res->RowCount();
    if (!$userCount) {
      return;
    }
    $users = array();
    $queues = array();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $users[] = $row;
      $queues[] = $row['id'];
    }
    $res->Free();
    
    // lock
    $db->Query('UPDATE {pre}tccme_plugin_queue SET locked = ? WHERE id IN (' . implode(', ', $queues) . ')', time());
    
    // load classes, if needed
    if(!class_exists('BMMailbox')) {
      include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');
    }
    if(!class_exists('BMUser')) {
      include(B1GMAIL_DIR . 'serverlib/user.class.php');
    }
    
    $mailLimit = round(($userLimit / $userCount) * $mailLimit);
    $totalMailsProcessed = 0;
    foreach($users as $row) {
      // create objects...
      /* @var $bUser BMUser */
      $bUser = _new('BMUser', array($row['user_id']));
      $bMailbox = _new('BMMailbox', array($row['user_id'], $bUser->GetDefaultSender(), $bUser));
      
      // fetch mails
      $mailRes = $db->Query('SELECT * FROM {pre}mails WHERE userid = ? AND id NOT IN (SELECT mail_id FROM {pre}tccme_plugin_mail WHERE user_id = ?) LIMIT ' . $mailLimit, $row['user_id'], $row['user_id']);
      $mailCounter = 0;
      while (($mail = $mailRes->FetchArray(MYSQL_ASSOC)) !== false) {
        ++$mailCounter;
        ++$totalMailsProcessed;
        $bMail = _new('BMMail', array(
          $mail['userid'],
          $mail,
          false,
          false));
        $this->_encryptMail($bMail, $bMailbox);
      }
      if ($mailCounter != $mailLimit) {
        $db->Query('UPDATE {pre}tccme_plugin_queue SET locked = 0, finished = 1, mails_processed = ? WHERE id = ?', ($row['mails_processed'] + $mailCounter), $row['id']);
      } else {
        $db->Query('UPDATE {pre}tccme_plugin_queue SET locked = 0, finished = 0, mails_processed = ? WHERE id = ?', ($row['mails_processed'] + $mailCounter), $row['id']);
      }
      $mailRes->Free();
    }
    return $totalMailsProcessed;
  }
  
  /**
   * 
   * @param BMMail $bmMail
   * @param BMMailbox $mailbox
   */
  function _encryptMail(&$bmMail, $mailbox) {
    global $db;
    $userId = $mailbox->_userID;
    $mailId = $bmMail->id;
    $bUser = $mailbox->_userObject;    
    $res = $db->Query('SELECT gruppe FROM {pre}users WHERE id = ?', $userId);
    if (!$res->RowCount()) {
      return false;
    }
    list ($group) = $res->FetchArray(MYSQL_NUM);
    if (!$this->GetGroupOptionValue('tccme_eingeschaltet', $group)) {
      return false;
    }
    $cert = $this->_getCert($userId, $bUser);
    if (!$cert) {
      return false;
    }
    if ($bmMail->storedIn == STORE_DB) {
      $res = $db->Query('SELECT body FROM {pre}mails WHERE id = ?', $mailId);
      list ($body) = $res->FetchArray(MYSQL_NUM);
      $tempFileID = RequestTempFile($userId, time() + 5 * TIME_ONE_MINUTE);
      $sourceFileName = TempFileName($tempFileID);
      $GLOBALS['tempFilesToReleaseAtShutdown'][] = array(
        $userId, 
        $tempFileID);
      $tempFp = fopen($sourceFileName, 'wb+');
      fwrite($tempFp, $body);
      fclose($tempFp);
    } else {
      $sourceFileName = $bmMail->GetMessageFilename();
    }
    $tempFileID = RequestTempFile($userId, time() + 5 * TIME_ONE_MINUTE);
    $destinationFileName = TempFileName($tempFileID);
    $tempFp = fopen($destinationFileName, 'wb+');
    fclose($tempFp);
    $GLOBALS['tempFilesToReleaseAtShutdown'][] = array($userId, 
      $tempFileID);
    $sourceFp = fopen($sourceFileName, 'r');
    if (!$sourceFp) {
      PutLog(sprintf('Could not read message <%d>', $mailId), PRIO_ERROR, __FILE__, __LINE__);
      return false;
    }
    $encodingHeader = fread($sourceFp, 33);
    fclose($sourceFp);
    if ($encodingHeader != 'X-EncodedBy: CleverMailEncryption') {
      if (!openssl_pkcs7_encrypt($sourceFileName, $destinationFileName, $cert, array(
        'X-EncodedBy' => $this->name . '/' . $this->version))) {
        PutLog(sprintf('Could not encrypt message <%d>: %s', $mailId, openssl_error_string()), PRIO_ERROR, __FILE__, __LINE__);
        return false;
      }
      if ($bmMail->storedIn == STORE_DB) {
        $tempFp = fopen($destinationFileName, 'r');
        $db->Query('UPDATE {pre}mails SET body = ? WHERE id = ?', fread($tempFp, filesize($destinationFileName)), $mailId);
      } else {
        if(!@copy($destinationFileName, $bmMail->GetMessageFilename())) {
          PutLog(sprintf('Could not write encrypted message <%d>. Permission denied?', $mailId), PRIO_ERROR, __FILE__, __LINE__);
          return false;
        }
      }
    }
    $db->Query('REPLACE INTO {pre}tccme_plugin_mail (mail_id, user_id) VALUES (?, ?)', $mailId, $userId);
    $bmMail->flags |= FLAG_DECEPTIVE;
    $mailbox->FlagMail(FLAG_DECEPTIVE, true, $mailId);
    return true;
  }
  
  /**
   * @param int $mailID
   * @param BMMail $mail
   * @param BMMailbox $mailbox
   */
  function AfterStoreMail($mailID, &$mail, &$mailbox) {
    $mail->id = $mailID;
    $this->_encryptMail($mail, $mailbox);
  }
  
  /**
   * @param int $id
   * @param bool $allowOverride
   * @param BMMail $mail
   */
  function OnGetMessageFP($id, $allowOverride, $mail) {
    global $db;
    $start = microtime(true);
    if(!isset($_SESSION['tccme_privateKey'])) {
      return false;
    }
    $res = $db->Query('SELECT * FROM {pre}tccme_plugin_mail WHERE mail_id = ?', $mail->id);
    $count = $res->RowCount();
    if (!$count) {
      $res->Free();
      return false;
    }
    $res->Free();
    $privateKey = $_SESSION['tccme_privateKey'];
    $privateKey = openssl_get_privatekey($privateKey);
    $userId = $mail->_userID;
    $cert = $this->_getCert($userId);
    $cert = openssl_x509_read($cert);
    if ($mail->storedIn == STORE_DB) {
      $tempFileID = RequestTempFile($userId, time() + 5 * TIME_ONE_MINUTE);
      $sourceFileName = TempFileName($tempFileID);
      $GLOBALS['tempFilesToReleaseAtShutdown'][] = array(
        $userId, 
        $tempFileID);
      $tempFp = fopen($sourceFileName, 'wb+');
      fwrite($tempFp, $mail->_row['body']);
      fclose($tempFp);
    } else {
      $sourceFileName = $mail->GetMessageFilename();
    }
    $sourceFp = @fopen($sourceFileName, 'r');
    if(!$sourceFp) {
      PutLog(sprintf('Message <%d> can\'t be opened', $mail->id), PRIO_WARNING, __FILE__, __LINE__);
      return false;
    }
    $encodingHeader = fread($sourceFp, 33);
    fclose($sourceFp);
    if(!$encodingHeader) {
      PutLog(sprintf('Message <%d> can\'t be read or is empty', $mail->id), PRIO_WARNING, __FILE__, __LINE__);
      return false;
    }
    if($encodingHeader != 'X-EncodedBy: CleverMailEncryption') {
      PutLog(sprintf('Message <%d> is not properly encrypted. Deleting internal reference.', $mail->id), PRIO_WARNING, __FILE__, __LINE__);
      $db->Query('DELETE FROM {pre}tccme_plugin_mail WHERE mail_id = ?', $mail->id);
      return false;
    }
    $tempFileID = RequestTempFile($userId, time() + 5 * TIME_ONE_MINUTE);
    $destinationFileName = TempFileName($tempFileID);
    $GLOBALS['tempFilesToReleaseAtShutdown'][] = array($userId, 
      $tempFileID);
    if (!@openssl_pkcs7_decrypt($sourceFileName, $destinationFileName, $cert, $privateKey)) {
      PutLog(sprintf('Could not decrypt message <%d>: %s', $mail->id, openssl_error_string()), PRIO_ERROR, __FILE__, __LINE__);
      return false;
    }
    $processingTime = microtime(true) - $start;
    if(DEBUG) {
      PutLog(sprintf('Decrypted mail <%d> in %.04f seconds (%d bytes; throughput: %.02f KB/s)', $mail->id, $processingTime, filesize($sourceFileName), round(filesize($sourceFileName) / $processingTime / 1024, 2)),
        PRIO_DEBUG,
        __FILE__,
        __LINE__);
    }
    $tempFp = fopen($destinationFileName, 'r');
    return $tempFp;
  }
  
  /**
   * @param int $id
   * @param int $user
   * @param BMMail $mail
   */
  /*function OnGetMail($id, $user, $mail) {
    $config = $this->_getConfig();
    if($config['dont_cache_mails']) {
      $mail->_useCache = false;
    }
  }*/
  
  var $_config = null;
  
  function _getConfig() {
    global $db;
    if ($this->_config == null) {
      $res = $db->Query('SELECT * FROM {pre}tccme_plugin_settings LIMIT 1');
      $config = $res->FetchArray();
      $res->Free();
      $this->_config = $config;
    }
    return $this->_config;
  }
  
  function _getCert($userId, $user = null) {
    global $cacheManager;
    if (is_object($cacheManager)) {
      $cert = $cacheManager->Get('tccme_cert_' . $userId);
      if($cert !== false) {
        return $cert;
      }
    }
    if($user == null) {
      /* @var $user BMUser */
      $user = _new('BMUser', array($userId));
    }
    $cert = $user->GetPref('tccme_cert');
    if (is_object($cacheManager)) {
      $cacheManager->Set('tccme_cert_' . $userId, $cert);
    }
    return $cert;
  }
  
  function _getPublicKey($userId, $user = null) {
    global $cacheManager;
    if (is_object($cacheManager)) {
      $pkey = $cacheManager->Get('tccme_pkey_' . $userId);
      if($pkey !== false) {
        return $pkey;
      }
    }
    if($user == null) {
      if (!class_exists('BMUser')) {
        include (B1GMAIL_DIR . 'serverlib/user.class.php');
      }
      /* @var $user BMUser */
      $user = _new('BMUser', array($userId));
    }
    $pkey = $user->GetPref('tccme_publicKey');
    if (is_object($cacheManager)) {
      $cacheManager->Set('tccme_pkey_' . $userId, $pkey);
    }
    return $pkey;
  }
  
  function _getPrivateKey($userId, $cryptKey, $user = null) {
    global $db, $cacheManager;
    if($user == null) {
      /* @var $user BMUser */
      $user = _new('BMUser', array($userId));
    }
    $privateKey = $user->GetPref('tccme_privateKey');
    if (!$privateKey) {
      if(!$this->GetGroupOptionValue('tccme_eingeschaltet', $user->_row['gruppe'])) {
        return false;
      }
      $config = $this->_getConfig();
      $keyBits = (int)$config['schluessellaenge'];
      $keyPair = openssl_pkey_new(array(
        'private_key_type' => OPENSSL_KEYTYPE_RSA,
        'private_key_bits' => $keyBits));
      $csr = openssl_csr_new(array('commonName' => $userId), $keyPair);
      $ssCert = openssl_csr_sign($csr, null, $keyPair, 365 * 100);
      $certOut = '';
      openssl_x509_export($ssCert, $certOut);
      $keyPairDetails = openssl_pkey_get_details($keyPair);
      openssl_pkey_export($keyPair, $privateKey, $cryptKey);
      $publicKey = $keyPairDetails['key'];
      $user->SetPref('tccme_privateKey', $privateKey);
      $user->SetPref('tccme_publicKey', $publicKey);
      $user->SetPref('tccme_cert', $certOut);
      if (is_object($cacheManager)) {
        $cacheManager->Delete('tccme_pkey_' . $userId);
        $cacheManager->Delete('tccme_cert_' . $userId);
      }
    }
    // decrpyt private key
    $privateKey = openssl_pkey_get_private($privateKey, $cryptKey);
    // get private key
    if(!@openssl_pkey_export($privateKey, $privateKey)) {
      PutLog(sprintf('Could not load private key for user #%d: %s', $userId, openssl_error_string()), PRIO_ERROR, __FILE__, __LINE__);
    }
    return $privateKey;
  }
  
  function getNotices() {
    global $lang_admin, $db;
    if($this->_hasQueue()) {
      return array(
        0 => array('type' => 'info',
          'text' => $lang_admin['tccme.hinweis_warteschlange'],
          'link' => $this->_adminLink() . '&'));
    }
    return array();
  }
  
  function AdminHandler() {
    global $tpl, $lang_admin, $bm_prefs;
    
    if (!isset($_REQUEST['action']))
      $_REQUEST['action'] = 'start';
    
    $tabs = array(
      0 => array('title' => $lang_admin['overview'],
        'link' => $this->_adminLink() . '&amp;',
        'relIcon' => 'info32.png',
        'active' => $_REQUEST['action'] == 'start'),
      1 => array('title' => $lang_admin['prefs'],
        'link' => $this->_adminLink() . '&amp;action=settings&amp;',
        'relIcon' => 'ico_prefs_common.png',
        'active' => $_REQUEST['action'] == 'settings'));
    
    $tpl->assign('tabs', $tabs);
    switch ($_REQUEST['action']) {
      case 'settings':
        $this->_adminSettings();
        break;
      default:
        $this->_adminStart();
    }
    $tpl->assign('pageURL', $this->_adminLink());
    $tpl->assign('tccme_name', $this->name);
    $tpl->assign('tccme_version', $this->version);
    $tpl->assign('tccme_prefs', $this->_getConfig());
    $tpl->assign('tpldir_user', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
  }
  
  function _adminStart() {
    global $currentLanguage, $tpl, $db;
    
    if(isset($_REQUEST['do']) && $_REQUEST['do'] == 'workOffQueue') {
      if(!$this->_hasQueue()) {
        echo 'DONE';
      } else {
        echo $this->_workOffQueue();
      }
      exit;
    }
    
    $res = $db->Query('SELECT COUNT(*) FROM {pre}groupoptions WHERE `module` = ? AND `key` = \'tccme_eingeschaltet\' AND `value` = 1 AND EXISTS (SELECT id FROM {pre}gruppen WHERE id = gruppe)', $this->internal_name);
    list ($count) = $res->FetchArray(MYSQL_NUM);
    $tpl->assign('tccme_groupCount', $count);
    $res->Free();
    
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tccme_plugin_mail');
    list ($count) = $res->FetchArray(MYSQL_NUM);
    $tpl->assign('tccme_mailCount', $count);
    $res->Free();
    
    $tpl->assign('tccme_hasQueue', $this->_hasQueue());
    
    $tpl->assign('tccme_opensslSupport', SMIME_SUPPORT ? 'ok' : 'error');
    
    $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s&js=1&language=%s&version=%s', $this->update_url, urlencode($this->internal_name), urlencode(B1GMAIL_VERSION), $currentLanguage, $this->version);
    $tpl->assign('updateURL', htmlspecialchars($queryURL));
    $tpl->assign('notices', $this->getNotices());
    $tpl->assign('page', $this->_templatePath('tccme.admin.start.tpl'));
  }
  
  function _hasQueue() {
    global $db;
    $res = $db->Query('SELECT id FROM {pre}tccme_plugin_queue WHERE finished != 1 LIMIT 1');
    $hasQueue = $res->FetchArray() !== false;
    $res->Free();
    return $hasQueue;
  }
  
  function _adminSettings() {
    global $tpl, $db;
    if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
      $db->Query('UPDATE {pre}tccme_plugin_settings SET
        schluessellaenge=?', $_REQUEST['schluessellaenge']);
    }
    $tpl->assign('page', $this->_templatePath('tccme.admin.settings.tpl'));
  }

}
/**
 * register plugin
 */
$plugins->registerPlugin('TCMailEncryptionPlugin');

if (!class_exists('BMCache')) {
  include (B1GMAIL_DIR . 'serverlib/cache.class.php');
}

class TCMailEncryptionPlugin_BMCache_b1gMail extends BMCache_b1gMail {
  function Add($key, $obj, $expires = 0) {
    if(substr($key, 0, 10) == 'parsedMsg:') {
      return(false);
    }
    
    return parent::Add($key, $obj, $expires);
  }
}

class TCMailEncryptionPlugin_BMCache_memcache extends BMCache_memcache {
  function Add($key, $obj, $expires = 0) {
    if(substr($key, 0, 10) == 'parsedMsg:') {
      return(false);
    }
    
    return parent::Add($key, $obj, $expires);
  }
}

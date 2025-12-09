<?php
/**
 * TCTimezonePlugin
 *
 * @link     http://www.thinkclever.ch/
 * @copyright  2007-2009 ThinkClever IT Solutions
 * @version  $Id: tctz.plugin.php 74 2011-01-03 19:32:41Z caspar $
 */

/**
 * TCTimezonePlugin
 *
 */
class TCTimezonePlugin extends BMPlugin {

  function TCTimezonePlugin() {
    $this->name = 'CleverTimeZone';
    $this->author = 'ThinkClever GmbH';
    $this->web = 'http://www.thinkclever.ch/';
    $this->mail = 'info@thinkclever.ch';
    $this->version = '1.2.0';
    $this->designedfor = '7.3.0';
    $this->type = BMPLUGIN_DEFAULT;
    $this->order = -5;
    
    $this->admin_pages = true;
    $this->admin_page_title = $this->name;
    $this->admin_page_icon = 'tctz_icon48.png';
    
    $this->website = 'http://my.b1gmail.com/details/111/';
    $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
  }
  
  function Install() {
    global $db, $mysql;
    $databaseStructure = // Tables: 1; Hash: 5e0b66ca0bb2fbb4f76a43ed10989342
      'YToxOntzOjI1OiJ7cHJlfXRjdHpfcGx1Z2luX3NldHRpbmdzIjthOjI6e3M6NjoiZmllbGRzIjt'
    . 'hOjM6e2k6MDthOjY6e2k6MDtzOjExOiJjb29yZGluYXRlcyI7aToxO3M6MTI6InZhcmNoYXIoMj'
    . 'U1KSI7aToyO3M6MjoiTk8iO2k6MztzOjA6IiI7aTo0O3M6MTI6IjUyLjU0LCAxMy4zMiI7aTo1O'
    . '3M6MDoiIjt9aToxO2E6Njp7aTowO3M6ODoidGltZXpvbmUiO2k6MTtzOjEyOiJ2YXJjaGFyKDI1'
    . 'NSkiO2k6MjtzOjI6Ik5PIjtpOjM7czowOiIiO2k6NDtzOjEzOiJFdXJvcGUvQmVybGluIjtpOjU'
    . '7czowOiIiO31pOjI7YTo2OntpOjA7czoxMDoiZ29vZ2xlX2tleSI7aToxO3M6MTI6InZhcmNoYX'
    . 'IoMjU1KSI7aToyO3M6MjoiTk8iO2k6MztzOjA6IiI7aTo0O047aTo1O3M6MDoiIjt9fXM6Nzoia'
    . 'W5kZXhlcyI7YTowOnt9fX0=';
    $databaseStructure = unserialize(base64_decode($databaseStructure));
    $structure = array();
    foreach ($databaseStructure as $tableName => $data) {
      $tableName = str_replace('{pre}', $mysql['prefix'], $tableName);
      $structure[$tableName] = $data;
    }
    SyncDBStruct($structure);
    
    // prefs row?
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tctz_plugin_settings');
    list ($rowCount) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    
    // insert prefs row
    if ($rowCount < 1) {
      $db->Query("INSERT INTO `{pre}tctz_plugin_settings` (`google_key`) VALUES ('')");
    }
    
    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', 'TCTimezonePlugin', '_TCTimezonePlugin');
    
    // log
    PutLog(sprintf('%s v%s installed', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    
    return true;
  }
  
  function Uninstall() {
    global $db;
    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', '_TCTimezonePlugin', 'TCTimezonePlugin');
    // log
    PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    return true;
  }
  
  function AfterInit() {
    $config = $this->_getConfig();
    $timezone = $config['timezone'];
    $this->_setTimezone($timezone);
  }
  
  function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
    if (strpos($lang, 'deutsch') !== false) {
      $lang_admin['tctz.warnung'] = 'Warnung';
      $lang_admin['tctz.keine_gruppe_aktiviert'] = 'Bitte aktivieren Sie ' . $this->name . ' f&uuml;r mindestens eine Gruppe. Sie finden diese Einstellung in den Gruppeneinstellungen von b1gMail.';
      $lang_admin['tctz_google_key'] = 'Google API Key';
      $lang_admin['tctz_google_key_instruction'] = 'Bitte geben Sie in das untenstehende Feld Ihren Google API Key ein. Falls Sie noch keinen besitzen, k&ouml;nnen Sie <a href="http://code.google.com/apis/maps/signup.html" target="_blank">hier</a> einen beantragen.';
      $lang_admin['tctz_instruction'] = 'Bitte geben Sie im Textfeld jenen Standort ein, welchen Sie als Standard festlegen m&ouml;chten oder w&auml;hlen Sie ihn direkt in der Karte aus. Best&auml;tigen Sie danach mit &quot;Speichern&quot;.';
      $lang_admin['tctz_default_timezone'] = 'Standard-Zeitzone';
      
      $lang_admin['tctz_standard'] = 'Standard-Zeitzone';
      $lang_admin['tctz_erweitert'] = 'Benutzerdefiniert (Der Benutzer kann selber ausw&auml;hlen)';
      $lang_admin['tctz_automatisch'] = 'Automatisch (Zeitzone automatisch erkennen)';
      
      $lang_user['tctz_mod'] = 'Zeitzone';
      $lang_user['tctz_mod2'] = 'Zeitzoneneinstellungen';
      $lang_user['prefs_d_tctz_mod'] = 'Erm&ouml;glicht die Konfiguration der Zeitzone.';
      $lang_user['tctz_automatic'] = 'Zeitzone automatisch erkennen';
      $lang_user['tctz_map'] = $lang_admin['tctz_map'] = 'Standort';
      $lang_user['tctz_coordinates'] = $lang_admin['tctz_coordinates'] = 'Koordinaten';
      $lang_user['tctz_timezone'] = $lang_admin['tctz_timezone'] = 'Zeitzone';
      $lang_user['tctz_google_loading'] = $lang_admin['tctz_google_loading'] = 'Karte l&auml;dt...';
      $lang_user['tctz_search'] = $lang_admin['tctz_search'] = 'Suchen';
      $lang_user['tctz_error_input'] = $lang_admin['tctz_error_input'] = 'Bitte geben Sie Ihren Standort im Textfeld ein.';
      $lang_user['tctz_error_not_found'] = $lang_admin['tctz_error_not_found'] = 'Der gew�hlte Standort ist ung�ltig.';
      $lang_user['tctz_instruction'] = 'Bitte geben Sie im Textfeld Ihren Standort ein oder w&auml;hlen Sie ihn direkt in der Karte aus. Best&auml;tigen Sie danach mit &quot;OK&quot;.';
    } else {
      $lang_admin['tctz.warnung'] = 'Warning';
      $lang_admin['tctz.keine_gruppe_aktiviert'] = 'Please activate ' . $this->name . ' for at least one group. You are able to do so in the group settings of b1gMail.';
      $lang_admin['tctz_google_key'] = 'Google API Key';
      $lang_admin['tctz_google_key_instruction'] = 'Please enter your Google API key in the field below. If you do not yet have a key, you need to <a href="http://code.google.com/apis/maps/signup.html" target="_blank">sign up</a> for one.';
      $lang_admin['tctz_instruction'] = 'Please mark a spot on the map to set the default Time Zone and confirm your choice by clicking the &quot;Save&quot; button.';
      $lang_admin['tctz_default_timezone'] = 'Default Time Zone';
      
      $lang_admin['tctz_standard'] = 'Default Time Zone';
      $lang_admin['tctz_erweitert'] = 'Userdefined (The user can choose himself)';
      $lang_admin['tctz_automatisch'] = 'Automatic (Detect Time Zone automatically)';
      
      $lang_user['tctz_mod'] = 'Time Zone';
      $lang_user['tctz_mod2'] = 'Time Zone Settings';
      $lang_user['prefs_d_tctz_mod'] = 'Allows you to edit your Time Zone.';
      $lang_user['tctz_automatic'] = 'Detect Time Zone automatically';
      $lang_user['tctz_map'] = $lang_admin['tctz_map'] = 'Place';
      $lang_user['tctz_coordinates'] = $lang_admin['tctz_coordinates'] = 'Coordinates';
      $lang_user['tctz_timezone'] = $lang_admin['tctz_timezone'] = 'Time Zone';
      $lang_user['tctz_google_loading'] = $lang_admin['tctz_google_loading'] = 'Loading map...';
      $lang_user['tctz_search'] = $lang_admin['tctz_search'] = 'Search';
      $lang_user['tctz_error_input'] = $lang_admin['tctz_error_input'] = 'Please enter your location in the text field.';
      $lang_user['tctz_error_not_found'] = $lang_admin['tctz_error_not_found'] = 'The selected place does not exist.';
      $lang_user['tctz_instruction'] = 'Please mark your current location by clicking on the map and confirm your choice by clicking the &quot;Save&quot; button.';
    }
    $this->RegisterGroupOption('tctz_eingeschaltet', FIELD_DROPDOWN, $lang_user['tctz_mod'] . ':', array(
      '0' => $lang_admin['tctz_standard'],
      '2' => $lang_admin['tctz_automatisch'],
      '1' => $lang_admin['tctz_erweitert']));
  }
  
  function FileHandler($file, $action) {
    $active = $this->GetGroupOptionValue('tctz_eingeschaltet');
    if ($active) {
      global $thisUser;
      $automatic = $thisUser->GetPref('tctz_automatic') || $active == 2;
      $timezone = $thisUser->GetPref('tctz_timezone');
      if ($automatic && !isset($_SESSION['tctz_timezone']) && !($file == 'start.php' && $action == 'tctz_set')) {
        header('Location: start.php?action=tctz_set&' . SID);
        exit;
      } elseif ($automatic && isset($_SESSION['tctz_timezone']) && $_SESSION['tctz_timezone'] === true) {
        $timezone = false;
      }
      if (!$timezone) {
        $config = $this->_getConfig();
        $timezone = $config['timezone'];
      }
      $this->_setTimezone($timezone);
      if ($file == 'prefs.php' && $active != 2) {
        $config = $this->_getConfig();
        if (!empty($config['google_key'])) {
          $GLOBALS['prefsItems']['tctz_mod'] = true;
          $GLOBALS['prefsImages']['tctz_mod'] = 'plugins/templates/images/tctz_icon48.png';
          $GLOBALS['prefsIcons']['tctz_mod'] = 'plugins/templates/images/tctz_icon16.png';
        }
      }
      if ($file == 'start.php' && $action == 'tctz_ajax') {
        $this->_ajaxCall();
      } else if ($file == 'start.php' && $action == 'tctz_set') {
        $_SESSION['tctz_timezone'] = true;
        if (isset($_GET['offset'])) {
          $timezone = $this->_getTimezoneByOffset($_GET['offset']);
          $_SESSION['tctz_timezone'] = $timezone;
          $thisUser->SetPref('tctz_timezone', $timezone);
        } else {
          global $tpl;
          $tpl->assign('url', 'start.php?' . SID);
          $tpl->display($this->_templatePath('tctz.user.automatic.tpl'));
        }
      } else if ($file == 'organizer.calendar.php' && $action == 'dayView') {
        global $tpl;
        $vars = $tpl->get_template_vars();
        $tpl = new TCTimezonePlugin_TPL();
        $tpl->assign($vars);
        $tpl->assign('tctz_offset', (date('Z') - (date('I') ? 3600 : 0)));
        $tpl->setTemplate($this->_templatePath('tctz.organizer.calendar.dayview.view.tpl'));
      }
    } else {
      $config = $this->_getConfig();
      $timezone = $config['timezone'];
      $this->_setTimezone($timezone);
    }
  }
  
  function _getTimezoneByOffset($offsetInSeconds) {
    // http://www.phpbuilder.com/board/showpost.php?p=10886172&postcount=2
    $abbreviationsList = // Length: 556; Hash: 9c7b27f47c4db17147e2259ec34ed7cb
      'dZPbToNAEIbfpfe1pUBb8KoHE6PWNGlj4hWZ0hVWlh2zsNZqfHf3WNDEOzLf/v8cgTScpF80HQa'
    . 'T6yYNZulgCzl9ofno/gSvwAjlg2uNA43DDu+gRrBorNG0Q7fIkUkmDU00jNLBoiaC5jC6k5yARX'
    . 'ONkg49YJMteEEYaQyf/ZGuCX8nwqDpH+mGfNAcsxVtz4bHriTPH8kpe0ZRDVRcOQ6jq1h/BnH3Z'
    . 'AUCcrCZIzcLz5YI2RNtWvD60Ol7KXZtdocltwahguG4gwtREN5Srqwk4bpPKlyXE19Hy0A9yUeL'
    . 'T/Qs+J+N3T5upMA3ombHj2hWZTSTC9iCoEbg87j4LWEN5RXVKPzttcEmx5PuT8V8p4GqoqEw2pN'
    . 'SgEmkh5S46BIq6QR+tGOH7uEgmZtbdDWzMLzAtgZ+NPcQu8IdUNsoqfOMnaenK2RYH1Crpv1c6x'
    . 'Iqc5Sz/usl8KLCSsfn/qR0fEd5AW9qoJokfZ89Vmd0uZPeqmXTCmA6EYiT/TPs9Sd9uCHsgFJw4'
    . 'xsE/VI2UMDRjs/+cNGl3zovodXVf/8A';
    $abbreviationsList = unserialize(gzinflate(base64_decode($abbreviationsList)));
    $offsetInHours = (string)($offsetInSeconds / 60 / 60);
    $offsetInHours = str_replace(',', '.', $offsetInHours);
    if(isset($abbreviationsList[$offsetInHours])) {
      return $abbreviationsList[$offsetInHours];
    }
    return false;
  }
  
  function _setTimezone($timezone) {
    if (!$timezone) {
      return false;
    }
    if (function_exists('date_default_timezone_set')) {
      date_default_timezone_set($timezone);
    } else {
      putenv('TZ=' . $timezone);
      mktime(0, 0, 0, 1, 1, 1970); // bugfix
    }
  }
  
  function UserPrefsPageHandler($action) {
    $config = $this->_getConfig();
    $active = $this->GetGroupOptionValue('tctz_eingeschaltet');
    if ($action != 'tctz_mod' || defined('TCTZ_PREFS_SHOWN') || !$active || $active == 2 || empty($config['google_key'])) {
      return false;
    }
    global $tpl, $db, $thisUser;
    
    define('TCTZ_PREFS_SHOWN', true);
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
      if (!empty($_POST['coordinates']) && preg_match('#\-?[0-9]+\.[0-9]+, \-?[0-9]+\.[0-9]+#', $_POST['coordinates'])) {
        list ($lat, $lng) = explode(', ', $_POST['coordinates'], 2);
        $timezone = $this->_getTimezone($lat, $lng);
        if ($timezone) {
          $thisUser->SetPref('tctz_coordinates', $_POST['coordinates']);
          $thisUser->SetPref('tctz_timezone', $timezone);
          $thisUser->SetPref('tctz_automatic', !empty($_POST['automatic']));
        }
      }
      if(!empty($_POST['automatic'])) {
        $timezone = $this->_getTimezoneByOffset($_POST['automatic']);
        $_SESSION['tctz_timezone'] = $timezone;
        $thisUser->SetPref('tctz_timezone', $timezone);
        $thisUser->SetPref('tctz_coordinates', false);
        $thisUser->SetPref('tctz_automatic', true);
      }
      header('Location: ' . $_SERVER['REQUEST_URI']);
      exit();
    }
    $tpl->assign('tctz_coordinates', $thisUser->GetPref('tctz_coordinates'));
    $tpl->assign('tctz_timezone', $thisUser->GetPref('tctz_timezone'));
    $tpl->assign('tctz_automatic', $thisUser->GetPref('tctz_automatic'));
    $tpl->assign('pageContent', $this->_templatePath('tctz.user.prefs.tpl'));
    $tpl->assign('tctz_prefs', $config);
    $tpl->assign('tctz_include_javascript', $this->_templatePath('tctz.googlemaps.tpl'));
    $tpl->display('li/index.tpl');
    return true;
  }
  
  function _getTimezone($lat, $lng) {
    if (!class_exists('BMHTTP')) {
      include (B1GMAIL_DIR . 'serverlib/http.class.php');
    }
    $url = 'http://ws.geonames.org/timezone?lat=' . $lat . '&lng=' . $lng;
    $http = _new('BMHTTP', array($url));
    $result = $http->DownloadToString();
    $matches = array();
    if (preg_match('#<timezoneId>(.*)</timezoneId>#', $result, $matches)) {
      return $matches[1];
    }
    return false;
  }
  
  function OnCron() {

  }
  
  function AdminHandler() {
    global $tpl, $lang_admin, $bm_prefs;
    
    if (!isset($_REQUEST['action']))
      $_REQUEST['action'] = 'settings';
    
    $tabs = array(
      0 => array('title' => $lang_admin['prefs'],
        'link' => $this->_adminLink() . '&action=settings&',
        'relIcon' => 'ico_prefs_common.png',
        'active' => $_REQUEST['action'] == 'settings'));
    
    $tpl->assign('tabs', $tabs);
    switch ($_REQUEST['action']) {
      case 'settings':
        $this->_adminSettings();
        break;
      case 'ajax':
        $this->_ajaxCall();
        break;
    }
    $tpl->assign('pageURL', $this->_adminLink());
    $tpl->assign('tccrn_name', $this->name);
    $tpl->assign('tpldir_user', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
    $tpl->assign('tctz_prefs', $this->_getConfig());
  }
  
  function _ajaxCall() {
    if (!empty($_GET['coordinates']) && preg_match('#\-?[0-9]+\.[0-9]+, \-?[0-9]+\.[0-9]+#', $_GET['coordinates'])) {
      list ($lat, $lng) = explode(', ', $_GET['coordinates'], 2);
      echo $this->_getTimezone($lat, $lng);
      exit();
    } else if(!empty($_GET['offset'])) {
      echo $this->_getTimezoneByOffset($_GET['offset']);
      exit();
    }
  }
  
  function _adminSettings() {
    global $tpl, $db, $currentLanguage;
    if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
      $db->Query('UPDATE {pre}tctz_plugin_settings SET
        google_key=?', $_POST['google_key']);
      if (!empty($_POST['coordinates']) && preg_match('#\-?[0-9]+\.[0-9]+, \-?[0-9]+\.[0-9]+#', $_POST['coordinates'])) {
        list ($lat, $lng) = explode(', ', $_POST['coordinates'], 2);
        $timezone = $this->_getTimezone($lat, $lng);
        if ($timezone) {
          $db->Query('UPDATE {pre}tctz_plugin_settings SET
            coordinates=?, timezone=?', $_POST['coordinates'], $timezone);
        }
      }
      $this->_config = null;
    }
    $tpl->assign('tctz_include_javascript', $this->_templatePath('tctz.googlemaps.tpl'));
    $tpl->assign('page', $this->_templatePath('tctz.admin.prefs.tpl'));
    
    $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s&js=1&language=%s&version=%s', $this->update_url, urlencode($this->internal_name), urlencode(B1GMAIL_VERSION), $currentLanguage, $this->version);
    $tpl->assign('updateURL', htmlspecialchars($queryURL));
    $tpl->assign('notices', $this->getNotices());
  }
  
  var $_config = null;
  
  function _getConfig() {
    global $db;
    if ($this->_config == null) {
      $res = $db->Query('SELECT * FROM {pre}tctz_plugin_settings LIMIT 1');
      $config = $res->FetchArray();
      $res->Free();
      $this->_config = $config;
    }
    return $this->_config;
  }

}
/**
 * register plugin
 */
$plugins->registerPlugin('TCTimezonePlugin');

if (!class_exists('TCTimezonePlugin_TPL')) {
  class TCTimezonePlugin_TPL extends Template {
    
    var $_template;
    
    function setTemplate($template) {
      $this->_template = $template;
    }
    
    function display($resource_name, $cache_id = null, $compile_id = null) {
      if ($resource_name == 'li/organizer.calendar.dayview.view.tpl') {
        $resource_name = $this->_template;
      }
      parent::display($resource_name, $cache_id, $compile_id);
    }
  }
}
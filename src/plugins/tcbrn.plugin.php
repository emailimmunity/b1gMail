<?php
/**
 * CleverBranding
 *
 * @link       http://www.thinkclever.ch/
 * @copyright  2007-2013 ThinkClever IT Solutions
 */

/**
 * CleverBranding
 *
 */
class TCBrandPlugin extends BMPlugin {

    function TCBrandPlugin() {
        $this->name = 'CleverBranding';
        $this->author = 'ThinkClever GmbH';
        $this->web = 'http://www.thinkclever.ch/';
        $this->mail = 'info@thinkclever.ch';
        $this->version = '1.3.1';
        $this->designedfor = '7.3.0';
        $this->type = BMPLUGIN_DEFAULT;
        $this->order = -10;

        $this->admin_pages = true;
        $this->admin_page_title = $this->name;
        $this->admin_page_icon = 'tcbrn_icon32.png';

        $this->website = 'http://my.b1gmail.com/details/85/';
        $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
    }

    function Install() {
      global $mysql, $db, $bm_prefs;
      $databaseStructure = // Tables: 1; Hash: f7e4c68330fab9388812d133bc8ea786
          'rZZNb+IwEIb/ysoXQNoDzgdQc9rjSlt2VWkPe4pcYrKWHDvyRxWE+O87TtrguLRV2XAAe5J5H3s'
        . '8noESTE6GJDlBp0azs90/alk0wlVcFqWqKZcGbSlJ/Fsrgg6cibK3LMmJkyWMVv3AkA1BvQsv0Z'
        . 'YTDCYMPlzaOcaLL04aXknWPUvgWULQ7qefpDBJCfr18N3Psu0OvnPvnBFEnVUFl3vNaiYt2p69b'
        . 'sAEfbq3/IkNxCVBlstjR128xbr//aNngQNBS/RMBN8OkYQIWEWtygGQghaTrp7PjKWypLqcfZ2x'
        . 'FlYBv5pVrJ1dx3biPRMi9eIco9Nod31EX+CwFsta+77+LpLMokOyrG4EtZeQgcwT1fu/VM+TPF9'
        . '8Tj2P1AWVlaPVROqrUH1N0F45afVxEM/7/Mo+SK8g9mBP8jjq6xCT+wyyYqIdbCLpLkU+ka3B0q'
        . '+l6l0o7+X6hBHc2Fsg8erx6IbjTah/KRD/kZwYvwdgB+qEneYgcBLlkmHi4LSYSD2ND8LYsqi0a'
        . '5ohkdZDMbzlpHEWE5zRBdQR68xEe8ijCLVwEoLpidTju2yMKG6O/zAcIUb32DcfzxDKdzPVWK5k'
        . '2Jj6Kn5kBgq3VB9XbbBL9Qq5eRvJJH0MyshkyNGdv4OSqyrl7JShPD8na8ladvkDsO6a9P23hz/'
        . 'ehK/2/fOoJwdvBdYzfP4B';

      $databaseStructure = unserialize(gzinflate(base64_decode($databaseStructure)));
      $structure = array();
      foreach ($databaseStructure as $tableName => $data) {
          $tableName = str_replace('{pre}', $mysql['prefix'], $tableName);
          $structure[$tableName] = $data;
      }
      SyncDBStruct($structure);

      $res = $db->Query('SELECT COUNT(*) FROM {pre}tcbrn_plugin_domains');
      list ($rowCount) = $res->FetchArray(MYSQL_NUM);
      $res->Free();
      if ($rowCount == 0) {
        $domains = $bm_prefs['domains'];
        if(!is_array($domains)) {
          $domains = explode(':', $domains);
        }
        $cList = array('at' => 89,
          'ch' => 105,
          'cn' => 22,
          'de' => 25,
          'es' => 112,
          'fr' => 32,
          'jp' => 48,
          'nl' => 85,
          'ru' => 101,
          'se' => 104,
          'uk' => 37,
          'us' => 133);
        $dach = array('de', 'ch', 'at');
        $languages = GetAvailableLanguages();
        foreach ($domains as $domain) {
          $country = '';
          $language = $bm_prefs['language'];
          $extension = array_pop(explode('.', $domain));
          if (isset($cList[$extension])) {
            $country = $cList[$extension];
          }
          if (in_array($extension, $dach)) {
            if (isset($languages['deutsch'])) {
              $language = 'deutsch';
            }
          } elseif (isset($languages['english'])) {
            $language = 'english';
          }
          $data = array(
            'active' => 1,
            'mode' => 'standard',
            'domain' => $domain,
            'template' => '',
            'xmailer' => $domain . '/' . $this->designedfor,
            'title' => $bm_prefs['titel'],
            'country' => $country,
            'domainlist' => 1,
            'domainlist_domains' => implode(':', $domains),
            'domainlist_default' => $domain,
            'language' => $language,
            'selfurl' => '',
            'ssl_url' => '',
            'ssl_login_enable' => 'no',
            'ssl_login_option' => 'no',
            'logouturl' => '',
            'std_gruppe' => '',
            'usr_status' => '');
          $this->_saveDomain($data);
        }
      }
      // log
      PutLog(sprintf('%s v%s installed', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);

      return true;
    }

    function Uninstall() {
        // log
        PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
      return true;
    }

    function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
      if (strpos($lang, 'deutsch') !== false) {
        $lang_admin['tcbrn.domain'] = 'Domain';
        $lang_admin['tcbrn.domain_bearbeiten'] = 'Domain bearbeiten';
        $lang_admin['tcbrn.domain_hinzufuegen'] = 'Domain hinzuf&uuml;gen';

        $lang_admin['tcbrn.aktiviert'] = 'Aktiv';
        $lang_admin['tcbrn.aktivieren'] = 'Aktivieren';
        $lang_admin['tcbrn.deaktivieren'] = 'Deaktivieren';
        $lang_admin['tcbrn.jetzt_aktivieren'] = 'Aktivieren!';
        $lang_admin['tcbrn.jetzt_deaktivieren'] = 'Deaktivieren!';

        $lang_admin['tcbrn.modus']  = 'Modus';
        $lang_admin['tcbrn.modus_standard']  = 'Standard';
        $lang_admin['tcbrn.modus_standard_hinweis']  = 'Domain und s&auml;mtliche Subdomains';
        $lang_admin['tcbrn.modus_exakt']  = 'Exakt';
        $lang_admin['tcbrn.modus_exakt_hinweis']  = 'Platzhalter * (0-n Zeichen) und ? (genau 1 Zeichen) nutzbar';

        $lang_admin['tcbrn.domainliste'] = 'Domainliste';
        $lang_admin['tcbrn.domainliste_konfigurieren'] = 'konfigurieren';
        $lang_admin['tcbrn.domainliste_sichtbar'] = 'Sichtbar';
        $lang_admin['tcbrn.domainliste_standard'] = 'Standard';
        $lang_admin['tcbrn.domainliste_domain'] = 'Domain';

        $lang_admin['tcbrn.default'] = '(Standard)';

        $lang_admin['tcbrn.xmailer'] = 'X-Mailer';
      } else {
        $lang_admin['tcbrn.domain'] = 'Domain';
        $lang_admin['tcbrn.domain_bearbeiten'] = 'Edit domain';
        $lang_admin['tcbrn.domain_hinzufuegen'] = 'Add domain';

        $lang_admin['tcbrn.aktiviert'] = 'Enabled';
        $lang_admin['tcbrn.aktivieren'] = 'Enable';
        $lang_admin['tcbrn.deaktiviert'] = 'Disabled';
        $lang_admin['tcbrn.deaktivieren'] = 'Disable';
        $lang_admin['tcbrn.jetzt_aktivieren'] = 'Enable!';
        $lang_admin['tcbrn.jetzt_deaktivieren'] = 'Disable!';

        $lang_admin['tcbrn.modus'] = 'Mode';
        $lang_admin['tcbrn.modus_standard'] = 'Default';
        $lang_admin['tcbrn.modus_standard_hinweis'] = 'Domain and all subdomains';
        $lang_admin['tcbrn.modus_exakt'] = 'Precise';
        $lang_admin['tcbrn.modus_exakt_hinweis'] = 'Wildcard * (0-n letters) and ? (exactly 1 letter) allowed';

        $lang_admin['tcbrn.domainliste'] = 'Domainlist';
        $lang_admin['tcbrn.domainliste_konfigurieren'] = 'configure';
        $lang_admin['tcbrn.domainliste_sichtbar'] = 'Visible';
        $lang_admin['tcbrn.domainliste_standard'] = 'Default';
        $lang_admin['tcbrn.domainliste_domain'] = 'Domain';

        $lang_admin['tcbrn.default'] = '(Default)';

        $lang_admin['tcbrn.xmailer'] = 'X-Mailer';
      }
    }

    function OnLoad() {
      if(ADMIN_MODE || empty($_SERVER['SERVER_NAME'])) {
        return;
      }
      $domains = $this->_getDomains(true);
      $cDomain = $_SERVER['SERVER_NAME'];
      //$cDomain = 'de.emailpoint.eu';
      $dSettings = null;
      foreach ($domains as $domain) {
        $regex = $domain['domain'];
        if ($domain['mode'] == 'exact') {
          $regex = '^' . str_replace(array('*', '?'), array(
            '.*',
            '.'), preg_quote($regex)) . '$';
        } elseif ($domain['mode'] == 'standard') {
          $regex = '^(.*\.)?' . preg_quote($regex) . '$';
        }
        if (preg_match('#' . $regex . '#', $cDomain)) {
          $dSettings = $domain;
          break;
        }
      }
      if ($dSettings == null) {
        return;
      }
      global $bm_prefs;
      // Sprache setzen
      if (!isset($_SESSION['bm_sessionLanguage']) && !isset($_GET['language']) && !(isset($_POST['language']) && isset($_REQUEST['action']) && $_REQUEST['action'] == 'login')) {
        global $currentLanguage;
        if(!empty($dSettings['language']) && $currentLanguage != $dSettings['language']) {
          $_REQUEST['language'] = $_GET['language'] = $_POST['language'] = $bm_prefs['language'] = $dSettings['language'];
          ReadLanguage();
        }
      }

      $fields = array(
        'titel' => 'title', // Titel
        'template' => 'template', // Template
        'std_land' => 'country', // Land
        'selfurl' => 'selfurl', // URL zu b1gMail
        'ssl_url' => 'ssl_url', // SSL-URL zu b1gMail
        'ssl_login_option' => 'ssl_login_option', // SSL bei Login an-/abwählbar
        'ssl_login_enable' => 'ssl_login_enable', // Login standardmäßig per SSL?
        'logouturl' => 'logouturl', // Logout-URL
        'std_gruppe' => 'std_gruppe', // Standard-Gruppe
        'usr_status' => 'usr_status' // Status nach Registrierung
      );
      foreach($fields as $bmKey => $tcbrnKey) {
        if(isset($bm_prefs[$bmKey]) && !empty($dSettings[$tcbrnKey])) {
          $bm_prefs[$bmKey] = $dSettings[$tcbrnKey];
        }
      }
      // Domainliste setzen
      if (isset($bm_prefs['domains']) && !empty($dSettings['domainlist'])) {
        $bm_domains = $bm_prefs['domains'];
        if(!is_array($bm_domains)) {
          $bm_domains = explode(':', $bm_prefs['domains']);
        }
        $domains = explode(':', $dSettings['domainlist_domains']);
        if(array_search($dSettings['domainlist_default'], $domains) !== false) {
          $newDomains[] = $dSettings['domainlist_default'];
        }
        foreach($domains as $domain) {
          if($domain != $dSettings['domainlist_default'] && array_search($domain, $bm_domains) !== false) {
            $newDomains[] = $domain;
          }
        }
        if(!is_array($bm_prefs['domains'])) {
          $newDomains = implode(':', $newDomains);
        }
        $bm_prefs['domains'] = $newDomains;
      }

      $this->domain_xmailer = $dSettings['xmailer'];
    }

    function OnGetDomainList(&$list) {
      global $bm_prefs;
      $list = array_intersect($bm_prefs['domains'], $list);
    }

    function getClassReplacement($class) {
      if ($class == 'BMMailBuilder') {
        return 'TCBrandPlugin_BMMailBuilder';
      }
      return false;
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
        1 => array('title' => $lang_admin['tcbrn.domain'],
          'link' => $this->_adminLink() . '&amp;action=domain&amp;',
          'relIcon' => 'ico_prefs_common.png',
          'active' => $_REQUEST['action'] == 'domain')/*,
        2 => array('title' => $lang_admin['prefs'],
          'link' => $this->_adminLink() . '&amp;action=settings&amp;',
          'active' => $_REQUEST['action'] == 'settings')*/);

      $tpl->assign('tabs', $tabs);
      switch ($_REQUEST['action']) {
        case 'settings':
          $this->_adminSettings();
          break;
        case 'domain':
          $this->_adminDomain();
          break;
        default:
          $this->_adminStart();
      }
      $tpl->assign('pageURL', $this->_adminLink());
      $tpl->assign('tccrn_name', $this->name);
      $tpl->assign('tpldir_user', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
    }

    function _adminSettings() {
      global $tpl, $db;
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $logLevel = 0;
          if (isset($_REQUEST['loglevel']) && is_array($_REQUEST['loglevel'])) {
            foreach ($_REQUEST['loglevel'] as $val) {
              $logLevel |= $val;
            }
          }
        $db->Query('UPDATE {pre}tcbrn_plugin_settings SET loglevel = ?', $logLevel);
        $this->_config = null;
      }
      $tpl->assign('page', $this->_templatePath('tcbrn.admin.settings.tpl'));
      $tpl->assign('tcbrn_prefs', $this->_getConfig());
    }

    function _adminRedirect() {
      header('Location: ' . $this->_adminLink(true));
      exit();
    }

    function _adminDomain() {
      global $db, $tpl, $bm_prefs;
      $domains = $bm_prefs['domains'];
      if(!is_array($domains)) {
        $domains = explode(':', $domains);
      }
      /* @var $db DB */
      if (isset($_GET['id']) && strlen($_GET['id'])) {
        $res = $db->Query('SELECT * FROM {pre}tcbrn_plugin_domains WHERE domainid = ?', $_GET['id']);
        if (!$res->RowCount()) {
          $res->Free();
          $this->_adminRedirect();
        }
        $row = $res->FetchArray(MYSQL_ASSOC);
        $res->Free();
        $row['domainlist_domains'] = explode(':', $row['domainlist_domains']);
        $tpl->assign('tcbrn_data', $row);
      } else {
        $tpl->assign('tcbrn_data', array(
          'active' => 1,
          'mode' => 'standard',
          'domain' => $_SERVER['SERVER_NAME'],
          'selfurl' => $bm_prefs['selfurl'],
          'domainlist' => 0,
          'domainlist_domains' => $domains,
          'title' => $bm_prefs['titel']));
      }
      $edit = !empty($row);
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $data = $_POST;
        if($edit) {
          $data['domainid'] = $row['domainid'];
        }
        if(!empty($data['domainlist_domains'])) {
          $data['domainlist_domains'] = implode(':', $data['domainlist_domains']);
        }
        $this->_saveDomain($data);
        $this->_adminRedirect();
      }

      $templates = GetAvailableTemplates();
      if(function_exists('LoadTbxConfigDescriptors')) {
        $templates = array_keys($templates);
      }
      $countries = CountryList();
      $languages = GetAvailableLanguages();
      $groups = BMGroup::GetSimpleGroupList();
      $tpl->assign('groups', $groups);
      $tpl->assign('domainList', $domains);
      $tpl->assign('templates', $templates);
      $tpl->assign('countries', $countries);
      $tpl->assign('languages', $languages);
      $tpl->assign('page', $this->_templatePath('tcbrn.admin.edit.tpl'));
  }

  function _adminStart() {
    global $currentLanguage, $tpl, $db;
    /* @var $db DB */
    if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
      if (!empty($_REQUEST['singleID'])) {
        $_REQUEST['domains'] = array(
        $_REQUEST['singleID']);
      }
      if (!empty($_REQUEST['singleAction'])) {
        $_REQUEST['massAction'] = $_REQUEST['singleAction'];
      }
      if (!empty($_REQUEST['domains']) && !empty($_REQUEST['massAction'])) {
        $domains = array();
        foreach ($_REQUEST['domains'] as $domain) {
          $domains[] = $db->Escape($domain);
        }
        $list = '"' . implode('", "', $domains) . '"';
        if ($_REQUEST['massAction'] == 'delete') {
          $db->Query('DELETE FROM {pre}tcbrn_plugin_domains WHERE domainid IN (' . $list . ')');
        } elseif ($_REQUEST['massAction'] == 'activate') {
          $db->Query('UPDATE {pre}tcbrn_plugin_domains SET active = 1 WHERE domainid IN (' . $list . ')');
        }  elseif ($_REQUEST['massAction'] == 'deactivate') {
          $db->Query('UPDATE {pre}tcbrn_plugin_domains SET active = 0 WHERE domainid IN (' . $list . ')');
        }
      }
    }

    $domains = $this->_getDomains();

    $tpl->assign('tcbrn_domains', $domains);
    $tpl->assign('languages', GetAvailableLanguages());
    $tpl->assign('countries', CountryList());

    $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s&js=1&language=%s&version=%s', $this->update_url, urlencode($this->internal_name), urlencode(B1GMAIL_VERSION), $currentLanguage, $this->version);
    $tpl->assign('updateURL', htmlspecialchars($queryURL));
    $tpl->assign('notices', $this->getNotices());
    $tpl->assign('page', $this->_templatePath('tcbrn.admin.start.tpl'));
  }

  function _getDomains($notUi = false) {
    global $db;
    if(!$notUi) {
      $sql = 'SELECT * FROM {pre}tcbrn_plugin_domains ORDER BY active DESC, domain ASC';
    } else {
      $sql = 'SELECT * FROM {pre}tcbrn_plugin_domains WHERE active = 1 ORDER BY mode DESC, LENGTH(domain) DESC';
    }
    $res = $db->Query($sql);
    $domains = array();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $domains[] = $row;
    }
    return $domains;
  }

  function _saveDomain($data) {
    global $db;
    $sql = '{pre}tcbrn_plugin_domains SET active = ?, mode = ?, domain = ?, template = ?, language = ?, country = ?, title = ?, domainlist = ?, domainlist_domains = ?, domainlist_default = ?, selfurl = ?, std_gruppe = ?, usr_status = ?, xmailer = ?, ssl_url = ?, ssl_login_option = ?, ssl_login_enable = ?, logouturl = ?';
      if (isset($data['domainid'])) {
        $sql = 'UPDATE ' . $sql . ' WHERE domainid = \'' . $db->Escape($data['domainid']) . '\'';
      } else {
        $sql = 'INSERT INTO ' . $sql;
      }
      $db->Query($sql, !empty($data['active']), $data['mode'], $data['domain'], $data['template'], $data['language'], $data['country'], $data['title'], !empty($data['domainlist']), $data['domainlist_domains'], $data['domainlist_default'], $data['selfurl'], $data['std_gruppe'], $data['usr_status'], $data['xmailer'], $data['ssl_url'], $data['ssl_login_option'], $data['ssl_login_enable'], $data['logouturl']);
  }

}
/**
 * register plugin
 */
$plugins->registerPlugin('TCBrandPlugin');


if(!class_exists('BMMailBuilder'))
  include(B1GMAIL_DIR . 'serverlib/mailbuilder.class.php');

class TCBrandPlugin_BMMailBuilder extends BMMailBuilder {

  /**
   * add header field
   *
   * @param string $key
   * @param string $value
   */
  function AddHeaderField($key, $value) {
    global $plugins;
    if(isset($plugins->_plugins['TCBrandPlugin'])) {
      $brand = $plugins->_plugins['TCBrandPlugin']['instance'];
      if(!empty($brand->domain_xmailer) && strtolower($key) == 'x-mailer') {
        $value = $brand->domain_xmailer;
      }
    }
    parent::AddHeaderField($key, $value);
  }
}

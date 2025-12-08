<?php
/**
 * BetterMailSearch
 * 
 * @link       http://www.thinkclever.ch/
 * @copyright  2007-2011 ThinkClever IT Solutions
 * @version    $Id: fulltext.plugin.php 97 2011-08-28 13:40:08Z caspar $
 */

/**
 * BetterMailSearch
 *
 */
class BetterMailSearch extends BMPlugin {
    
    function BetterMailSearch() {
        $this->name = 'BetterMailSearch';
        $this->author = 'ThinkClever GmbH';
        $this->web = 'http://www.thinkclever.ch/';
        $this->mail = 'info@thinkclever.ch';
        $this->version = '1.6.0';
        $this->designedfor = '7.3.0';
        $this->type = BMPLUGIN_DEFAULT;
        $this->order = -10;
        
        $this->admin_pages = true;
        $this->admin_page_title = $this->name;
        $this->admin_page_icon = 'tcbms_icon32.png';
        
        $this->website = 'http://my.b1gmail.com/details/4/';
        $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
    }
    
    function Install() {
        global $db, $mysql;
    $databaseStructure = // Tables: 3; Hash: 3d9f1bd677eef5b6a80477f343681390
        'vZRLc9owEMe/SkcXyEwPlgwmiFOPmUloJzM99ORR7AU0kWUqyaWB4bt3ZcDYmGeS6QWs1/52//s'
      . 'QPOQry8OAk9XcwNolL5mN56qYSh1bECaZxVKn8JeMBGf+asTJRIJKrd/p8ZXkAX5Emw/L+5xYGc'
      . 'uUjCSnuKZ4X2rXpfTuS6GtnGoozxieMU7G3/0ixEXIyY/nB7/qjcb42/ePe5yIwuXoQmIgA+3Ia'
      . 'O3t1oD3JdAu3sl8+vm4YeIzTgKyJaMeJYrVUTQoWZmQqkYbVLB3MsJ2OEle+Fg3AOaxmVDKY/oX'
      . 'QiqNnmKtt95iPqHMX5n8Qan807fnX36LtjOJzygam7zGe6VrFxsZ8HfD3d1KqdrtQxHX3is2PFN'
      . '/i9ykx8uPHS2/xf8sv0EJ3Li4RaJUf7zjwnRDdrkoxudTxC6kaC87Gih0XHlTu1d3cqN3dFxv56'
      . 'Se2uNiU9pSG89B40uoYh/6nSLrdpZWdb52sjf7W3Xuzlfq+KAfGvpSjBFtxXYOMpmByU3VF77Od'
      . 'kKzIAhuxDRbO9qKHudzJzMJBnTVf4NdUDafIB/jWoL0f1rCrcE1mp3RNjX2tq+cLScpvQZleIIS'
      . 'LwsFbnkz7fgY6zf0RFvOwGQCJsb8pAq5rtWUwfXDDHMdBi1mdIqJmXIfZuI+6x8iBw0kugXlKJu'
      . 'aHKyFj+Bw2vSCYXQIvG/kEmNciJmx2AtSK2yJV59IoTGf033J+lmArfxWunBNNukhddig4u5cCb'
      . 'ecCeVQXTBKFC+fiaONucJQ1hSc11WgeMns8yNszdmAr3A0/gM=';
        $databaseStructure = unserialize(gzinflate(base64_decode($databaseStructure)));
        $structure = array();
        foreach ($databaseStructure as $tableName => $data) {
            $tableName = str_replace('{pre}', $mysql['prefix'], $tableName);
            $structure[$tableName] = $data;
        }
        
        $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', 'BetterMailSearch', '_BetterMailSearch');
        
        @set_time_limit(360);
        SyncDBStruct($structure);
        
        // prefs row?
        $res = $db->Query('SELECT COUNT(*) FROM {pre}tcbms_plugin_settings');
        list ($rowCount) = $res->FetchArray(MYSQL_NUM);
        $res->Free();
        
        // insert prefs row
        if ($rowCount < 1) {
            $db->Query("INSERT INTO `{pre}tcbms_plugin_settings` (`engine`, `zsl_speicherort`, `index_optimieren`, `index_optimieren_zeit`, `index_optimieren_zeit_zuletzt`, `treffer_hardlimit`, `treffer_softlimit`, `email_groesse`, `wahrscheinlichkeit_anzeigen`, `platzhalter_erlauben`, `detail_ansicht_anzeigen`) VALUES ('mysql', '', 'zeit', 3, 0, 100, 24, 10, 0, 1, 0);");
        }
        
        // log
        PutLog(sprintf('%s v%s installed', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
        
        return true;
    }
    
    function Uninstall() {
        global $db, $cacheManager;
        $db->Query('DROP TABLE `{pre}tcbms_plugin_search_word`');
        $db->Query('DROP TABLE `{pre}tcbms_plugin_search_index`');
        $db->Query('DROP TABLE `{pre}tcbms_plugin_settings`');
        $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', '_BetterMailSearch', 'BetterMailSearch');
        if (is_object($cacheManager)) {
          $cacheManager->Delete('tcbms_storageUsage');
          $cacheManager->Delete('tcbms_countIndexedMails');
        }
        
        // log
        PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
        
        return true;
    }
    
    function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
        if (strpos($lang, 'deutsch') !== false) {
            //Deutsch!
            $lang_admin['tcbms.willkommen_text'] = 'Vielen Dank, dass Sie sich f&uuml;r das Produkt ' . $this->name . ' (Version ' . $this->version . ') der ThinkClever GmbH entschieden haben.';
            $lang_admin['tcbms.indexierte_mails'] = 'Mails im Index';
            $lang_admin['tcbms.index_groesse'] = 'Indexgr&ouml;sse';
            $lang_admin['tcbms.index_letzt_optimierung'] = 'Letzte Index-Optimierung';
            $lang_admin['tcbms.unicode_support'] = 'Unicode-Support';
            
            $lang_admin['tcbms.warnung'] = 'Warnung';
            $lang_admin['tcbms.keine_gruppe_aktiviert'] = 'Bitte aktivieren Sie ' . $this->name . ' f&uuml;r mindestens eine Gruppe. Sie finden diese Einstellung in den Gruppeneinstellungen von b1gMail.';
            
            $lang_admin['tcbms.javascript_aktivieren'] = 'Damit Sie diese Funktion nutzen k&ouml;nnen, m&uuml;ssen Sie JavaScript im Browser aktivieren.';
            
            $lang_admin['tcbms.jetzt_regenerieren'] = 'Sie k&ouml;nnen den Index um fehlende Eintr&auml;ge erg&auml;nzen, falls er beispielsweise fehlerhaft ist, noch nie generiert, versehentlich gel&ouml;scht wurde oder Sie BetterMailSearch f&uuml;r eine neue Gruppe aktiviert haben.';
            $lang_admin['tcbms.index_regenerieren'] = 'Index erg&auml;nzen';
            $lang_admin['tcbms.index_wird_regeneriert'] = 'Index wird erg&auml;nzt';
            
            $lang_admin['tcbms.jetzt_loeschen'] = 'Wenn der Index schwer besch&auml;digt ist, muss er evt. gel&ouml;scht werden. Danach sollte er unbedingt mit der Funktion &quot;' . $lang_admin['tcbms.index_regenerieren'] . '&quot; regeneriert werden. Diese Funktion sollte nur in Ausnahmef&auml;llen genutzt werden.';
            $lang_admin['tcbms.index_loeschen'] = 'Index l&ouml;schen';
            $lang_admin['tcbms.index_wirklich_loeschen'] = 'M&ouml;chten Sie den Index wirklich l&ouml;schen?';
            
            $lang_admin['tcbms.jetzt_optimieren'] = 'Wenn viele Mails hinzugef&uuml;gt, gel&ouml;scht oder ver&auml;ndert wurden, kann der Index an Geschwindigkeit verlieren. Falls dies der Fall ist, k&ouml;nnen Sie hier den Index optimieren.';
            $lang_admin['tcbms.index_optimieren'] = 'Index optimieren';
            $lang_admin['tcbms.index_wird_optimiert'] = 'Index wird optimiert';
            
            $lang_admin['tcbms.mails_verarbeitet'] = 'Datens&auml;tze verarbeitet';
            
            $lang_admin['tcbms.index_sofort'] = 'Nach jeder &Auml;nderung (nicht empfohlen)';
            $lang_admin['tcbms.index_zeit'] = 'H&ouml;chstens alle';
            $lang_admin['tcbms.minuten'] = 'Minuten';
            $lang_admin['tcbms.index_nie'] = 'Nur manuell';
            $lang_admin['tcbms.max_email_groesse'] = 'Max. E-Mail-Text-Gr&ouml;sse';
            $lang_admin['tcbms.hard_limit'] = 'Max. Anzahl Treffer (Hard-Limit)';
            $lang_admin['tcbms.hard_limit_hinweis'] = 'Ein hoher Wert verbessert die Qualit&auml;t der Ergebnisse, verlangsamt aber die Suche.';
            $lang_admin['tcbms.hard_limit_text_1'] = 'Suche nach';
            $lang_admin['tcbms.hard_limit_text_2'] = 'Treffern beenden';
            $lang_admin['tcbms.soft_limit'] = 'Max. Anzahl Treffer';
            $lang_admin['tcbms.soft_limit_text_1'] = 'Nur';
            $lang_admin['tcbms.soft_limit_text_2'] = 'Treffer anzeigen';
            $lang_admin['tcbms.wahrscheinlichkeit_anzeigen'] = 'Wahrscheinlichkeit anzeigen';
            $lang_admin['tcbms.detail_ansicht_anzeigen'] = 'Detail-Ansicht aktivieren';
            $lang_admin['tcbms.platzhalter_erlauben'] = 'Platzhalter erlauben';
            $lang_admin['tcbms.platzhalter_erlauben_hinweis'] = 'Der Suchalgorithmus erlaubt die Platzhalter * (0-n Zeichen) und ? (genau 1 Zeichen). Allerdings k&ouml;nnen Platzhalter die Suche verlangsamen.';
            $lang_admin['tcbms.aktivieren'] = 'Mail-Suche durch ' . $this->name . ' ersetzen:';
            
            $lang_user['tcbms.wahrscheinlichkeit'] = 'Wahrscheinlichkeit';
            $lang_user['tcbms.detail_ansicht'] = 'E-Mails durchsuchen...';
            $lang_user['tcbms.suchbegriff'] = 'Suchbegriff';
            $lang_user['tcbms.suchen'] = 'Suchen';
            $lang_user['tcbms.am_suchen'] = 'Suchen';
            $lang_user['tcbms.spam_durchsuchen'] = 'Auch Spammails durchsuchen';
        } else {
            //Englisch!
            $lang_admin['tcbms.willkommen_text'] = 'Thank you for purchasing ' . $this->name . ' (version ' . $this->version . ') written by ThinkClever GmbH.';
            $lang_admin['tcbms.indexierte_mails'] = 'Indexed mails';
            $lang_admin['tcbms.index_groesse'] = 'Size of the index';
            $lang_admin['tcbms.index_letzt_optimierung'] = 'Last index optimisation';
            $lang_admin['tcbms.unicode_support'] = 'Unicode support';
            
            $lang_admin['tcbms.warnung'] = 'Warning';
            $lang_admin['tcbms.keine_gruppe_aktiviert'] = 'Please activate ' . $this->name . ' for at least one group. You find this option in the group options of b1gMail.';
            
            $lang_admin['tcbms.javascript_aktivieren'] = 'In order to use this function, you need to enable JavaScript in your browser.';
            
            $lang_admin['tcbms.jetzt_regenerieren'] = 'You may complete the index if it was never generated before, deleted by mistake or when you enabled BetterMailSearch for an additional group.';
            $lang_admin['tcbms.index_regenerieren'] = 'Complete index';
            $lang_admin['tcbms.index_wird_regeneriert'] = 'Competing index';
            
            $lang_admin['tcbms.jetzt_loeschen'] = 'You may delete the index if it contains errors. Afterwards you should use &quot;' . $lang_admin['tcbms.index_regenerieren'] . '&quot;.';
            $lang_admin['tcbms.index_loeschen'] = 'Delete index';
            $lang_admin['tcbms.index_wirklich_loeschen'] = 'Dou you really want to delete the index?';
            
            $lang_admin['tcbms.jetzt_optimieren'] = 'If many mails were added, deleted or edited, the index can get slow. If this is the case, you might want to optimise your index here.';
            $lang_admin['tcbms.index_optimieren'] = 'Optimise index';
            $lang_admin['tcbms.index_wird_optimiert'] = 'Optimising index';
            
            $lang_admin['tcbms.mails_verarbeitet'] = 'data records processed';
            
            $lang_admin['tcbms.index_sofort'] = 'After each update (not recommended)';
            $lang_admin['tcbms.index_zeit'] = 'No more than every';
            $lang_admin['tcbms.minuten'] = 'minutes';
            $lang_admin['tcbms.index_nie'] = 'Manually only';
            $lang_admin['tcbms.max_email_groesse'] = 'Max. mail size';
            $lang_admin['tcbms.hard_limit'] = 'Outside estimate of hits (hard-limit)';
            $lang_admin['tcbms.hard_limit_hinweis'] = 'A high value improves the quality of the result, but it slows down the search.';
            $lang_admin['tcbms.hard_limit_text_1'] = 'Search terminated after';
            $lang_admin['tcbms.hard_limit_text_2'] = 'hits';
            $lang_admin['tcbms.soft_limit'] = 'Outside estimate of hits (quick search)';
            $lang_admin['tcbms.soft_limit_text_1'] = 'Show';
            $lang_admin['tcbms.soft_limit_text_2'] = 'hits only';
            $lang_admin['tcbms.wahrscheinlichkeit_anzeigen'] = 'Display likeliness';
            $lang_admin['tcbms.detail_ansicht_anzeigen'] = 'Enable detailed view';
            $lang_admin['tcbms.platzhalter_erlauben'] = 'Allow wildcard';
            $lang_admin['tcbms.platzhalter_erlauben_hinweis'] = 'The search-algorithm allows the wildcards *, (0-n letters) and ? (exactly 1 letter). Though wildcards can slow down the search.';
            $lang_admin['tcbms.aktivieren'] = 'Replace default mail search with ' . $this->name . ':';
            
            $lang_user['tcbms.wahrscheinlichkeit'] = 'Likeliness';
            $lang_user['tcbms.detail_ansicht'] = 'Detailed view...';
            $lang_user['tcbms.suchbegriff'] = 'Search term';
            $lang_user['tcbms.suchen'] = 'Search';
            $lang_user['tcbms.am_suchen'] = 'Searching';
            $lang_user['tcbms.spam_durchsuchen'] = 'Don\'t hide spam';
        }
        
        $this->RegisterGroupOption('tcbms_eingeschaltet', FIELD_CHECKBOX, $lang_admin['tcbms.aktivieren']);
    }
    
    function OnCron() {
      global $db;
        $config = $this->_getConfig();
        if ($config['index_optimieren'] == 'sofort' || ($config['index_optimieren'] == 'zeit' && ($config['index_optimieren_zeit_zuletzt'] + ($config['index_optimieren_zeit'] * TIME_ONE_MINUTE) < time()))) {
            $this->_optimizeAndCleanupIndex();
        }
    }
    
    var $_folderIcons = array(
      FOLDER_INBOX    => 'menu_ico_inbox',
      FOLDER_OUTBOX    => 'menu_ico_outbox',
      FOLDER_DRAFTS    => 'menu_ico_drafts',
      FOLDER_SPAM      => 'menu_ico_spam',
      FOLDER_TRASH    => 'menu_ico_trash'
    );
    
    function _getMailIcon($folder) {
    return isset($this->_folderIcons[$folder]) ? $this->_folderIcons[$folder] : 'menu_ico_folder';
    }
    
    function OnSearch($query, $dateFrom = 0, $dateTo = 0) {
        global $lang_user, $thisUser, $userRow, $currentCharset;
        if (!$this->GetGroupOptionValue('tcbms_eingeschaltet')) {
            return array();
        }
    
        $config = $this->_getConfig();
        $hits = $this->_search($query, $thisUser, true, array('limit' => $config['treffer_softlimit']), false, false, $dateFrom, $dateTo);
        if (count($hits) > 0) {
            $results = array();
            if($_REQUEST['action'] != 'search' && $config['detail_ansicht_anzeigen']) {
              $results[] = array('icon' => 'ico_search', 
                  'title' => $lang_user['tcbms.detail_ansicht'], 
                  'link' => sprintf('start.php?action=tcbms_search&q=%s&', urlencode($query)));
            }
            foreach ($hits as $hit) {
                $fullTitle = $title = $this->_convert('utf-8', $currentCharset, $hit->subject);
                $score = ' (' . (round($hit->score * 100)) . '%)';
                if ($config['wahrscheinlichkeit_anzeigen'] && $_REQUEST['action'] != 'search') {
                    $maxLength = 25; // Von b1gMail vorgegeben
                    if (strlen($title . $score) > $maxLength) {
                        $title = substr($title, 0, $maxLength - strlen($score) - 3) . '...';
                    }
                    $title .= $score;
                    $fullTitle .= $score;
                }
                $results[] = array(
                    'icon' => $this->_getMailIcon($hit->folder),
          'bold' => ($hit->flags & FLAG_UNREAD) != 0,
          'strike' => ($hit->flags & FLAG_DELETED) != 0 || ($hit->folder == FOLDER_TRASH),
                    'title' => $title,
                    'fullTitle' => $fullTitle,
          'size' => $hit->size,
          'date' => $hit->date,
          'id' => $hit->mailid,
                    'link' => sprintf('email.read.php?id=%d&', $hit->mailid));
            }
            if($_REQUEST['action'] != 'search') {
              $results = array_slice($results, 0, $config['treffer_softlimit'] + 1);
            }
      
      $massActions = array(
        $lang_user['actions']   => array(
          'markread'    => $lang_user['markread'],
          'markunread'  => $lang_user['markunread'],
          'delete'    => $lang_user['delete']
        ),
        $lang_user['move']    => array()
      );
      
      if(!class_exists('BMMailbox'))
        include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');
      $mailbox = _new('BMMailbox', array($userRow['id'], $userRow['email'], $thisUser));
      $folders = $mailbox->GetFolderList(false);
      foreach($folders as $folderID=>$folder)
        $massActions[$lang_user['move']]['moveto_'.$folderID] = $lang_user['moveto'] . ' &quot;' . HTMLFormat($folder['title']) . '&quot;';
            
            return array(
                0 => array(
          'name' => __CLASS__ . '_mails',
                    'title' => $lang_user['mails'], 
                    'results' => $results,
          'massActions' => $massActions));
        }
        return array();
    }
    
    /**
   * handle search mass action
   *
   * @param string $category Category name
   * @param string $action Action name
   * @param array $items Array with item IDs
   * @return bool Handled?
   */
  function HandleSearchMassAction($category, $action, $items) {
    global $thisUser, $userRow;
    
    if($category == __CLASS__ . '_mails') {
      if(!class_exists('BMMailbox')) {
        include(B1GMAIL_DIR . 'serverlib/mailbox.class.php');
      }
      $mailbox = _new('BMMailbox', array($userRow['id'], $userRow['email'], $thisUser));
      
      if($action == 'delete') {
        foreach($items as $mailID) {
          $mailbox->DeleteMail((int)$mailID);
        }
      } else if($action == 'markread') {
        foreach($items as $mailID) {
          $mailbox->FlagMail(FLAG_UNREAD, false, (int)$mailID);
        }
      } else if($action == 'markunread') {
        foreach($items as $mailID) {
          $mailbox->FlagMail(FLAG_UNREAD, true, (int)$mailID);
        }
      } else if(substr($action, 0, 7) == 'moveto_') {
        $destFolderID = (int)substr($action, 7);
        $mailbox->MoveMail($items, $destFolderID);
      }
    }
  }
    
    function GetSearchCategories() {
      global $lang_user;
        if (!$this->GetGroupOptionValue('tcbms_eingeschaltet')) {
            return array();
        }
      return array(__CLASS__ . '_mails' => array('title' => $lang_user['mails'], 'icon' => 'mail_markunread'));
    }
    
    var $_file;
    
    function FileHandler($file, $action) {
      $this->_file = $file;
        if (($file == 'start.php' && $action == 'userSearch') || $file == 'search.php') {
            global $bm_prefs;
            if (!$this->GetGroupOptionValue('tcbms_eingeschaltet')) {
                return;
            }
            $searchIn = @unserialize($bm_prefs['search_in']);
            if (!is_array($searchIn))
                $searchIn = array();
            if (isset($searchIn['mails'])) {
                unset($searchIn['mails']);
            }
            $bm_prefs['search_in'] = serialize($searchIn);
        }
        if ($file == 'start.php' && $action == 'tcbms_search') {
            global $tpl, $lang_user, $thisUser, $currentCharset, $userRow, $bm_prefs;
            if (isset($_REQUEST['perpage'])) {
                $thisUser->SetPref('tcbms_perPage', (int) $_REQUEST['perpage']);
            }
            if (!isset($_REQUEST['q'])) {
                $_REQUEST['q'] = '';
            }
            $q = $_REQUEST['q'];
            if (!empty($_REQUEST['js'])) {
              $q = CharsetDecode($q, 'utf-8');
            }
            if (!isset($_REQUEST['spam'])) {
                $_REQUEST['spam'] = 0;
            }
            $spam = $_REQUEST['spam'];
            $sort = array('sortField' => '', 'sortOrder' => '');
            if (isset($_REQUEST['sort'])) {
                $sort['sortField'] = $_REQUEST['sort'];
                if (!isset($_REQUEST['order'])) {
                    $_REQUEST['order'] = 'asc';
                }
                $sort['sortOrder'] = @$_REQUEST['order'];
                $pageNo = @$_REQUEST['page'];
                $tpl->assign('sortOrder', $sort['sortOrder']);
                $tpl->assign('sortOrderInv', $sort['sortOrder'] == 'asc' ? 'desc' : 'asc');
                $tpl->assign('sortColumn', $sort['sortField']);
            } else {
                $sort = null;
                $pageNo = 1;
                $tpl->assign('sortOrder', 'desc');
                $tpl->assign('sortOrderInv', 'asc');
                $tpl->assign('sortColumn', 'score');
            }
            
            $limit = $thisUser->GetPref('tcbms_perPage');
            if (empty($limit)) {
                $limit = $bm_prefs['ordner_proseite'];
            }
            $limit = (int)$limit;
            
            $count = $this->_search($q, $thisUser, true, $sort, $spam, true);
            if(empty($count)) {
              $count = 0;
            }
            $sort['limit'] = $limit;
            $sort['offset'] = ($pageNo - 1) * $sort['limit'];
            $pageCount = ceil($count / $sort['limit']);
            $pageNo = max(1, min($pageCount, $pageNo));
            $tpl->assign('perPage', $limit);
            $tpl->assign('enablePreview', $userRow['preview'] == 'yes');
            $tpl->assign('pageNo', $pageNo);
            $tpl->assign('pageCount', $pageCount);
            
            $hits = $this->_search($q, $thisUser, true, $sort, $spam);
            $results = array();
            if (count($hits) > 0) {
                foreach ($hits as $hit) {
                    $title = $this->_convert('utf-8', $currentCharset, $hit->subject);
                    $hitFrom = $this->_convert('utf-8', $currentCharset, $hit->from);
                    $from = ParseMailList($hitFrom);
                    if (count($from) == 0)
                        $from_name = $from_mail = $hitFrom;
                    else {
                        $from_name = (isset($from[0]['name']) && trim($from[0]['name']) != '' ? $from[0]['name'] : $from[0]['mail']) . (count($from) > 1 ? ', ...' : '');
                        $from_mail = $from[0]['mail'];
                    }
                    $results[$hit->mailid] = array(
                        'flags' => $hit->flags, 
                        'subject' => $title, 
                        'from_name' => $from_name, 
                        'from_mail' => $from_mail, 
                        'size' => $hit->size, 
                        'score' => $hit->score, 
                        'timestamp' => $hit->date, 
                        'link' => sprintf('email.read.php?id=%d&', $hit->mailid));
                }
                //$results = array_slice($results, 0, $config['treffer_softlimit']);
            }
            
            include (B1GMAIL_DIR . '/serverlib/mailbox.class.php');
            include (B1GMAIL_DIR . '/serverlib/email.top.php');
            
            $tpl->assign('mailList', $results);
            $tpl->assign('qUrl', urlencode($q));
            $tpl->assign('q', $q);
            $tpl->assign('searchSpam', $spam);
            $tpl->assign('tcbms_prefs', $this->_getConfig());
            
            $tpl->assign('folderInfo', array('type' => 'intellifolder', 
                'title' => $lang_user['search']));
            $tpl->assign('pageContent', $this->_templatePath('tcbms.search.tpl'));
            $tpl->assign('activeTab', 'email');
            $tpl->display('li/index.tpl');
        }
    }
    
    function _optimizeAndCleanupIndex() {
      global $db;
        $index = $this->_getIndex();
        $index->cleanUp();
        $index->optimize();
        $db->Query('UPDATE {pre}tcbms_plugin_settings SET index_optimieren_zeit_zuletzt=?', time());
    }
    
    /**
     * @param int $mailID
     * @param BMMail $mail
     * @param BMMailbox $mailbox
     */
    function AfterStoreMail($mailID, &$mail, &$mailbox) {
        $user = $mailbox->_userObject;
        if (!$this->GetGroupOptionValue('tcbms_eingeschaltet', $user->_row['gruppe'])) {
            return;
        }
        $this->_addToIndex($mail, $mailID);
        $config = $this->_getConfig();
        if ($config['index_optimieren'] == 'sofort') {
            $this->_optimizeAndCleanupIndex();
        }
    }
    
    function AfterDeleteMail($mailID, &$mailbox) {
        $index = $this->_getIndex();
        $index->deleteMail($mailID);
        $config = $this->_getConfig();
        if ($config['index_optimieren'] == 'sofort') {
            $this->_optimizeAndCleanupIndex();
        }
    }
    
    function OnDeleteUser($userID) {
        $index = $this->_getIndex();
        $index->deleteUser($userID);
        $config = $this->_getConfig();
        if ($config['index_optimieren'] == 'sofort') {
            $this->_optimizeAndCleanupIndex();
        }
    }
    
    function AdminHandler() {
        global $tpl, $lang_admin;
        
        if (!isset($_REQUEST['action']))
            $_REQUEST['action'] = 'start';
        
        $tabs = array(
            0 => array('title' => $lang_admin['overview'], 
                'link' => $this->_adminLink() . '&', 
                'relIcon'  => 'info32.png',
                'active' => $_REQUEST['action'] == 'start'), 
            1 => array('title' => $lang_admin['prefs'], 
                'link' => $this->_adminLink() . '&action=settings&', 
                'relIcon'  => 'ico_prefs_common.png',
                'active' => $_REQUEST['action'] == 'settings'));
        
        $tpl->assign('tabs', $tabs);
        
        if ($_REQUEST['action'] == 'start') {
            $this->_adminStart();
        } else if ($_REQUEST['action'] == 'settings') {
            $this->_adminSettings();
        } else if ($_REQUEST['action'] == 'search') {
            $this->_adminSearch();
        }
        $tpl->assign('pageURL', $this->_adminLink());
        $tpl->assign('tcbms_name', $this->name);
        $tpl->assign('tcbms_version', $this->version);
        $config = $this->_getConfig();
        $tpl->assign('tcbms_prefs', $config);
    }
    
    function _adminSearch() {
        global $tpl;
        if (!DEBUG) {
            return;
        }
        if (isset($_REQUEST['q'])) {
            $config = $this->_getConfig();
            $hits = $this->_search($_REQUEST['q'], null, false);
            $tpl->assign('q', $_REQUEST['q']);
            $tpl->assign('hits', $hits);
        }
        $tpl->assign('page', $this->_templatePath('tcbms.prefs.search.tpl'));
    }
    
    function _adminStart() {
        global $tpl, $cacheManager, $db;
        if (isset($_REQUEST['do'])) {
            if ($_REQUEST['do'] == 'go_regenerate') {
                $this->_regenerateIndex();
            } else if ($_REQUEST['do'] == 'go_optimize') {
                $this->_optimizeIndex();
            } else if ($_REQUEST['do'] == 'go_delete') {
                $index = $this->_getIndex();
                $index->delete();
            }
            if (is_object($cacheManager)) {
                $cacheManager->Delete('tcbms_storageUsage');
                $cacheManager->Delete('tcbms_countIndexedMails');
            }
        }
        $index = $this->_getIndex();
        if (!is_object($cacheManager)) {
            $countMails = false;
        } else {
            $countMails = $cacheManager->Get('tcbms_countIndexedMails');
        }
        if ($countMails === false) {
            $countMails = $index->countIndexedMails();
            if (is_object($cacheManager))
                $cacheManager->Add('tcbms_countIndexedMails', $countMails);
        }
        if (!is_object($cacheManager)) {
            $storageUsage = false;
        } else {
            $storageUsage = $cacheManager->Get('tcbms_storageUsage');
        }
        if ($storageUsage === false) {
            $storageUsage = $index->getStorageUsage();
            if (is_object($cacheManager))
                $cacheManager->Add('tcbms_storageUsage', $storageUsage);
        }
        $res = $db->Query('SELECT COUNT(*) FROM {pre}groupoptions WHERE `module` = ? AND `key` = \'tcbms_eingeschaltet\' AND `value` = 1 AND EXISTS (SELECT id FROM {pre}gruppen WHERE id = gruppe)', 'BetterMailSearch');
        list($count) = $res->FetchArray(MYSQL_NUM);
        $tpl->assign('tcbms_groupCount', $count);
        $tpl->assign('tcbms_index_anzahl', $countMails);
        $tpl->assign('tcbms_index_groesse', $storageUsage);
        
        global $currentLanguage;
        $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s&js=1&language=%s&version=%s',
          $this->update_url,
          urlencode($this->internal_name),
          urlencode(B1GMAIL_VERSION),
          $currentLanguage,
          $this->version);
        $tpl->assign('updateURL', $queryURL);
    $tpl->assign('notices', $this->getNotices());
    
        $tpl->assign('page', $this->_templatePath('tcbms.prefs.start.tpl'));
    
    }
    
    function _adminSettings() {
        global $tpl, $db, $cacheManager;
        if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
            $db->Query('UPDATE {pre}tcbms_plugin_settings SET
              zsl_speicherort=?,
              index_optimieren=?,
              index_optimieren_zeit=?,
              treffer_softlimit=?,
              email_groesse=?,
              wahrscheinlichkeit_anzeigen=?,
              platzhalter_erlauben=?,
              detail_ansicht_anzeigen=?',
              $_POST['zsl_speicherort'],
              $_POST['index_optimieren'],
              max($_POST['index_optimieren_zeit'], 1),
              min($_POST['treffer_softlimit'], 25),
              $_POST['email_groesse'],
              !empty($_POST['wahrscheinlichkeit_anzeigen']),
              !empty($_POST['platzhalter_erlauben']),
              !empty($_POST['detail_ansicht_anzeigen']));
            
            if (is_object($cacheManager)) {
                $cacheManager->Delete('tcbms_storageUsage');
                $cacheManager->Delete('tcbms_countIndexedMails');
            }
        }
        $tpl->assign('page', $this->_templatePath('tcbms.prefs.settings.tpl'));
    }
    
    /**
     * @param string $query
     * @param BMUser $user
     * @return array
     */
    function _search($q, $user = null, $filter = true, $sort = null, $spam = false, $count = false, $dateFrom = 0, $dateTo = 0) {
        $index = $this->_getIndex();
        if (!is_array($sort)) {
            $sort = array();
        }
        if (!isset($sort['sortField']) || !in_array($sort['sortField'], array('von', 
            'betreff', 
            'fetched', 
            'size', 
            'score'))) {
            $sort['sortField'] = 'score';
        }
        if (!isset($sort['sortOrder']) || !in_array(strtoupper($sort['sortOrder']), array(
            'DESC', 
            'ASC'))) {
            $sort['sortOrder'] = 'DESC';
        }
        if (!isset($sort['offset']) || !is_int($sort['offset'])) {
            $sort['offset'] = 0;
        } else {
            $sort['offset'] = max(0, $sort['offset']);
        }
        if (!isset($sort['limit']) || !is_int($sort['limit'])) {
            $sort['limit'] = 100;
        }
        return $index->search($q, $user, $filter, $sort, $spam, $count, $dateFrom, $dateTo);
    }
    
    function _optimizeIndex() {
        @set_time_limit(120);
        $limit = 200;
        $index = $this->_getIndex();
        $firstCall = false;
      if(empty($_GET['tcbms_total'])) {
          $rowCount = $index->countOptimize();
        } else {
          $rowCount = $_GET['tcbms_total'];
        }
        $count = $index->cleanUp($limit);
        
        if ($index->countOptimize()) {
            echo $count . '/' . $rowCount;
            exit();
        } else {
            $this->_optimizeAndCleanupIndex();
            PutLog('Index cleaned up and optimized', PRIO_DEBUG, __FILE__, __LINE__);
            echo 'DONE';
            exit();
        }
        //PutLog('Index optimized!', PRIO_DEBUG, __FILE__, __LINE__);
    }
    
    function _regenerateIndex() {
        global $db;
        @set_time_limit(120);
        if(!class_exists('BMMail')) {
          require_once B1GMAIL_DIR . 'serverlib/mail.class.php';
        }
        $limit = 10;
        $index = $this->_getIndex();
        if (empty($_GET['tcbms_skip'])) {
            $skip = 0;
        } else {
            $skip = $_GET['tcbms_skip'];
        }
        $qPart = ' FROM {pre}mails, {pre}gruppen, {pre}users, {pre}groupoptions WHERE {pre}users.id = {pre}mails.userid AND {pre}users.gruppe = {pre}gruppen.id AND {pre}groupoptions.gruppe = {pre}gruppen.id AND {pre}groupoptions.key = \'tcbms_eingeschaltet\' AND {pre}groupoptions.value = 1 AND NOT EXISTS (SELECT si_id FROM bm60_tcbms_plugin_search_index WHERE si_mail_id = bm60_mails.id LIMIT 1)';
    
        if(empty($_GET['tcbms_total'])) {
          $res = $db->Query('SELECT COUNT(*)' . $qPart);
          list ($rowCount) = $res->FetchArray(MYSQL_NUM);
          $res->Free();
        } else {
          $rowCount = $_GET['tcbms_total'];
        }
        
        $res = $db->Query('SELECT {pre}mails.* ' . $qPart . ' ORDER BY {pre}mails.id ASC LIMIT ' . $skip . ', ' . $limit);
        $count = 0;
        ob_start();
        $start = time();
        $ended = false;
        while (($mail = $res->FetchArray(MYSQL_ASSOC)) !== false) {
            $bMail = _new('BMMail', array($mail['userid'], $mail, false, false));
            $index->deleteMail($bMail);
            $this->_addToIndex($bMail);
            $count++;
            if ($start + 10 < time()) { // Running out of time!
                PutLog('Is #' . $bMail->id . ' a big message?', PRIO_DEBUG, __FILE__, __LINE__);
                $ended = true;
                break;
            }
        }
        $res->Free();
        $config = $this->_getConfig();
        if ($config['engine'] == 'zsl') {
            $index->optimize();
        }
        ob_end_clean();
        //sleep(3);
        if ($count == $limit || $ended) {
            echo $count . '/' . $rowCount;
            exit();
        } else {
            PutLog('Index completed', PRIO_DEBUG, __FILE__, __LINE__);
            echo 'DONE';
            exit();
        }
    }
    
    /**
     * @param BMMail $mail
     */
    function _addToIndex($mail, $mailID = '') {
        global $cacheManager;
        if (empty($mailID)) {
            $mailID = $mail->id;
        }
        if ($mail->storedIn == STORE_FILE && (!file_exists($fileName = DataFilename($mailID)) || !is_readable($fileName))) {
            return;
        }
        $config = $this->_getConfig();
        $index = $this->_getIndex();
        $index->addMail($mail, $mailID, $config['email_groesse'] * 1024);
        
        if (!is_object($cacheManager)) {
            return;
        }
        $cacheManager->Delete('tcbms_storageUsage');
        $cacheManager->Delete('tcbms_countIndexedMails');
    }
    
    var $_includePathSet = false;
    
    var $_index = null;
    
    /**
     * Gibt den Indexer zurück
     *
     * @return TCBMS_Search_Interface
     */
    function _getIndex() {
      if($this->_index === null) {
          $config = $this->_getConfig();
          if ($config['engine'] == 'zsl') {
              if (!$this->_includePathSet) {
                  set_include_path(B1GMAIL_DIR . 'plugins/tcbms.library/' . PATH_SEPARATOR . get_include_path());
                  $this->_includePathSet = true;
              }
              $this->_index = new TCBMS_Search_ZSL($config);
          } else if ($config['engine'] == 'mysql') {
              $this->_index = new TCBMS_Search_MySQL($config);
          }
      }
        return $this->_index;
    }
    
    var $_config = null;
    
    function _getConfig() {
        global $db;
        if ($this->_config == null) {
            $res = $db->Query('SELECT * FROM {pre}tcbms_plugin_settings LIMIT 1');
            $config = $res->FetchArray();
            $res->Free();
            $config['zsl_speicherort'] = rtrim($config['zsl_speicherort'], '/') . '/';
            $this->_config = $config;
        }
        return $this->_config;
    }
    
    function _convert($in, $out, $text) {
        if (strtolower($in) == strtolower($out)) {
            return $text;
        }
        if (function_exists('mb_convert_encoding')) {
            if (($newText = @mb_convert_encoding($text, $out, $in)) !== false)
                $text = $newText;
        } else if (function_exists('iconv')) {
            if (($newText = @iconv($in, $out, $text)) !== false)
                $text = $newText;
        }
        return $text;
    }
}

/**
 * web search widget
 *
 */
class BetterMailSearch_Widget_Search extends BMPlugin {
    function BetterMailSearch_Widget_Search() {
        global $lang_user;
        
        $this->type = BMPLUGIN_WIDGET;
        $this->name = 'BetterMailSearch widget';
        $this->author = 'ThinkClever GmbH';
        $this->widgetTemplate = 'tcbms.widget.search.tpl';
        $this->widgetTitle = $lang_user['search'] . ' (' . $lang_user['email'] . ')';
        $this->widgetIcon = 'tcbms_icon12.png';
        $this->version = '1.0.3';
        $this->designedfor = '7.3.0';
        
        $this->website = 'http://my.b1gmail.com/details/4/';
        $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
    }
    
    function isWidgetSuitable($for) {
        return ($for == BMWIDGET_START);
    }
    
    function renderWidget() {
        return (true);
    }
}

/**
 * register plugin
 */
$plugins->registerPlugin('BetterMailSearch');

/**
 * register widget
 */
$plugins->registerPlugin('BetterMailSearch_Widget_Search');

/**
 * Das Interface (PHP4-Style) för den Zugriff auf den Index.
 */
class TCBMS_Search_Interface {
    function TCBMS_Search_Interface($config) {
        trigger_error('Instatiation of public interface Interface not allowed', E_USER_ERROR);
    }
    
    /**
     * Fügt eine Mail zum Index hinzu
     *
     * @param BMMail $mail
     */
    function addMail($mail, $mailID = '', $maxSize = 0) {
        trigger_error('Missing implementation of TCBMS_Search_Interface::addMail', E_USER_ERROR);
    }
    
    /**
     * Löscht eine Mail aus dem Index
     *
     * @param BMMail $mail
     */
    function deleteMail($mail) {
        trigger_error('Missing implementation of TCBMS_Search_Interface::deleteMail', E_USER_ERROR);
    }
    
    /**
     * Alle Einträge die zu user $userId gehören
     *
     * @param int $userId
     */
    function deleteUser($userId) {
        trigger_error('Missing implementation of TCBMS_Search_Interface::deleteUser', E_USER_ERROR);
    }
    
    /**
     * Optimiert den Index
     */
    function cleanUp() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::cleanUp', E_USER_ERROR);
    }
    
    /**
     * Optimiert den Index
     */
    function optimize() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::optimize', E_USER_ERROR);
    }
    
    /**
     * Zählt die Index-Optimierungs-Schritte
     */
    function countOptimize() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::countOptimize', E_USER_ERROR);
    }
    
    /**
     * Löscht den gesamten Index
     */
    function delete() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::delete', E_USER_ERROR);
    }
    
    /**
     * Gibt die Anzahl Mails im Index zurück.
     */
    function countIndexedMails() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::countIndexedFiles', E_USER_ERROR);
    }
    
    /**
     * Gibt in Byte zurück, wie viel Speicher auf der Harddisk belegt wird.
     */
    function getStorageUsage() {
        trigger_error('Missing implementation of TCBMS_Search_Interface::getStorageUsage', E_USER_ERROR);
    }
    
    /**
     * Sucht im Index nach Mails die zu $query passen und $user gehören.
     *
     * @param string $query Suchstring
     * @param BMUser $user User (null = egal)
     * @param boolean $filter true = Eingabe eines Admins, false = Eingabe einse Users
     * @param array $sort
     */
    function search($query, $user = null, $filter = false, $sort = null, $spam = true, $count = false, $dateFrom = 0, $dateTo = 0) {
        trigger_error('Missing implementation of TCBMS_Search_Interface::search', E_USER_ERROR);
    }
}

class TCBMS_Search_MySQL extends TCBMS_Search_Interface {
    var $_config;
    var $_dbCharset = null;
    
    function TCBMS_Search_MySQL($config) {
      global $bm_prefs;
        $this->_config = $config;
        if(empty($bm_prefs['db_is_utf8'])) {
          $dbCharset = $this->_getDBCharset();
          if($dbCharset != 'utf8') {
            $this->_dbCharset = $dbCharset;
          }
        }
    }
    
    /**
     * Fügt eine Mail zum Index hinzu
     *
     * @param BMMail $mail
     */
    function addMail($mail, $mailID = '', $maxSize = 0) {
        //PutLog('Adding Mail to Index...', PRIO_WARNING, __FILE__, __LINE__);
        global $currentCharset;
        if (empty($mailID)) {
            $mailID = $mail->id;
        }
        $bm71 = method_exists($mail, 'IsEncrypted');
        if(!$bm71 || !$mail->IsEncrypted()) {
          $parts = $mail->GetTextParts();
          if (!isset($parts['text'])) {
              if (!isset($parts['html'])) {
                  $text = '';
              } else {
                $text = $parts['html'];
                $search = array('@<script[^>]*?>.*?</script>@si',  // Strip out javascript
                    '@<style[^>]*?>.*?</style>@siU',  // Strip style tags properly
                    '@<[\/\!]*?[^<>]*?>@si',  // Strip out HTML tags
                    '@<![\s\S]*?--[ \t\n\r]*>@'); // Strip multi-line comments including CDATA
                $text = preg_replace($search, '', $text);
                $text = $this->_convert($currentCharset, 'utf-8', strip_tags(html_entity_decode($text)));
              }
          } else {
              $text = $this->_convert($currentCharset, 'utf-8', $parts['text']);
          }
          if($maxSize != 0 && strlen($text) > $maxSize) {
            $pos = strpos($text, ' ', $maxSize);
            $pos = min($maxSize + 30, $pos);
            $text = substr($text, 0, $pos);
          }
          $subject = $mail->GetHeaderValue('subject');
          $from = $mail->GetHeaderValue('from');
          $to = $mail->GetHeaderValue('to');
        } else {
          $text = '';
          if(!empty($mail->_row)) {
            $subject = $mail->_row['betreff'];
            $from = $mail->_row['von'];
            $to = $mail->_row['an'];
          }
        }
        $subject = $this->_convert($currentCharset, 'utf-8', $subject);
        $from = $this->_convert($currentCharset, 'utf-8', $from);
        $to = $this->_convert($currentCharset, 'utf-8', $to);
        //PutLog('Got all we need... Now add it!', PRIO_WARNING, __FILE__, __LINE__);
        $this->_addTextToIndex(array(
            0 => array('text' => $text, 'score' => 1), 
            1 => array('text' => $subject, 'score' => 5), 
            2 => array('text' => $from, 'score' => 3), 
            3 => array('text' => $to, 'score' => 3)), $mailID);
    }
    
    function _getDbCharset() {
        global $db;
        $res = $db->Query('SHOW VARIABLES');
        while (($table = $res->FetchArray()) !== false) {
            if (strtolower(@$table['Variable_name']) == 'character_set_database') {
                return $table['Value'];
            }
        }
        return 'latin1';
    }
    
    function _setDbCharset($charset = 'utf8') {
      global $db;
        if($this->_dbCharset != null) {
          $db->Query('SET NAMES ' . $charset);
        }
    }
    
    function _addTextToIndex($parts, $mailId) {
        global $db;
        $this->_setDbCharset();
        $data = array();
        foreach ($parts as $part) {
            //PutLog('Adding Part to Index...', PRIO_WARNING, __FILE__, __LINE__);
            $words = $this->_getWords($part['text']);
            foreach ($words as $word) {
                if (empty($word)) {
                    continue;
                }
                
                $swId = $this->_getWordId($word);
                
                if (isset($data[$swId])) {
                    $data[$swId]['si_count'] += $part['score'];
                } else {
                    $data[$swId]['si_sw_id'] = $swId;
                    $data[$swId]['si_mail_id'] = $mailId;
                    $data[$swId]['si_count'] = $part['score'];
                }
            }
            //PutLog('Ok...', PRIO_WARNING, __FILE__, __LINE__);
        }
        if (empty($data)) {
            return;
        }
        $baseSql = 'INSERT INTO {pre}tcbms_plugin_search_index (si_sw_id, si_mail_id, si_count) VALUES ';
        $first = false;
        $counter = 0;
        $sql = '';
        foreach ($data as $entry) {
          $counter++;
            $sql .= '(' . implode(', ', array_values($entry)) . ')';
            if($counter % 500 == 0) {
              $db->Query($baseSql . $sql);
              $first = true;
              $sql = '';
            }
            if (!$first) {
                $sql .= ' ,';
            } else {
                $first = false;
            }
        }
        if(!empty($sql)) {
          $db->Query($baseSql . substr($sql, 0, strlen($sql) - 2));
        }
        $this->_setDbCharset($this->_dbCharset);
    }
    
    function _getWords($text, $search = false) {
      if(empty($text)) {
        return array();
      }
      // Some distributions (Red Hat Enterprise, CentOS, ...) have probably forgot to include the PCRE Unicode build options...
      // Checks if PCRE is compiled with UTF-8 and Unicode support:
      $pcreUtf8 = @preg_match('/\pL/u', 'a');
      if(!$pcreUtf8) {
        $text = utf8_decode($text);
      }
      $regex = '#[^';
      if($pcreUtf8) {
        $regex .= '\pL\s0-9';
      } else {
        $regex .= 'a-zA-Z0-9\säÄöÖüÜàÀéÉèÈßçÇ';
      }
      if($search && $this->_config['platzhalter_erlauben']) {
        $regex .= '%_';
      }
      $regex .= ']#';
      if($pcreUtf8) {
        $regex .= 'u';
      }
      $text = preg_replace($regex, ' ', $text);
        $text = preg_replace('#[\t\r\n]#', ' ', $text);
        $text = preg_replace('#\s\s+#', ' ', $text);
        if(!$pcreUtf8) {
          $text = utf8_encode($text);
        }
        $text = trim($text);
        $words = explode(' ', $text);
        shuffle($words);
        return $words;
    }
    
    function _getWordId($word) {
        global $db;
        $res = $db->Query('SELECT sw_id FROM {pre}tcbms_plugin_search_word WHERE sw_word = SUBSTRING(?, 1, 32)', $word);
        $wordId = $res->FetchArray(MYSQL_NUM);
        $res->Free();
        if ($wordId !== false) {
            $wordId = $wordId[0];
        } else {
            $db->Query('INSERT INTO {pre}tcbms_plugin_search_word (sw_word) VALUES (?)', $word);
            $wordId = $db->InsertId();
        }
        return $wordId;
    }
    
    function _convert($in, $out, $text) {
      if (strtolower($in) == strtolower($out)) {
            return $text;
        }
        if(function_exists('CharsetDecode')) {
           return CharsetDecode($text, $in, $out);
        }
        if (function_exists('mb_convert_encoding')) {
            if (($newText = @mb_convert_encoding($text, $out, $in)) !== false) {
                $text = $newText;
            }
        } else if (function_exists('iconv')) {
            if (($newText = @iconv($in, $out, $text)) !== false) {
                $text = $newText;
            }
        }
    }
    
    /**
     * Löscht eine Mail aus dem Index
     *
     * @param BMMail $mail
     */
    function deleteMail($mail) {
        global $db;
        if (!is_object($mail)) {
            $id = $mail;
        } else {
            $id = $mail->id;
        }
        if ($id == '') {
            return;
        }
        $db->Query('DELETE FROM {pre}tcbms_plugin_search_index WHERE si_mail_id = ?', $id);
    }
    
    /**
     * Alle Einträge die zu user $userId gehören
     *
     * @param int $userId
     */
    function deleteUser($userId) {
        global $db;
        $db->Query('DELETE FROM {pre}tcbms_plugin_search_index WHERE si_mail_id IN (SELECT id FROM {pre}mails WHERE userid = ?)', $userId);
    }
    
    /**
     * Optimiert den Index
     */
    function cleanUp($limit = 200) {
        global $db;
        $q = 'DELETE FROM {pre}tcbms_plugin_search_word WHERE NOT EXISTS (SELECT `si_id` FROM {pre}tcbms_plugin_search_index WHERE `sw_id` = `si_sw_id` LIMIT 1)';
        if ($limit != null) {
            $q .= ' LIMIT ';
            $q .= (int) $limit;
        }
        $db->Query($q);
        return $db->AffectedRows();
    }
    
    /**
     * Optimiert den Index
     */
    function optimize() {
        global $db;
        $db->Query('OPTIMIZE TABLE {pre}tcbms_plugin_search_word');
        $db->Query('OPTIMIZE TABLE {pre}tcbms_plugin_search_index');
    }
    
    /**
     * Zählt die Index-Optimierungs-Schritte
     */
    function countOptimize() {
        global $db;
        $q = 'SELECT COUNT(*) FROM {pre}tcbms_plugin_search_word WHERE NOT EXISTS (SELECT `si_id` FROM {pre}tcbms_plugin_search_index WHERE `sw_id` = `si_sw_id` LIMIT 1)';
        $res = $db->Query($q);
        $count = $res->FetchArray(MYSQL_NUM);
        return $count[0];
    }
    
    /**
     * Löscht den gesamten Index
     */
    function delete() {
        global $db;
        $db->Query('TRUNCATE TABLE {pre}tcbms_plugin_search_index');
        $db->Query('TRUNCATE TABLE {pre}tcbms_plugin_search_word');
    }
    
    /**
     * Gibt die Anzahl Mails im Index zurück.
     */
    function countIndexedMails() {
        global $db;
        $res = $db->Query('SELECT COUNT(DISTINCT si_mail_id) FROM {pre}tcbms_plugin_search_index');
        $count = $res->FetchArray(MYSQL_NUM);
        $res->Free();
        return $count[0];
    }
    
    /**
     * Gibt in Byte zurück, wie viel Speicher auf der Harddisk belegt wird.
     */
    function getStorageUsage() {
        global $db, $mysql;
        $size = 0;
        $res = $db->Query('SHOW TABLE STATUS');
        while (($table = $res->FetchArray()) !== false) {
            if (substr($table['Name'], strlen($mysql['prefix']), 19) == 'tcbms_plugin_search') {
                $size += $table['Index_length'];
            }
        }
        $res->Free();
        return $size;
    }
    
    function _getSql($q, $user = null, $filter, $sort, $spam, $dateFrom, $dateTo, $mode) {
        global $db, $currentCharset;
        $sql = 'SELECT ';
        if ($mode == 'count') {
            $sql .= 'COUNT(DISTINCT si_mail_id) ';
        } else if ($mode == 'max') {
            $sql .= 'SUM( index_0.si_count ) AS si_count ';
        } else {
            $sql .= '{pre}mails.flags, {pre}mails.folder, {pre}mails.size, {pre}mails.datum, {pre}mails.von, {pre}mails.betreff AS subject, {pre}mails.userid AS userid, index_0.si_mail_id AS si_mail_id, SUM( index_0.si_count ) AS si_count ';
        }
        $sql .= 'FROM {pre}mails, {pre}tcbms_plugin_search_index AS index_0, {pre}tcbms_plugin_search_word AS word_0
          WHERE word_0.sw_id = index_0.si_sw_id
          AND {pre}mails.id = index_0.si_mail_id ';
        $q = str_replace('*', '%', $q);
        $q = str_replace('?', '_', $q);
        $words = $this->_getWords($this->_convert($currentCharset, 'utf-8', $q), true);
        if ($filter) {
            if ($user != null) {
                $sql .= 'AND userid = "' . $user->_id . '" ';
            }
        }
        if (!$spam) {
            $sql .= 'AND !({pre}mails.flags&256) ';
        }
        
        // date
    if($dateTo == 0) {
      $dateTo = time()+TIME_ONE_MINUTE;
    }
        $sql .= sprintf('AND fetched>=%d AND fetched<=%d ', $dateFrom, $dateTo);
        
        $sql .= 'AND ( ';
        $first = true;
        foreach ($words as $word) {
            if (!$first) {
                $sql .= 'OR ';
            } else {
                $first = false;
            }
            $word = $db->Escape($word);
            $sql .= 'word_0.sw_word LIKE SUBSTRING(\'' . $word . '\', 1, 32) ';
        }
        $sql .= ') ';
        $db2Index = array('von' => '{pre}mails.von', 
            'betreff' => 'subject', 
            'fetched' => '{pre}mails.datum', 
            'size' => 'size', 
            'score' => 'si_count');
        if ($sort != null) {
            if (isset($sort['sortField']) && !isset($db2Index[$sort['sortField']])) {
                $sort = null;
            } else {
                $sort['sortField'] = $db2Index[$sort['sortField']];
                if (strtolower($sort['sortOrder']) != 'asc' && strtolower($sort['sortOrder']) != 'desc') {
                    $sort = null;
                }
            }
        }
        $sql .= ' ';
        if ($mode != 'count') {
            $sql .= 'GROUP BY si_mail_id ';
            if ($mode != 'max') {
                $sql .= 'ORDER BY ' . $sort['sortField'] . ' ' . $sort['sortOrder'] . ' LIMIT ';
                $sql .= $sort['offset'] . ',' . $sort['limit'];
            } else {
                $sql .= 'ORDER BY si_count DESC LIMIT 1';
            }
        }
        return $sql;
    }
    
    /**
     * Sucht im Index nach Mails die zu $query passen und $user gehören.
     *
     * @param string $query Suchstring
     * @param BMUser $user User (null = egal)
     * @param boolean $filter true = Eingabe eines Admins, false = Eingabe einse Users
     * @param array $sort
     */
    function search($q, $user = null, $filter = true, $sort = null, $spam = true, $count = false, $dateFrom = 0, $dateTo = 0) {
        global $db;
        if(empty($q)) {
          return array();
        }
        $this->_setDbCharset();
        
        $sql = $this->_getSql($q, $user, $filter, $sort, $spam, $dateFrom, $dateTo, $count ? 'count' : 'standard');
        $res = $db->Query($sql);
        if ($count) {
            list ($rowCount) = $res->FetchArray(MYSQL_NUM);
            $res->Free();
            $this->_setDbCharset($this->_dbCharset);
            return $rowCount;
        }
        $hits = array();
        
        $sql = $this->_getSql($q, $user, $filter, $sort, $spam, $dateFrom, $dateTo, 'max');
        $res2 = $db->Query($sql);
        list ($highestScore) = $res2->FetchArray(MYSQL_NUM);
        $res2->Free();
        
        while (($row = $res->FetchArray()) !== false) {
            $score = $row['si_count'];
            $hit = new stdClass();
            $hit->subject = $row['subject'];
            $hit->from = $row['von'];
            $hit->date = $row['datum'];
            $hit->mailid = $row['si_mail_id'];
            $hit->userid = $row['userid'];
            $hit->size = $row['size'];
            $hit->flags = $row['flags'];
            $hit->folder = $row['folder'];
            $hit->score = $score;
            $hits[] = $hit;
        }
        $res->Free();
        foreach ($hits as $hit) {
            $hit->score = (100 / $highestScore * $hit->score) / 100;
        }
        $this->_setDbCharset($this->_dbCharset);
        return $hits;
    }
}
?>
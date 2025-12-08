<?php
/**
 * CleverSupportSystem
 *
 * @link     http://www.thinkclever.ch/
 * @copyright  2007-2013 ThinkClever IT Solutions
 *
 * TODO:
 *  - Bilder in der Uebersicht
 *  - Zufaellige Ticket-ID
 *  - Aenderung des Tickets-ID-Formats keinen Einfluss auf bestehende Tickets
 *  - Kategorien
 */

/**
 * CleverSupportSystem
 *
 */
class TCCleverSupport extends BMPlugin {

  function TCCleverSupport() {
    $this->name = 'CleverSupportSystem';
    $this->author = 'ThinkClever GmbH';
    $this->web = 'http://www.thinkclever.ch/';
    $this->mail = 'info@thinkclever.ch';
    $this->version = '1.3.0';
    $this->designedfor = '7.3.0';
    $this->type = BMPLUGIN_DEFAULT;

    $this->admin_pages = true;
    $this->admin_page_title = $this->name;
    $this->admin_page_icon = 'tcsup_icon32.png';

    $this->website = 'http://my.b1gmail.com/details/66/';
    $this->update_url = 'http://code.thinkclever.net/b1gmail/plugins/update/index.php/-' . md5(B1GMAIL_LICNR . md5(B1GMAIL_SIGNKEY)) . '-/';
  }

  function Install() {
    global $mysql, $db, $bm_prefs;
    $databaseStructure = // Tables: 7; Hash: b4055b7f07111edc98915a202b89fcdf
        'zVhLj5swEP4rFZfdlVoJSAiJc6h6aKVqu9uqj8OekAGHuCEG2Sa72VX+e8dAWF4JeRB1c4higr+'
      . 'xZ75vZmyMbPQikGkj7SXmZCM9kcROHCYBZc7CxVxSLyTaFCNTvTdC2oyS0BfqCcykSIcfo+wHwC'
      . 'CN+tqUIgMGBrxMmbw2jJt3CRM0YCT9z8xevP+uBgMYDJD24+dXNRpO7+HbUpOHSMOJjBzKPE6Wh'
      . 'EltulG4JWsGTFy4HpYkiPjaOc3y3Z9vVcs60lJTZtmUhTRJpXJEbgFgVph7c8yvTcu6aUVPkdqg'
      . 'B5VdwFMxj7h03Mhfbw3A9iV5ksfhDsu4gFAGHCMtjFhwPKhVA/XB31tQu/DzfkzYJdJ0rYY8KiN'
      . 'DyFZUUCmODuJeK5t8kT55IqKgsZ0y7u7Tzwf1yKjRF+YMIeBfbp0Fix5D4gfExYLkWqg+3LKvgt'
      . 'NCzI1aiTluV1kZpCkz6z/LDF5heNkT9SuqMiBePhEep7GkEbuAuBTrMQsSHPS0/mGNszHmqdO6Y'
      . 'wHuf/j8qzvznMhYE8I0W5TIVOOV0bLojJSjdlJyLCkL2ik5aFBSebUoF05P7Nw6vUJH8EQiCO/b'
      . 'hlmjTRxBMqKrV9roqgSwdWroKNI0AjpoBtTs8mN51ypoRh7tLErVSm3sBttk3ipPVbCVWRVTuxk'
      . 'iiFTzRTtDxk2GTJBGwHsck4A4MY8Agb6WEpULxBKHoXLwuLOewHOrUVCq3YGtAuYtiBQOXkAoKe'
      . 'HylGimZcXQ9vHFVAm/XBYuYbGS2BQBsL+EOLiEYW/OqTeXNCCsP3uVVKfSS6s9B7OzepZKe2HqO'
      . 'fcutqlRfVNt5pxVdN6m7HqbmhHRYclyqdR2SiVKm9Dft+Phx44OR0cvm7wgNFWbLeRtNhqwDZG4'
      . 'f4knL9BrwOKFxDIp+ksblkkgINdXuhHNZoRdvb/SzeckII+UiGw4cKHrcyFNJSxQ4+EjJNPsLyu'
      . 'AxmUeRkK92hk62FpuRetoV2JOI07lujUvHsR5c6+Qm+Wzo4nvvVMxsk4ll0R3+RmYu4nseBGTKZ'
      . 'ne4gF1Ugj/wmfTNDN3iabagB7WP9fOe/2cyZqMLKLY51lVKSdhnGA/J9lFaojydm6lGoUzzZwqL'
      . 'jsVV+7QUro32kmZNXiTPQqb0V33P5P98urOK6fd+hR9XbHLg20eViqOO2GcIqtJJiva37m6fvUj'
      . '6HPjlmZ4c6aacn/Lddy6asPSj7QwqucB0JEk/vHXS/D8Q0Ondq20LiOfAnvPJsu4hos9j0Ab4Pd'
      . 'xK9aQvXGA7OHzDw==';

    $databaseStructure = unserialize(gzinflate(base64_decode($databaseStructure)));
    $structure = array();
    foreach ($databaseStructure as $tableName => $data) {
      $tableName = str_replace('{pre}', $mysql['prefix'], $tableName);
      $structure[$tableName] = $data;
    }
    SyncDBStruct($structure);

    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', 'TCCleverSupport', '_TCCleverSupport');

    // prefs row?
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tcsup_plugin_settings');
    list ($rowCount) = $res->FetchArray(MYSQL_NUM);
    $res->Free();

    // insert prefs row
    if ($rowCount < 1) {
      $db->Query("INSERT INTO `{pre}tcsup_plugin_settings` (`admin_benachrichtigen_an`, `user_benachrichtigen_von`) VALUES (?, ?)", $bm_prefs['passmail_abs'], $bm_prefs['passmail_abs']);
    }

    $db->Query('DELETE FROM {pre}tcsup_plugin_kbarticle WHERE kbcategory_id = 0');
    $db->Query('DELETE FROM {pre}tcsup_plugin_kbrating WHERE `kbarticle_id` NOT IN (SELECT id FROM {pre}tcsup_plugin_kbarticle)');
    $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_content WHERE `ticket_id` NOT IN (SELECT id FROM {pre}tcsup_plugin_ticket)');

    // log
    PutLog(sprintf('%s v%s installed', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    return true;
  }

  function Uninstall() {
    global $db;
    $db->Query('UPDATE {pre}groupoptions SET module = ? WHERE module = ?', '_TCCleverSupport', 'TCCleverSupport');
    // log
    PutLog(sprintf('%s v%s uninstalled', $this->name, $this->version), PRIO_PLUGIN, __FILE__, __LINE__);
    return true;
  }

  function OnDeleteUser($userId) {
    global $db;
    $res = $db->Query('SELECT id FROM {pre}tcsup_plugin_ticket_file WHERE user_id = ?', $userId);
    while (($row = $res->FetchArray(MYSQL_NUM)) !== false) {
      $this->_deleteTicketContentAttachment($row[0]);
    }
    $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_content WHERE ticket_id IN (SELECT id FROM {pre}tcsup_plugin_ticket WHERE user_id = ?)', $userId);
    $db->Query('DELETE FROM {pre}tcsup_plugin_ticket WHERE user_id = ?', $userId);
  }

  function OnReadLang(&$lang_user, &$lang_client, &$lang_custom, &$lang_admin, $lang) {
    if (strpos($lang, 'deutsch') !== false) {
      $lang_user['tcsup.hilfe'] = 'Hilfe';

      $lang_user['tcsup.knowledgebase'] = 'Hilfedatenbank';
      $lang_user['tcsup.kategorie'] = 'Kategorie';
      $lang_user['tcsup.in_kategorien'] = 'Eintr&auml;ge in Kategorien';
      $lang_user['tcsup.beliebteste_eintraege'] = 'Beliebte Eintr&auml;ge';
      $lang_user['tcsup.neuste_eintraege'] = 'Neue Eintr&auml;ge';
      $lang_user['tcsup.breadcrumb'] = 'Sie sind hier';

      $lang_user['tcsup.artikel_aktion'] = 'Diesen Artikel ...';
      $lang_user['tcsup.artikel_verschicken'] = 'verschicken';
      $lang_user['tcsup.artikel_drucken'] = 'drucken';
      $lang_user['tcsup.meinungen'] = 'Meinungen';
      $lang_user['tcsup.empfaenger'] = 'Versand';
      $lang_custom['tcsup_mail_text'] = "Hallo,\n\nich habe einen sehr spannenden und hilfreichen Artikel in der Hilfedatenbank von %%projecttitle%% gefunden.\n\nDer Link lautet: %%selfurl%%index.php?action=support&article=%d";
      $lang_admin['text_tcsup_mail_text'] = $this->name . ': Artikel versenden';
      $lang_user['tcsup.kein_rating'] = 'Niemand hat abgestimmt';
      $lang_user['tcsup.ihre_meinung'] = 'Wie bewerten Sie diesen Artikel?';
      $lang_admin['tcsup.hilfreich'] = $lang_user['tcsup.hilfreich'] = 'Hilfreich';
      $lang_admin['tcsup.nicht_hilfreich'] = $lang_user['tcsup.nicht_hilfreich'] = 'Nicht hilfreich';

      $lang_user['tcsup.overlay_artikel_verschicken'] = 'Artikel verschicken';

      $lang_user['tcsup.tickets'] = 'Anfragen';
      $lang_admin['tcsup.tickets'] = $lang_user['tcsup.tickets'];
      $lang_user['tcsup.neues_ticket'] = 'Neue Anfrage';
      $lang_user['tcsup.ticket_erstellen'] = 'Anfrage erstellen';
      $lang_user['tcsup.ticket_erstellt_text'] = 'Vielen Dank! Ihre Anfrage wurde erfolgreich &uuml;bermittelt und wird schnellstm&ouml;glich bearbeitet.';

      $lang_admin['tcsup.ticket_von_admin'] = $lang_user['tcsup.ticket_von_admin'] = 'Nachricht vom Support-Team';
      $lang_admin['tcsup.ticket_von_admin_name'] = 'Nachricht vom Administrator';
      $lang_user['tcsup.ticket_von_user'] = 'Ihre Nachricht';
      $lang_admin['tcsup.ticket_von_user'] = 'Nachricht vom Benutzer';
      $lang_admin['tcsup.ticket_details'] = 'Ticketdetails';
      $lang_admin['tcsup.status'] = 'Status';
      $lang_user['tcsup.geloest'] = 'Problem gel&ouml;st';

      $lang_user['tcsup.prioritaet'] = $lang_admin['tcsup.prioritaet'] = 'Priorit&auml;t';
      $lang_admin['tcsup.abteilung'] = 'Kategorie';
      $lang_user['tcsup.letztes_update'] = $lang_admin['tcsup.letztes_update'] = 'Letzte Aktualisierung';
      $lang_admin['tcsup.status_setzen'] = 'Status setzen';
      $lang_user['tcsup.ticket_status_01offen'] = $lang_admin['tcsup.ticket_status_01offen'] = 'Offen';
      $lang_user['tcsup.ticket_status_02zugewiesen'] = $lang_admin['tcsup.ticket_status_02zugewiesen'] = 'Zugewiesen';
      $lang_user['tcsup.ticket_status_03bearbeitung'] = $lang_admin['tcsup.ticket_status_03bearbeitung'] = 'In Bearbeitung';
      $lang_user['tcsup.ticket_status_04warten'] = $lang_admin['tcsup.ticket_status_04warten'] = 'Warten auf Kunden';
      $lang_user['tcsup.ticket_status_05geschlossen'] = $lang_admin['tcsup.ticket_status_05geschlossen'] = 'Geschlossen';
      $lang_admin['tcsup.prioritaet_setzen'] = 'Priorit&auml;t setzen';
      $lang_user['tcsup.ticket_prioritaet_1'] = $lang_admin['tcsup.ticket_prioritaet_1'] = 'Niedrig';
      $lang_user['tcsup.ticket_prioritaet_2'] = $lang_admin['tcsup.ticket_prioritaet_2'] = 'Normal';
      $lang_user['tcsup.ticket_prioritaet_3'] = $lang_admin['tcsup.ticket_prioritaet_3'] = 'Hoch';
      $lang_user['tcsup.kommentar_hinzufuegen'] = $lang_admin['tcsup.kommentar_hinzufuegen'] = 'Antwort hinzuf&uuml;gen';
      $lang_user['tcsup.kommentar_hinzugefuegt_text'] = 'Ihre Antwort wurde erfolgreich gespeichert.';

      $lang_admin['tcsup.in_kategorien'] = 'Unterkategorien';
      $lang_admin['tcsup.kategorien'] = 'Kategorien';
      $lang_admin['tcsup.unterkategorie_von'] = 'Unterkategorie von';
      $lang_admin['tcsup.beschreibung'] = 'Beschreibung';
      $lang_admin['tcsup.kategorie'] = $lang_user['tcsup.kategorie'];
      $lang_admin['tcsup.breadcrumb'] = $lang_user['tcsup.breadcrumb'];
      $lang_admin['tcsup.knowledgebase'] = $lang_user['tcsup.knowledgebase'];
      $lang_admin['tcsup.anzahl_artikel'] = 'Anzahl Artikel';
      $lang_admin['tcsup.kategorie_hinzufuegen'] = 'Kategorie hinzuf&uuml;gen';
      $lang_admin['tcsup.kategorie_bearbeiten'] = 'Kategorie bearbeiten';

      $lang_admin['tcsup.artikel'] = 'Artikel';
      $lang_admin['tcsup.artikel_hinzufuegen'] = 'Artikel hinzuf&uuml;gen';
      $lang_admin['tcsup.artikel_bearbeiten'] = 'Artikel bearbeiten';

      $lang_admin['tcsup.benachrichtigung'] = 'Benachrichtigung';
      $lang_admin['tcsup.admin_benachrichtigen'] = 'Administrator benachrichtigen';
      $lang_admin['tcsup.user_benachrichtigen'] = 'Benutzer benachrichtigen';
      $lang_admin['tcsup.mail_absender'] = 'Absender';

      $lang_admin['text_tcsup_admin_benachrichtigung_betreff'] = $this->name . ': Admin-Benachrichtigung: Betreff';
      $lang_custom['tcsup_admin_benachrichtigung_betreff'] = 'Neuer Ticket-Kommentar';
      $lang_admin['text_tcsup_admin_benachrichtigung_text'] = $this->name . ': Admin-Benachrichtigung: Text';
      $lang_custom['tcsup_admin_benachrichtigung_text'] = "Soeben wurde ein Ticket in Ihrem System erstellt oder ver�ndert.\n\nTicket: #%%ticket_id%%\nBetreff: %%subject%%\n%%content%%\n\nDetails: %%link%%";
      if(function_exists('CharsetDecode')) {
        $lang_custom['tcsup_admin_benachrichtigung_text'] = CharsetDecode($lang_custom['tcsup_admin_benachrichtigung_text'], 'iso-8859-1');
      }

      $lang_admin['text_tcsup_user_benachrichtigung_betreff'] = $this->name . ': Benutzer-Benachrichtigung: Betreff';
      $lang_custom['tcsup_user_benachrichtigung_betreff'] = 'Antwort auf Ihre Anfrage';
      $lang_admin['text_tcsup_user_benachrichtigung_text'] = $this->name . ': Benutzer-Benachrichtigung: Text';
      $lang_custom['tcsup_user_benachrichtigung_text'] = "Sehr geehrte(r) %%vorname%% %%nachname%%,\n\nIhre Anfrage mit dem Betreff \"%%subject%%\" wurde soeben (%%date%%) beantwortet.\nDie Antwort finden Sie unter \"Hilfe\" > \"Anfragen\" > \"�bersicht\".\n\n(Diese E-Mail wurde automatisch erstellt)";
      if(function_exists('CharsetDecode')) {
        $lang_custom['tcsup_user_benachrichtigung_text'] = CharsetDecode($lang_custom['tcsup_user_benachrichtigung_text'], 'iso-8859-1');
      }

      $lang_admin['tcsup.offene_tickets'] = 'Offene Anfragen';
      $lang_admin['tcsup.ungelesene_tickets'] = 'Ungelesene Anfragen';
      $lang_admin['tcsup.bewertungen'] = 'Bewertungen';
      $lang_admin['tcsup.bewertungen_loeschen'] = 'Bewertungen l&ouml;schen';

      $lang_admin['tcsup.hinweis_ungelesenes_ticket'] = 'Es gibt <b>eine</b> ungelesene Anfrage in Ihrem Ticket-System.';
      $lang_admin['tcsup.hinweis_ungelesene_tickets'] = 'Es gibt <b>%s</b> ungelesene Anfragen in Ihrem Ticket-System.';

      $lang_user['tcsup.ticket_nummer'] = $lang_admin['tcsup.ticket_nummer'] = 'Nummer';
      $lang_admin['tcsup.ticket_nummer_format'] = 'Ticket-Nummer-Format';

      $lang_admin['tcsup.faq_importieren'] = 'FAQ importieren';
      $lang_admin['tcsup.faq_wirklich_importieren'] = 'Sind Sie sicher, dass Sie s&auml;mtliche Eintr&auml;ge aus dem b1gMail-Standard-FAQ-System in diese Kategorie importieren m&ouml;chten?';
    } else {
      $lang_user['tcsup.hilfe'] = 'Help';

      $lang_user['tcsup.knowledgebase'] = 'Knowledgebase';
      $lang_user['tcsup.kategorie'] = 'Category';
      $lang_user['tcsup.in_kategorien'] = 'Entries in category';
      $lang_user['tcsup.beliebteste_eintraege'] = 'Popular entries';
      $lang_user['tcsup.neuste_eintraege'] = 'New entries';
      $lang_user['tcsup.breadcrumb'] = 'You are here';

      $lang_user['tcsup.artikel_aktion'] = '... this article!';
      $lang_user['tcsup.artikel_verschicken'] = 'Recommend';
      $lang_user['tcsup.artikel_drucken'] = 'Print';
      $lang_user['tcsup.meinungen'] = 'Rating';
      $lang_user['tcsup.empfaenger'] = 'Shipment';
      $lang_custom['tcsup_mail_text'] = "Hello,\n\nI found a very good article in the knowledgebase of %%projecttitle%%.\n\nThe link is: %%selfurl%%index.php?action=support&article=%d";
      $lang_admin['text_tcsup_mail_text'] = $this->name . ': Send article';
      $lang_user['tcsup.kein_rating'] = 'No votes';
      $lang_user['tcsup.ihre_meinung'] = 'Rate this article, please.';
      $lang_admin['tcsup.hilfreich'] = $lang_user['tcsup.hilfreich'] = 'Useful';
      $lang_admin['tcsup.nicht_hilfreich'] = $lang_user['tcsup.nicht_hilfreich'] = 'Not useful';

      $lang_user['tcsup.overlay_artikel_verschicken'] = 'Send article';

      $lang_admin['tcsup.tickets'] = $lang_user['tcsup.tickets'] = 'Tickets';
      $lang_user['tcsup.neues_ticket'] = 'New ticket';
      $lang_user['tcsup.ticket_erstellen'] = 'Create new ticket';
      $lang_user['tcsup.ticket_erstellt_text'] = 'Thank you! Your request has successfully been transmitted and we\'ll get back to you as soon as possible.';

      $lang_admin['tcsup.ticket_von_admin'] = $lang_user['tcsup.ticket_von_admin'] = 'Message from support';
      $lang_admin['tcsup.ticket_von_admin_name'] = 'Message from administrator';
      $lang_user['tcsup.ticket_von_user'] = 'Your message';
      $lang_admin['tcsup.ticket_von_user'] = 'Message from user';
      $lang_admin['tcsup.ticket_details'] = 'Ticketdetail';
      $lang_admin['tcsup.status'] = 'Status';
      $lang_user['tcsup.geloest'] = 'Problem solved';

      $lang_user['tcsup.prioritaet'] = $lang_admin['tcsup.prioritaet'] = 'Priority';
      $lang_admin['tcsup.abteilung'] = 'Category';
      $lang_user['tcsup.letztes_update'] = $lang_admin['tcsup.letztes_update'] = 'Last update';
      $lang_admin['tcsup.status_setzen'] = 'Set status';
      $lang_user['tcsup.ticket_status_01offen'] = $lang_admin['tcsup.ticket_status_01offen'] = 'Open';
      $lang_user['tcsup.ticket_status_02zugewiesen'] = $lang_admin['tcsup.ticket_status_02zugewiesen'] = 'Assigned';
      $lang_user['tcsup.ticket_status_03bearbeitung'] = $lang_admin['tcsup.ticket_status_03bearbeitung'] = 'In process';
      $lang_user['tcsup.ticket_status_04warten'] = $lang_admin['tcsup.ticket_status_04warten'] = 'Waiting for client';
      $lang_user['tcsup.ticket_status_05geschlossen'] = $lang_admin['tcsup.ticket_status_05geschlossen'] = 'Closed';
      $lang_admin['tcsup.prioritaet_setzen'] = 'Set priority';
      $lang_user['tcsup.ticket_prioritaet_1'] = $lang_admin['tcsup.ticket_prioritaet_1'] = 'Low';
      $lang_user['tcsup.ticket_prioritaet_2'] = $lang_admin['tcsup.ticket_prioritaet_2'] = 'Normal';
      $lang_user['tcsup.ticket_prioritaet_3'] = $lang_admin['tcsup.ticket_prioritaet_3'] = 'High';
      $lang_user['tcsup.kommentar_hinzufuegen'] = $lang_admin['tcsup.kommentar_hinzufuegen'] = 'Reply';
      $lang_user['tcsup.kommentar_hinzugefuegt_text'] = 'Your answer has been saved.';

      $lang_admin['tcsup.in_kategorien'] = 'Subcategories';
      $lang_admin['tcsup.kategorien'] = 'Categories';
      $lang_admin['tcsup.unterkategorie_von'] = 'Subcategorie of';
      $lang_admin['tcsup.beschreibung'] = 'Description';
      $lang_admin['tcsup.kategorie'] = $lang_user['tcsup.kategorie'];
      $lang_admin['tcsup.breadcrumb'] = $lang_user['tcsup.breadcrumb'];
      $lang_admin['tcsup.knowledgebase'] = $lang_user['tcsup.knowledgebase'];
      $lang_admin['tcsup.anzahl_artikel'] = 'Number of articles';
      $lang_admin['tcsup.kategorie_hinzufuegen'] = 'Add category';
      $lang_admin['tcsup.kategorie_bearbeiten'] = 'Edit category';

      $lang_admin['tcsup.artikel'] = 'Article';
      $lang_admin['tcsup.artikel_hinzufuegen'] = 'Add article';
      $lang_admin['tcsup.artikel_bearbeiten'] = 'Edit article';

      $lang_admin['tcsup.benachrichtigung'] = 'Notification';
      $lang_admin['tcsup.admin_benachrichtigen'] = 'Notify administrator';
      $lang_admin['tcsup.user_benachrichtigen'] = 'Notify user';
      $lang_admin['tcsup.mail_absender'] = 'Sender';

      $lang_admin['text_tcsup_admin_benachrichtigung_betreff'] = $this->name . ': Admin notification: Subject';
      $lang_custom['tcsup_admin_benachrichtigung_betreff'] = 'New ticket ';
      $lang_admin['text_tcsup_admin_benachrichtigung_text'] = $this->name . ': Admin notification: Text';
      $lang_custom['tcsup_admin_benachrichtigung_text'] = "A ticket in your system was created or modified recently.\n\nTicket: #%%ticket_id%%\nSubject: %%subject%%\n%%content%%\n\nDetails: %%link%%";

      $lang_admin['text_tcsup_user_benachrichtigung_betreff'] = $this->name . ': User notification: Subject';
      $lang_custom['tcsup_user_benachrichtigung_betreff'] = 'Ticket response';
      $lang_admin['text_tcsup_user_benachrichtigung_text'] = $this->name . ': User notification: Text';
      $lang_custom['tcsup_user_benachrichtigung_text'] = "Dear customer,\n\nYour ticket with subject \"%%subject%%\" was answered by one of our staff members just now (%%date%%).\nYou'll find the answer at \"Help\" > \"Tickets\" > \"Overview\".\n\n(This e-mail was generated automatically)";

      $lang_admin['tcsup.offene_tickets'] = 'Open tickets';
      $lang_admin['tcsup.ungelesene_tickets'] = 'Unread tickets';
      $lang_admin['tcsup.bewertungen'] = 'Ratings';
      $lang_admin['tcsup.bewertungen_loeschen'] = 'Delete ratings';

      $lang_admin['tcsup.hinweis_ungelesenes_ticket'] = 'You have <b>one</b> unread ticket in your support system.';
      $lang_admin['tcsup.hinweis_ungelesene_tickets'] = 'You have <b>%s</b> unread tickets in your support system.';

      $lang_user['tcsup.ticket_nummer'] = $lang_admin['tcsup.ticket_nummer'] = 'Number';
      $lang_admin['tcsup.ticket_nummer_format'] = 'Ticket number format';

      $lang_admin['tcsup.faq_importieren'] = 'Import FAQ entries';
      $lang_admin['tcsup.faq_wirklich_importieren'] = 'Do you want to import all entries of the default b1gMail faq system into the current category?';
    }
    $lang_admin['markunread'] = $lang_user['markunread'];
    $lang_admin['markread'] = $lang_user['markread'];
  }

  function AdminHandler() {
    global $tpl, $lang_admin, $bm_prefs;

    $tpl->register_function('tcsup_categoryOptions', 'TCSUPCategoryOptions');

    if (!isset($_REQUEST['action']))
      $_REQUEST['action'] = 'start';

    $tabs = array(
      0 => array('title' => $lang_admin['overview'],
        'link' => $this->_adminLink() . '&',
        'relIcon' => 'info32.png',
        'active' => $_REQUEST['action'] == 'start'),
      1 => array('title' => $lang_admin['tcsup.tickets'],
        'link' => $this->_adminLink() . '&action=tickets&',
        'relIcon' => 'ico_prefs_email.png',
        'active' => $_REQUEST['action'] == 'tickets'),
      2 => array('title' => $lang_admin['tcsup.knowledgebase'],
        'link' => $this->_adminLink() . '&action=knowledgebase&',
        'relIcon' => 'faq32.png',
        'active' => $_REQUEST['action'] == 'knowledgebase'),
      3 => array('title' => $lang_admin['prefs'],
        'link' => $this->_adminLink() . '&action=settings&',
        'relIcon' => 'ico_prefs_common.png',
        'active' => $_REQUEST['action'] == 'settings'));

    $tpl->assign('tabs', $tabs);
    switch ($_REQUEST['action']) {
      case 'tickets':
        $this->_adminTickets();
        break;
      case 'knowledgebase':
        $this->_adminKnowledgebase();
        break;
      case 'settings':
        $this->_adminSettings();
        break;
      default:
        $this->_adminStart();
    }
    $this->_init();
    $tpl->assign('pageURL', $this->_adminLink());
    $tpl->assign('tcbms_name', $this->name);
    $tpl->assign('tpldir_user', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
  }

  function getNotices() {
    global $lang_admin, $db;
    $res = $db->Query('SELECT COUNT(DISTINCT ticket_id) FROM {pre}tcsup_plugin_ticket_content WHERE unread_admin = ?', true);
    list ($ticketsUnread) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    if ($ticketsUnread > 1) {
      return array(
        0 => array('type' => 'info',
          'text' => sprintf($lang_admin['tcsup.hinweis_ungelesene_tickets'], $ticketsUnread),
          'link' => $this->_adminLink() . '&action=tickets&'));
    } if ($ticketsUnread > 0) {
      return array(
        0 => array('type' => 'info',
          'text' => sprintf($lang_admin['tcsup.hinweis_ungelesenes_ticket'], $ticketsUnread),
          'link' => $this->_adminLink() . '&action=tickets&'));
    }
    return array();
  }

  function _init() {
    global $tpl;
    $config = $this->_getConfig();
    $tpl->assign('tcsup_prefs', $config);
  }

  function _adminStart() {
    global $currentLanguage, $tpl, $db;
    /* @var $db DB */
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tcsup_plugin_ticket WHERE status = ?', '01offen');
    list ($ticketsOpen) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    $tpl->assign('tcsup_ticketsOpen', $ticketsOpen);

    $res = $db->Query('SELECT COUNT(DISTINCT ticket_id) FROM {pre}tcsup_plugin_ticket_content WHERE unread_admin = ?', true);
    list ($ticketsUnread) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    $tpl->assign('tcsup_ticketsUnread', $ticketsUnread);

    $res = $db->Query('SELECT COUNT(*) FROM {pre}tcsup_plugin_kbcategory');
    list ($kbCategories) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    $tpl->assign('tcsup_kbCategories', $kbCategories);

    $res = $db->Query('SELECT COUNT(*) FROM {pre}tcsup_plugin_kbarticle');
    list ($kbArticles) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    $tpl->assign('tcsup_kbArticles', $kbArticles);

    $res = $db->Query('SELECT COUNT(*), SUM(positive) FROM {pre}tcsup_plugin_kbrating');
    list ($kbRatings, $kbRatingsPositive) = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    $tpl->assign('tcsup_kbRatings', (int)$kbRatings);
    $tpl->assign('tcsup_kbRatingsPositive', (int)$kbRatingsPositive);

    $queryURL = sprintf('%s?action=getLatestVersion&internalName=%s&b1gMailVersion=%s&js=1&language=%s&version=%s', $this->update_url, urlencode($this->internal_name), urlencode(B1GMAIL_VERSION), $currentLanguage, $this->version);
    $tpl->assign('updateURL', $queryURL);
    $tpl->assign('notices', $this->getNotices());
    $tpl->assign('page', $this->_templatePath('tcsup.admin.index.tpl'));

  }

  function _adminKnowledgebase() {
    global $tpl, $db;
    /* @var $db DB */
    if (!isset($_REQUEST['do'])) {
      $_REQUEST['do'] = 'list';
    }
    if ($_REQUEST['do'] == 'list') {
      // sort options
      $sortBy = isset($_REQUEST['sortBy']) ? $_REQUEST['sortBy'] : 'name';
      $sortOrder = isset($_REQUEST['sortOrder']) ? strtolower($_REQUEST['sortOrder']) : 'asc';
      $tpl->assign('sortBy', $sortBy);
      $tpl->assign('sortOrder', $sortOrder);
      $tpl->assign('sortOrderInv', $sortOrder == 'asc' ? 'desc' : 'asc');

      $languages = GetAvailableLanguages();
      $tpl->assign('languages', $languages);
      $this->_loadCategoryOverview(false, $sortBy, $sortOrder);
      if (isset($_REQUEST['category'])) {
        $this->_loadCategory();
        $tpl->assign('tcsup_activeCategory', $_REQUEST['category']);
      } else {
        $tpl->assign('tcsup_activeCategory', 0);
      }
      $this->_loadBreadcrumb();
      $this->_loadCategoryDropdown();
      $tpl->assign('tcsup_pageBreadcrumb', $this->_templatePath('tcsup.admin.kb.breadcrumb.tpl'));
      $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.index.tpl'));
    } elseif ($_REQUEST['do'] == 'categoryAction') {
      if (!empty($_REQUEST['singleID'])) {
        $_REQUEST['category'] = array(
          $_REQUEST['singleID']);
      }
      if (!empty($_REQUEST['singleAction'])) {
        $_REQUEST['massAction'] = $_REQUEST['singleAction'];
      }
      if (!empty($_REQUEST['category']) && !empty($_REQUEST['massAction'])) {
        $categories = array();
        foreach ($_REQUEST['category'] as $category) {
          $categories[] = $db->Escape($category);
        }
        $list = '"' . implode('", "', $categories) . '"';
        if ($_REQUEST['massAction'] == 'delete') {
          array_walk($_REQUEST['category'], array(
            $this,
            '_adminDeleteCategory'));
        } elseif (is_numeric($_REQUEST['massAction'])) {
          $res = $db->Query('SELECT `language` FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['massAction']);
          list ($lang) = $res->FetchArray(MYSQL_NUM);
          $db->Query('UPDATE {pre}tcsup_plugin_kbcategory SET `language` = ?, parent = ? WHERE id IN (' . $list . ')', $lang, $_REQUEST['massAction']);
          //exit;
        } else {
          $languages = GetAvailableLanguages();
          if (isset($languages[$_REQUEST['massAction']])) {
            $db->Query('UPDATE {pre}tcsup_plugin_kbcategory SET `language` = ?, parent = NULL WHERE id IN (' . $list . ')', $_REQUEST['massAction']);
          }
        }
      }
      $link = '';
      if (!empty($_REQUEST['id'])) {
        $link = '&category=' . $_REQUEST['id'];
      }
      header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase' . $link);
    } elseif ($_REQUEST['do'] == 'addCategory') {
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        if (empty($_REQUEST['parent'])) {
          $_REQUEST['parent'] = 'null';
        }
        if (is_numeric($_REQUEST['parent'])) {
          $res = $db->Query('SELECT `language`, id FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['parent']);
          list ($lang, $parent) = $res->FetchArray(MYSQL_NUM);
        } else {
          $lang = $_REQUEST['parent'];
          $parent = null;
        }
        $db->Query('INSERT INTO {pre}tcsup_plugin_kbcategory (`name`, `description`, `language`, `parent`) VALUES (?, ?, ?, ?)', $_REQUEST['name'], $_REQUEST['description'], $lang, $parent);
        header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $db->InsertId());
      } else {
        $this->_loadCategoryDropdown();
        if (!empty($_REQUEST['id'])) {
          $tpl->assign('tcsup_defaultCategory', $_REQUEST['id']);
        }
        $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.addCategory.tpl'));
      }
    } elseif ($_REQUEST['do'] == 'editCategory') {
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        if (empty($_REQUEST['parent'])) {
          $_REQUEST['parent'] = 'null';
        }
        if (is_numeric($_REQUEST['parent'])) {
          $res = $db->Query('SELECT `language`, id FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['parent']);
          list ($lang, $parent) = $res->FetchArray(MYSQL_NUM);
        } else {
          $lang = $_REQUEST['parent'];
          $parent = null;
        }
        $db->Query('UPDATE {pre}tcsup_plugin_kbcategory SET `name` = ?, `description` = ?, `language` = ?, `parent` = ? WHERE id = ?', $_REQUEST['name'], $_REQUEST['description'], $lang, $parent, $_REQUEST['id']);
        header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $_REQUEST['id']);
      } else {
        $this->_loadCategoryDropdown();
        if (empty($_REQUEST['id'])) {
          header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
          exit();
        }
        $res = $db->Query('SELECT id, name, description, parent, `language` FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['id']);
        $row = $res->FetchArray();
        $tpl->assign('tcsup_data', $row);
        if (empty($row)) {
          header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $_REQUEST['id']);
          exit();
        }
        $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.editCategory.tpl'));
      }
    } elseif ($_REQUEST['do'] == 'addArticle') {
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        if (empty($_REQUEST['parent']) || $_REQUEST['parent'] == 'null') {
          $res = $db->Query('SELECT id FROM {pre}tcsup_plugin_kbcategory LIMIT 1');
          if(!$res->RowCount()) {
            header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
            exit;
          }
          list($id) = $res->FetchArray(MYSQL_NUM);
          $_REQUEST['parent'] = $id;
        }
        $db->Query('INSERT INTO {pre}tcsup_plugin_kbarticle (`title`, `body`, `short_body`, `kbcategory_id`, `date`) VALUES (?, ?, ?, ?, ?)', $_REQUEST['title'], $_REQUEST['body'], $_REQUEST['short_body'], $_REQUEST['parent'], time());
        header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $_REQUEST['parent']);
      } else {
        $this->_loadCategoryDropdown();
        if (!empty($_REQUEST['id'])) {
          $tpl->assign('tcsup_defaultCategory', $_REQUEST['id']);
        }
        global $bm_prefs;
        $tpl->assign('usertpldir', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
        $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.addArticle.tpl'));
      }
    } elseif ($_REQUEST['do'] == 'editArticle') {
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $db->Query('UPDATE {pre}tcsup_plugin_kbarticle SET `title` = ?, `body` = ?, `short_body` = ?, `kbcategory_id` = ? WHERE `id` = ?', $_REQUEST['title'], $_REQUEST['body'], $_REQUEST['short_body'], $_REQUEST['parent'], $_REQUEST['id']);
        header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $_REQUEST['parent']);
      } else {
        $this->_loadCategoryDropdown();
        if (empty($_REQUEST['id'])) {
          header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
          exit();
        }
        $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_kbarticle WHERE id = ?', $_REQUEST['id']);
        $row = $res->FetchArray();
        $tpl->assign('tcsup_data', $row);
        if (empty($row)) {
          header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
          exit();
        }
        global $bm_prefs;
        $tpl->assign('usertpldir', B1GMAIL_REL . 'templates/' . $bm_prefs['template'] . '/');
        $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.editArticle.tpl'));
      }
    } elseif ($_REQUEST['do'] == 'articleAction') {
      $this->_adminArticleAction();
    } elseif ($_REQUEST['do'] == 'importFaq') {
      $res = $db->Query('SELECT `language` FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['id']);
      if(!$res->RowCount()) {
        header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
        exit;
      }
      list ($lang) = $res->FetchArray(MYSQL_NUM);
      $res = $db->Query('SELECT * FROM {pre}faq WHERE lang = ? OR lang = ? AND typ = ? OR typ = ?', $lang, ':all:', 'li', 'both');
      while(($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
        $db->Query('INSERT INTO {pre}tcsup_plugin_kbarticle (`title`, `body`, `short_body`, `kbcategory_id`, `date`) VALUES (?, ?, ?, ?, ?)', $row['frage'], $row['antwort'], '', $_REQUEST['id'], time());
      }
      header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase&category=' . $_REQUEST['id']);
    } elseif($_REQUEST['do'] == 'showRatings') {
      $this->_adminShowRatingsAction();
    } else {
      header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase');
    }
  }

  function _adminShowRatingsAction() {
    global $tpl, $db;
    if(!empty($_REQUEST['singleAction']) || !empty($_REQUEST['massAction'])) {
      $this->_adminArticleAction();
    }
    $config = $this->_getConfig();
    if (!isset($_GET['start']) || !is_numeric($_GET['start'])) {
      $_GET['start'] = 0;
    }
    if (isset($_REQUEST['sort']) && !in_array($_REQUEST['sort'], array('positive', 'negative', 'title'))) {
      unset($_REQUEST['sort']);
    }
    if (isset($_REQUEST['order']) && !in_array($_REQUEST['order'], array('asc', 'desc'))) {
      unset($_REQUEST['order']);
    }
    if (!isset($_REQUEST['sort'])) {
      $_REQUEST['sort'] = 'negative';
    }
    if (!isset($_REQUEST['order'])) {
      $_REQUEST['order'] = 'desc';
    }
    $tpl->assign('sortOrder', $_REQUEST['order']);
    $tpl->assign('sortOrderInv', $_REQUEST['order'] == 'asc' ? 'desc' : 'asc');
    $tpl->assign('sortColumn', $_REQUEST['sort']);
    $sql = 'SELECT id, title, COUNT(kbarticle_id) AS votes, IFNULL(SUM(positive),-1) AS positive, IFNULL(COUNT(kbarticle_id) - SUM(positive), -1) AS negative FROM {pre}tcsup_plugin_kbarticle ';
    $sql .= 'LEFT OUTER JOIN {pre}tcsup_plugin_kbrating ON id = kbarticle_id ';
//    $sql .= 'WHERE kbarticle_id IS NOT NULL ';
    $sql .= 'GROUP BY id ORDER BY ' . $_REQUEST['sort'] . ' ' . $_REQUEST['order'] . ' ';
    $sql .= 'LIMIT ' . (int) $_GET['start'] . ',' . ($config['eintraege_pro_seite'] + 1);
    $res = $db->Query($sql);
    $articles = array();
    while(($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $articles[] = $this->_addRatingsToRow($row);
    }
    $this->_loadCategoryDropdown();
    $tpl->assign('tcsup_articles', $articles);
    $tpl->assign('page', $this->_templatePath('tcsup.admin.kb.ratings.tpl'));
  }

  function _adminArticleAction() {
    global $db;
    if (!empty($_REQUEST['singleID'])) {
      $_REQUEST['article'] = array(
        $_REQUEST['singleID']);
    }
    if (!empty($_REQUEST['singleAction'])) {
      $_REQUEST['massAction'] = $_REQUEST['singleAction'];
    }
    if (!empty($_REQUEST['article']) && !empty($_REQUEST['massAction'])) {
      $articles = array();
      foreach ($_REQUEST['article'] as $article) {
        $articles[] = $db->Escape($article);
      }
      $list = '"' . implode('", "', $articles) . '"';
      if ($_REQUEST['massAction'] == 'delete') {
        $db->Query('DELETE FROM {pre}tcsup_plugin_kbrating WHERE `kbarticle_id` IN (' . $list . ')');
        $db->Query('DELETE FROM {pre}tcsup_plugin_kbarticle WHERE `id` IN (' . $list . ')');
      } elseif ($_REQUEST['massAction'] == 'deleteRatings') {
        $db->Query('DELETE FROM {pre}tcsup_plugin_kbrating WHERE `kbarticle_id` IN (' . $list . ')');
      } elseif (is_numeric($_REQUEST['massAction'])) {
        $db->Query('UPDATE {pre}tcsup_plugin_kbarticle SET `kbcategory_id` = ? WHERE `id` IN (' . $list . ')', $_REQUEST['massAction']);
      }
    }
    $link = '';
    if (!empty($_REQUEST['massAction']) && is_numeric($_REQUEST['massAction'])) {
      $link = '&category=' . $_REQUEST['massAction'];
    } elseif (!empty($_REQUEST['id'])) {
      $link = '&category=' . $_REQUEST['id'];
    }
    if (empty($_REQUEST['do']) || $_REQUEST['do'] != 'showRatings') {
      header('Location: ' . $this->_adminLink(true) . '&action=knowledgebase' . $link);
    }
  }

  function _adminDeleteCategory($category) {
    global $db;
    $db->Query('DELETE FROM {pre}tcsup_plugin_kbrating WHERE `kbarticle_id` IN (SELECT id FROM {pre}tcsup_plugin_kbarticle WHERE kbcategory_id = ?)', $category);
    $db->Query('DELETE FROM {pre}tcsup_plugin_kbarticle WHERE kbcategory_id = ?', $category);
    $res = $db->Query('SELECT id FROM {pre}tcsup_plugin_kbcategory WHERE parent = ?', $category);
    while (($row = $res->FetchArray(MYSQL_NUM)) !== false) {
      $this->_adminDeleteCategory($row[0]);
    }
    $res->Free();
    $db->Query('DELETE FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $category);
  }

  function _adminSettings() {
    global $tpl, $db;
    if (isset($_REQUEST['do']) && $_REQUEST['do'] == 'save') {
      $db->Query('UPDATE {pre}tcsup_plugin_settings SET
        tickets_aktiviert=?,
        knowledgebase_aktiviert=?,
        admin_benachrichtigen=?,
        admin_benachrichtigen_an=?,
        user_benachrichtigen=?,
        user_benachrichtigen_von=?,
        ticket_nummer=?', !empty($_REQUEST['tickets_aktiviert']), !empty($_REQUEST['knowledgebase_aktiviert']), !empty($_REQUEST['admin_benachrichtigen']), $_REQUEST['admin_benachrichtigen_an'], !empty($_REQUEST['user_benachrichtigen']), $_REQUEST['user_benachrichtigen_von'], $_REQUEST['ticket_nummer']);
    }
    $tpl->assign('page', $this->_templatePath('tcsup.admin.settings.tpl'));
  }

  function _adminTickets() {
    global $tpl, $db;
    /* @var $db DB */
    if (empty($_REQUEST['do'])) {
      $tickets = $this->_getTickets();
      $tpl->assign('tcsup_tickets', $tickets);
      $tpl->assign('page', $this->_templatePath('tcsup.admin.ticket.index.tpl'));
    } elseif ($_REQUEST['do'] == 'massAction' && (!empty($_REQUEST['massAction']) || !empty($_REQUEST['singleAction'])) && (!empty($_REQUEST['ticket']) || !empty($_REQUEST['singleID']))) {
      if (!empty($_REQUEST['singleID'])) {
        $_REQUEST['ticket'] = array(
          $_REQUEST['singleID']);
      }
      if (!empty($_REQUEST['singleAction'])) {
        $_REQUEST['massAction'] = $_REQUEST['singleAction'];
      }
      $action = $_REQUEST['massAction'];
      $tickets = array();
      foreach ($_REQUEST['ticket'] as $ticket) {
        $tickets[] = $db->Escape($ticket);
      }
      $list = '"' . implode('", "', $tickets) . '"';

      if (strlen($action) == 1 && is_numeric($action)) {
        $db->Query('UPDATE {pre}tcsup_plugin_ticket SET priority = ? WHERE id IN (' . $list . ')', $action);
      } elseif ($action == 'delete') {
        $res = $db->Query('SELECT id FROM {pre}tcsup_plugin_ticket_file WHERE ticket_content_id IN (SELECT id FROM {pre}tcsup_plugin_ticket_content WHERE ticket_id IN (' . $list . '))');
        while (($row = $res->FetchArray(MYSQL_NUM)) !== false) {
          $this->_deleteTicketContentAttachment($row[0]);
        }
        $db->Query('DELETE FROM {pre}tcsup_plugin_ticket WHERE id IN (' . $list . ')');
        $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_content WHERE ticket_id IN (' . $list . ')');
      } elseif (substr($action, 0, 4) == 'mark') {
        $db->Query('UPDATE {pre}tcsup_plugin_ticket_content SET unread_admin = ' . ($action == 'markunread' ? '1' : '0') . ' WHERE ticket_id IN (' . $list . ')');
      } elseif (is_numeric(substr($action, 1, 1))) {
        $db->Query('UPDATE {pre}tcsup_plugin_ticket SET status = ? WHERE id IN (' . $list . ')', $action);
      }
      $link = '';
      if (isset($_REQUEST['sort']) && isset($_REQUEST['order'])) {
        $link = '&sort=' . $_REQUEST['sort'] . '&order=' . $_REQUEST['order'];
      }
      header('Location: ' . $this->_adminLink(true) . '&action=tickets' . $link);
    } else if ($_REQUEST['do'] == 'details' && isset($_REQUEST['ticket'])) {
      $ticket = $this->_getTicket($_REQUEST['ticket']);
      if ($ticket == null) {
        header('Location: ' . $this->_adminLink(true) . '&action=tickets');
        exit();
      }
      $userObject = _new('BMUser', array($ticket['user_id']));
      $tpl->assign('tcsup_user', $userObject->Fetch());
      $db->Query('UPDATE {pre}tcsup_plugin_ticket_content SET unread_admin = 0 WHERE ticket_id = ?', $ticket['id']);
      $tpl->assign('tcsup_ticket', $ticket);
      $tpl->assign('page', $this->_templatePath('tcsup.admin.ticket.details.tpl'));
    } else if ($_REQUEST['do'] == 'saveDetails' && !empty($_REQUEST['ticket'])) {
      $ticket = $this->_getTicket($_REQUEST['ticket']);
      if ($ticket == null) {
        header('Location: ' . $this->_adminLink(true) . '&action=tickets');
        exit();
      }
      $db->Query('UPDATE {pre}tcsup_plugin_ticket SET subject = ?, status = ?, priority = ? WHERE id = ?', $_REQUEST['subject'], $_REQUEST['status'], $_REQUEST['priority'], $ticket['id']);
      $noHtmlContent = strip_tags($_REQUEST['content'], '<img>');
      if(!empty($noHtmlContent)) {
        $this->_addTicketContent($ticket['id'], $_REQUEST['content'], true);
      }
      header('Location: ' . $this->_adminLink(true) . '&action=tickets&do=details&ticket=' . $ticket['id']);
      exit();
    /*} else if ($_REQUEST['do'] == 'addComment' && !empty($_REQUEST['ticket'])) {
      $ticket = $this->_getTicket($_REQUEST['ticket']);
      if ($ticket == null) {
        header('Location: ' . $this->_adminLink(true) . '&action=tickets');
        exit();
      }
      $this->_addTicketContent($ticket['id'], $_REQUEST['content'], true);
      header('Location: ' . $this->_adminLink(true) . '&action=tickets&do=details&ticket=' . $ticket['id']);
      exit();*/
    } else if ($_REQUEST['do'] == 'deleteComment' && !empty($_REQUEST['comment'])) {
      $res = $db->Query('SELECT ticket_id FROM {pre}tcsup_plugin_ticket_content WHERE id = ?', $_REQUEST['comment']);
      if (!$res->RowCount()) {
        header('Location: ' . $this->_adminLink(true) . '&action=tickets');
        exit();
      }
      list ($ticketId) = $res->FetchArray(MYSQL_NUM);
      $res->Free();
      $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_content WHERE id = ?', $_REQUEST['comment']);
      header('Location: ' . $this->_adminLink(true) . '&action=tickets&do=details&ticket=' . $ticketId);
      exit();
    } else if ($_REQUEST['do'] == 'toggleGroup') {
      if(isset($_GET['state']) && isset($_GET['group'])) {
        $this->_addToSession($_GET['group'], $_GET['state']);
      }
      exit();
    } else if ($_REQUEST['do'] == 'getFile' && !empty($_REQUEST['file'])) {
      $this->_sendTicketContentAttachment($_REQUEST['file']);
    } else if ($_REQUEST['do'] == 'deleteFile' && !empty($_REQUEST['file']) && !empty($_REQUEST['ticket'])) {
      $this->_deleteTicketContentAttachment($_REQUEST['file']);
      $ticketId = $_REQUEST['ticket'];
      header('Location: ' . $this->_adminLink(true) . '&action=tickets&do=details&ticket=' . $ticketId);
      exit();
    } else {
      $link = '';
      if (isset($_REQUEST['sort']) && isset($_REQUEST['order'])) {
        $link = '&sort=' . $_REQUEST['sort'] . '&order=' . $_REQUEST['order'];
      }
      header('Location: ' . $this->_adminLink(true) . '&action=tickets' . $link);
      exit();
    }
  }

  function _deleteTicketContentAttachment($fileId, $userId = null) {
    /* @var $db Db */
    global $db;
    $res = $db->Query('SELECT id, user_id FROM {pre}tcsup_plugin_ticket_file WHERE id = ?', $fileId);
    if(!$res->RowCount()) {
      $res->Free();
      return false;
    }
    $row = $res->FetchArray(MYSQL_ASSOC);
    if($userId !== null && $userId != $row['user_id']) {
      $res->Free();
      return false;
    }
    $fileName = DataFilename($row['id'], 'tcsup');
    if(file_exists($fileName)) {
      if(!@unlink($fileName)) {
        PutLog(sprintf('Failed to delete uploaded file <%s>', $fileName), PRIO_ERROR, __FILE__, __LINE__);
        return false;
      }
    }
    $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_file WHERE id = ?', $fileId);
    return true;
  }

  function _addToSession($key, $value) {
    $tcsup = isset($_SESSION['tcsup']) ? $_SESSION['tcsup'] : array();
    $tcsup[$key] = $value;
    $_SESSION['tcsup'] = $tcsup;
  }

  function OnSearch($query) {
    global $db, $lang_user;

    // prepare
    $results = array();
    $q = '\'%' . $db->Escape($query) . '%\'';
    $thisResults = array();
    $res = $db->Query('SELECT id, title FROM {pre}tcsup_plugin_kbarticle WHERE title LIKE ' . $q . ' OR body LIKE ' . $q . ' ORDER BY title ASC');
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $thisResults[] = array('icon' => 'ico_ok',
        'title' => $row['title'],
        'link' => sprintf('start.php?action=support&amp;article=%d&', $row['id']));
    }
    $res->Free();

    if (count($thisResults) > 0)
      $results[] = array(
        'title' => $lang_user['tcsup.knowledgebase'],
        'results' => $thisResults);
    return $results;
  }

  function GetUserPages($loggedin) {
    global $lang_user, $tpl, $userRow;
    if (empty($lang_user) || !isset($lang_user['tcsup.hilfe'])) {
      return array();
    }
    $config = $this->_getConfig();
    if ($loggedin && ($config['knowledgebase_aktiviert'] || $config['tickets_aktiviert'])) {
      global $db;
      $res = $db->Query('SELECT COUNT( DISTINCT {pre}tcsup_plugin_ticket_content.ticket_id )
        FROM {pre}tcsup_plugin_ticket
        JOIN {pre}tcsup_plugin_ticket_content ON {pre}tcsup_plugin_ticket.id = {pre}tcsup_plugin_ticket_content.ticket_id
        WHERE {pre}tcsup_plugin_ticket_content.unread_user = ? AND {pre}tcsup_plugin_ticket.user_id = ?', true, $userRow['id']);
      list ($ticketsUnread) = $res->FetchArray(MYSQL_NUM);
      $res->Free();
      $text = $lang_user['tcsup.hilfe'];
      if($ticketsUnread > 0) {
        $text .= ' (' . $ticketsUnread . ')';
      }
      $tpl->assign('tcsup_ticketsUnread', $ticketsUnread);
      return array(
        'supp.tab' => array(
          'icon' => '/../prefs_faq',
          'link' => 'start.php?action=support&sid=',
          'text' => $text,
          'order' => 999));
    } else {
      return array();
    }
  }

  function FileHandler($file, $action) {
    global $tpl;
    if ($file == 'index.php' && $action == 'support') {
      $this->_init();
      $this->_loadArticle();
      $tpl->assign('tcsup_notPrint', true);
      $tpl->display($this->_templatePath('tcsup.user.kb.print.tpl'));
      exit();
    } elseif ($file == 'start.php' && $action == 'support') {
      $tpl->assign('tcsup_url', $file . '?action=' . $action);
      $tpl->assign('activeTab', 'supp.tab');
      $tpl->assign('pageMenuFile', $this->_templatePath('tcsup.user.sidebar.tpl'));
      $cats = $this->_loadCategories();
      $config = $this->_getConfig();
      if (!count($cats)) {
        $this->_config['knowledgebase_aktiviert'] = $config['knowledgebase_aktiviert'] = false;
      }
      $this->_init();
      if (empty($_REQUEST['module'])) {
        if (!$config['knowledgebase_aktiviert'] && $config['tickets_aktiviert']) {
          $_REQUEST['module'] = 'tickets';
        } elseif ($config['knowledgebase_aktiviert']) {
          $_REQUEST['module'] = 'kb';
        } else {
          exit();
        }
      }
      if ($_REQUEST['module'] == 'kb') {
        if (!$config['knowledgebase_aktiviert']) {
          $this->_redirectUser();
        }
        $this->_userKnowledgebase();
      } elseif ($_REQUEST['module'] == 'tickets') {
        if (!$config['tickets_aktiviert']) {
          $this->_redirectUser();
        }
        $this->_userTickets();
      } else {
        $this->_redirectUser();
      }
    } elseif ($file == 'prefs.php' && $action == 'faq') {
      $config = $this->_getConfig();
      $cats = $this->_loadCategories();
      if (!count($cats)) {
        $this->_config['knowledgebase_aktiviert'] = $config['knowledgebase_aktiviert'] = false;
      }
      if ($config['knowledgebase_aktiviert']) {
        $this->_redirectUser();
      }
    }
  }

  function _userTickets() {
    global $tpl, $lang_user, $db, $userRow, $groupRow;
    /* @var $db DB */
    $db = $db;
    $tpl->assign('pageTitle', $lang_user['tcsup.tickets']);
    $tpl->addJSFile('li', $tpl->tplDir . 'js/email.js');
    if (empty($_REQUEST['do'])) {
      $tickets = $this->_getTickets($userRow['id']);
      $tpl->assign('tcsup_tickets', $tickets);
      $tpl->assign('pageContent', $this->_templatePath('tcsup.user.ticket.index.tpl'));
    } elseif ($_REQUEST['do'] == 'new') {
      if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
        $invalidFields = array();
        if (empty($_REQUEST['subject']) || strlen($_REQUEST['subject']) < 3) {
          $invalidFields[] = 'subject';
        }
        if (empty($_REQUEST['content']) || strlen($_REQUEST['content']) < 3) {
          $invalidFields[] = 'content';
        }
        if (!isset($_REQUEST['priority']) || ($_REQUEST['priority'] > 1 || $_REQUEST['priority'] < -1)) {
          $invalidFields[] = 'priority';
        }
        if (count($invalidFields) > 0) {
          // errors => mark fields red and show form again
          $tpl->assign('errorStep', true);
          $tpl->assign('errorInfo', $lang_user['checkfields']);
          $tpl->assign('invalidFields', $invalidFields);
        } else {
          $db->Query('INSERT INTO {pre}tcsup_plugin_ticket(`subject`, `priority`, `user_id`) VALUES(?, ?, ?)', $_REQUEST['subject'], ($_REQUEST['priority'] + 2), $userRow['id']);
          $ticketContentId = $this->_addTicketContent($db->InsertId(), nl2br(htmlspecialchars($_REQUEST['content'])));
          $this->_addTicketContentAttachments($ticketContentId);

          $tpl->assign('title', $lang_user['tcsup.neues_ticket']);
          $tpl->assign('msg', $lang_user['tcsup.ticket_erstellt_text']);
          $tpl->assign('backLink', 'start.php?action=support&module=tickets&' . SID);
          $tpl->assign('pageContent', 'li/msg.tpl');
          $tpl->display('li/index.tpl');
          return;
        }
      }
      $tpl->assign('pageContent', $this->_templatePath('tcsup.user.ticket.new.tpl'));
    } elseif ($_REQUEST['do'] == 'details' && !empty($_REQUEST['ticket'])) {
      $ticket = $this->_getTicket($_REQUEST['ticket']);
      if ($ticket == null) {
        $this->_redirectUser();
      }
      $db->Query('UPDATE {pre}tcsup_plugin_ticket_content SET unread_user = 0 WHERE ticket_id = ?', $ticket['id']);
      $tpl->assign('tcsup_ticket', $ticket);
      $tpl->assign('pageContent', $this->_templatePath('tcsup.user.ticket.details.tpl'));
    } else if ($_REQUEST['do'] == 'addComment' && !empty($_REQUEST['ticket'])) {
      $ticket = $this->_getTicket($_REQUEST['ticket']);
      if ($ticket == null) {
        $this->_redirectUser();
      }
      if ((!empty($_REQUEST['content']) && strlen(trim($_REQUEST['content'])) >= 3) || strlen(trim($_REQUEST['attachments'])) > 3) {
        $ticketContentId = $this->_addTicketContent($ticket['id'], nl2br(htmlspecialchars($_REQUEST['content'])));
        $this->_addTicketContentAttachments($ticketContentId);
      }
      if (!empty($_REQUEST['close'])) {
        $db->Query('UPDATE {pre}tcsup_plugin_ticket SET status = ? WHERE id = ?', '05geschlossen', $ticket['id']);
      }

      $tpl->assign('title', $lang_user['tcsup.kommentar_hinzufuegen']);
      $tpl->assign('msg', $lang_user['tcsup.kommentar_hinzugefuegt_text']);
      $tpl->assign('backLink', 'start.php?action=support&module=tickets&do=details&ticket=' . $ticket['id'] . '&' . SID);
      $tpl->assign('pageContent', 'li/msg.tpl');
      $tpl->display('li/index.tpl');
      return;
    } else if ($_REQUEST['do'] == 'getFile' && !empty($_REQUEST['file'])) {
      $this->_sendTicketContentAttachment($_REQUEST['file'], ($groupRow['wd_member_kbs'] <= 0 ? -1 : $groupRow['wd_member_kbs']), $userRow['id']);
    } else {
      $this->_redirectUser();
    }
    $tpl->display('li/index.tpl');
  }

  function _sendTicketContentAttachment($fileId, $speedLimit = -1, $userId = null) {
    global $db;
    $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_ticket_file WHERE id = ?', $fileId);
    if(!$res->RowCount()) {
      $res->Free();
      $this->_redirectUser();
    }
       $fileInfo = $res->FetchArray(MYSQL_ASSOC);
       if($userId != null && $userId != $fileInfo['user_id']) {
      $res->Free();
      $this->_redirectUser();
       }
       $fileName = DataFilename($fileInfo['id'], 'tcsup');
       // send file
    header('Pragma: public');
    header('Content-Type: ' . $fileInfo['contenttype']);
    header('Content-Length: ' . $fileInfo['size']);
    header('Content-Disposition: ' . (isset($_REQUEST['view']) ? 'inline' : 'attachment') . '; filename="' . addslashes($fileInfo['dateiname']) . '"');
    SendFile($fileName, $speedLimit);
  }

  function _addTicketContentAttachments($ticketContentId) {
    global $userRow, $groupRow;
    $attSize = 0;
    if (strlen(trim($_REQUEST['attachments'])) > 3) {
      $attachments = explode(';', $_REQUEST['attachments']);
      foreach ($attachments as $attachment) {
        if (strlen(trim($attachment)) > 3) {
          list ($tempFileID, $fileName, $contentType) = explode(',', $attachment);
          $tempFileID = (int) $tempFileID;
          if (ValidTempFile($userRow['id'], $tempFileID)) {
            if (($attSize + filesize(TempFileName($tempFileID))) <= $groupRow['anlagen']) {
              if (@is_readable(TempFileName($tempFileID))) {
                $this->_addTicketContentAttachment($ticketContentId, TempFileName($tempFileID), $fileName, $contentType);
                $attSize += filesize(TempFileName($tempFileID));
              }
            }
            ReleaseTempFile($userRow['id'], $tempFileID);
          }
        }
      }
    }
  }

  function _addTicketContentAttachment($ticketContentId, $tempFileName, $fileName, $mimeType) {
    /* @var $db DB */
    global $db, $userRow;
    if(!file_exists($tempFileName) || !is_readable($tempFileName)) {
      PutLog(sprintf('Failed to read uploaded file <%s>', $tempFileName), PRIO_ERROR, __FILE__, __LINE__);
      return false;
    }
    $size = filesize($tempFileName);
    $db->Query('INSERT INTO {pre}tcsup_plugin_ticket_file (`ticket_content_id`, `dateiname`, `size`, `contenttype`, `created`, `modified`, `accessed`, `user_id`) VALUES (?, ?, ?, ?, ?, ?, ?, ?);',
      $ticketContentId, $fileName, $size, $mimeType, time(), time(), time(), $userRow['id']);
       $fileId = $db->InsertId();
    $newFileName = DataFilename($fileId, 'tcsup');
    if(!@copy($tempFileName, $newFileName)) {
      PutLog(sprintf('Failed to move uploaded file <%s> to <%s>', $tempFileName, $fileName), PRIO_ERROR, __FILE__, __LINE__);
      $db->Query('DELETE FROM {pre}tcsup_plugin_ticket_file WHERE id = ?', $fileId);
      return false;
    }
    return true;
  }

  function _addTicketContent($ticketId, $content, $admin = false) {
    global $db;
    /* @var $db DB */
    $ticket = $this->_getTicket($ticketId);
    if ($ticket == null) {
      return 0;
    }
    if($admin) {
      global $adminRow;
      if(!empty($adminRow)) {
        $admin = $adminRow['username'];
      }
    }

    $q = 'INSERT INTO {pre}tcsup_plugin_ticket_content(`ticket_id`, `admin`, `content`, `unread_user`, `unread_admin`, `date`) VALUES(?, ?, ?, ?, ?, ?)';
    $db->Query($q, $ticketId, $admin, $content, $admin, !$admin, time());
    $id = $db->InsertId();

    if(empty($admin)) {
      PutLog('User #' . $ticket['user_id'] . ' commented ticket #' . $ticket['id'], PRIO_NOTE, __FILE__, __LINE__);
    } else {
      PutLog('Admin "' . $admin . '" commented ticket #' . $ticket['id'], PRIO_NOTE, __FILE__, __LINE__);
    }

    global $bm_prefs, $lang_custom;
    $config = $this->_getConfig();
    if ($config['admin_benachrichtigen'] && !$admin) {
      $vars = array('ticket_id' => $ticketId,
        'ticket_number' => $this->_formatTicketNumber(array('id' => $ticketId)),
        'subject' => $ticket['subject'],
        'date' => FormatDate(),
        'content' => htmlToText($content),
        'link' => sprintf('%sadmin/?jump=%s', $bm_prefs['selfurl'], urlencode($this->_adminLink() . '&action=tickets&do=details&ticket=' . $ticket['id'])));
      SystemMail($bm_prefs['passmail_abs'], $config['admin_benachrichtigen_an'], $lang_custom['tcsup_admin_benachrichtigung_betreff'], 'tcsup_admin_benachrichtigung_text', $vars);
    } elseif ($config['user_benachrichtigen'] && $admin) {
      $vars = array('ticket_id' => $ticketId,
        'ticket_number' => $this->_formatTicketNumber(array('id' => $ticketId)),
        'date' => FormatDate(),
        'subject' => $ticket['subject'],
        'content' => htmlToText($content));
      $userId = $ticket['user_id'];
      $user = _new('BMUser', array($userId));
      $userRow = $user->Fetch();
      foreach ($userRow as $key => $value) {
        $vars[$key] = $value;
      }
      SystemMail($config['user_benachrichtigen_von'], $user->GetDefaultSender(), $lang_custom['tcsup_user_benachrichtigung_betreff'], 'tcsup_user_benachrichtigung_text', $vars, $userId);
    }

    return $id;
  }

  function _getTicket($ticketId, $userId = null) {
    global $db;
    /* @var $db DB */
    $q = 'SELECT * FROM {pre}tcsup_plugin_ticket WHERE ';
    if ($userId !== null) {
      $q .= 'user_id = "' . $db->Escape($userId) . '" AND ';
    }
    $q .= 'id = "' . $db->Escape($ticketId) . '"';
    $res = $db->Query($q);
    if (!$res->RowCount()) {
      return null;
    }
    $ticket = $res->FetchArray(MYSQL_ASSOC);
    $ticket['ticket_number'] = $this->_formatTicketNumber($ticket);
    $res->Free();

    $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_ticket_content WHERE ticket_id = ? ORDER BY date DESC', $ticketId);
    $contents = $list = array();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $list[] = $db->Escape($row['id']);
      $contents[] = $row;
    }
    $res->Free();
    $list = '\'' . implode('\',\'', $list) . '\'';

    $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_ticket_file WHERE ticket_content_id IN (' . $list . ') ORDER BY dateiname ASC');
    $files = array();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      if(!isset($files[$row['ticket_content_id']])) {
        $files[$row['ticket_content_id']] = array();
      }
      $files[$row['ticket_content_id']][] = $row;
    }
    $res->Free();

    $ticket['contents'] = $contents;
    $ticket['files'] = $files;
    return $ticket;
  }

  function _getTickets($userId = null) {
    global $db, $tpl;
    /* @var $db DB */
    $config = $this->_getConfig();

    if (!isset($_GET['start']) || !is_numeric($_GET['start'])) {
      $_GET['start'] = 0;
    }
    if (isset($_REQUEST['sort']) && !in_array($_REQUEST['sort'], array('id',
      'date',
      'unread',
      'subject',
      'priority'))) {
      unset($_REQUEST['sort']);
    }
    if (isset($_REQUEST['order']) && !in_array($_REQUEST['order'], array('asc',
      'desc'))) {
      unset($_REQUEST['order']);
    }
    if (!isset($_REQUEST['sort'])) {
      $_REQUEST['sort'] = 'date';
    }
    if (!isset($_REQUEST['order'])) {
      $_REQUEST['order'] = 'desc';
    }
    $tpl->assign('sortOrder', $_REQUEST['order']);
    $tpl->assign('sortOrderInv', $_REQUEST['order'] == 'asc' ? 'desc' : 'asc');
    $tpl->assign('sortColumn', $_REQUEST['sort']);
    $q = 'SELECT *,
        (SELECT `date` FROM {pre}tcsup_plugin_ticket_content WHERE ticket_id = {pre}tcsup_plugin_ticket.id ORDER BY `date` DESC LIMIT 1) AS `date`,
        (SELECT ';
    if (ADMIN_MODE) {
      $q .= 'unread_admin';
    } else {
      $q .= 'unread_user';
    }
    $q .= ' FROM {pre}tcsup_plugin_ticket_content WHERE `ticket_id` = {pre}tcsup_plugin_ticket.id ORDER BY `date` DESC LIMIT 1) AS `unread`
        FROM {pre}tcsup_plugin_ticket';
    if ($userId !== null) {
      $q .= ' WHERE user_id = "' . $db->Escape($userId) . '"';
    }
    $q .= ' ORDER BY status ASC, ' . $_REQUEST['sort'] . ' ' . $_REQUEST['order'];
    $q .= ' LIMIT ' . (int) $_GET['start'] . ',' . ($config['eintraege_pro_seite'] + 1);
    $res = $db->Query($q);
    $tickets = array();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $row['ticket_number'] = $this->_formatTicketNumber($row);
      $tickets[] = $row;
    }
    $res->Free();
    $tpl->assign('tcsup_start', (int) $_GET['start']);
    $tpl->assign('tcsup_hasMore', count($tickets) > $config['eintraege_pro_seite']);
    $tickets = array_slice($tickets, 0, $config['eintraege_pro_seite']);
    return $tickets;
  }

  function _formatTicketNumber($ticket) {
    $config = $this->_getConfig();
    $ticketNumber = str_replace('?', $ticket['id'], $config['ticket_nummer']);
    return $ticketNumber;
  }

  var $_config = null;

  function _getConfig() {
    global $db;
    if ($this->_config == null) {
      $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_settings LIMIT 1');
      $config = $res->FetchArray();
      $res->Free();
      $this->_config = $config;
    }
    return $this->_config;
  }

  function _userKnowledgebase() {
    global $tpl, $lang_user, $lang_custom, $thisUser, $userRow, $groupRow, $currentCharset, $bm_prefs;
    $tpl->assign('pageTitle', $lang_user['tcsup.knowledgebase']);
    if (empty($_REQUEST['do'])) {
      if (empty($_REQUEST['article'])) {
        $this->_loadCategoryOverview();
        $this->_loadCategory();
        $tpl->assign('pageContent', $this->_templatePath('tcsup.user.kb.index.tpl'));
      } else {
        $this->_loadArticle();
        $tpl->assign('pageContent', $this->_templatePath('tcsup.user.kb.article.tpl'));
      }
      $tpl->assign('tcsup_pageBreadcrumb', $this->_templatePath('tcsup.user.kb.breadcrumb.tpl'));
      $tpl->display('li/index.tpl');
    } elseif ($_REQUEST['do'] == 'vote') {
      $this->_loadArticle();
      $this->_doRating();
    } elseif ($_REQUEST['do'] == 'print') {
      $this->_loadArticle();
      $tpl->display($this->_templatePath('tcsup.user.kb.print.tpl'));
    } elseif ($_REQUEST['do'] == 'send') {
      if (!class_exists('Safecode')) {
        include (B1GMAIL_DIR . '/serverlib/safecode.class.php');
      }
      $row = $this->_loadArticle();
      $tpl->assign('tcsup_text', $text = sprintf($this->_parseBody($lang_custom['tcsup_mail_text']), $row['id']));
      $possibleSenders = $thisUser->GetPossibleSenders();
      if (!empty($_POST)) {
        if (!empty($_POST['text']))
          $text = $_POST['text'];
        $tpl->assign('backLink', $_SERVER['REQUEST_URI'] . (isset($_POST['to']) ? '&amp;to=' . urlencode($_POST['to']) : '') . (isset($_POST['text']) ? '&amp;text=' . urlencode($_POST['text']) : ''));
        if ($bm_prefs['mail_send_code'] == 'yes' && (strlen($code = Safecode::GetCode((int) $_REQUEST['codeID'])) < 4 || trim(strtolower($_REQUEST['safecode'])) != strtolower($code))) {
          Safecode::ReleaseCode((int) $_REQUEST['codeID']);
          $tpl->assign('msg', $lang_user['invalidcode']);
          $tpl->assign('pageContent', 'li/error.tpl');
        } else {
          if (($userRow['last_send'] + $groupRow['send_limit']) > time()) {
            $tpl->assign('msg', sprintf($lang_user['waituntil3'], ($userRow['last_send'] + $groupRow['send_limit']) - time()));
            $tpl->assign('pageContent', 'li/error.tpl');
          } else {
            if (!isset($_POST['from']) || !isset($possibleSenders[$_POST['from']])) {
              $from = $possibleSenders[0];
            } else {
              $from = $possibleSenders[$_POST['from']];
            }
            $recipients = ExtractMailAddresses($_POST['to']);
            if (count($recipients) == 0) {
              $tpl->assign('msg', $lang_user['norecipients']);
              $tpl->assign('pageContent', 'li/error.tpl');
            } elseif (count($recipients) > $bm_prefs['max_bcc']) {
              $tpl->assign('msg', sprintf($lang_user['toomanyrecipients'], $bm_prefs['max_bcc'], count($recipients)));
              $tpl->assign('pageContent', 'li/error.tpl');
            } else {
              if (!class_exists('BMMailBuilder'))
                include (B1GMAIL_DIR . 'serverlib/mailbuilder.class.php');
              if (!class_exists('BMMail'))
                include (B1GMAIL_DIR . 'serverlib/mail.class.php');
              if (!class_exists('BMMailbox'))
                include (B1GMAIL_DIR . 'serverlib/mailbox.class.php');
              $mail = _new('BMMailBuilder', array(
                true));
              $to = trim(str_replace(array(
                "\r",
                "\t",
                "\n"), '', $_POST['to']));
              $mail->AddHeaderField('From', $from);
              $mail->AddHeaderField('To', $to);
              $mail->AddHeaderField('Subject', $lang_user['tcsup.knowledgebase'] . ': ' . $row['title']);

              $text = trim($text);
              ModuleFunction('OnSendMail', array(
                &$text,
                false));
              $mail->AddText($text, 'plain', $currentCharset);

              $outboxFP = $mail->Send();
              if ($outboxFP && is_resource($outboxFP)) {
                Add2Stat('send');
                $domains = explode(':', $bm_prefs['domains']);
                $local = false;
                foreach ($domains as $domain)
                  if (strpos(strtolower($to), '@' . strtolower($domain)) !== false)
                    $local = true;
                Add2Stat('send_' . ($local ? 'intern' : 'extern'));
                $thisUser->UpdateLastSend(count($recipients));

                PutLog(sprintf('<%s> (%d, IP: %s) sends mail from <%s> to <%s> using knowledgebase recommend form', $userRow['email'], $userRow['id'], $_SERVER['REMOTE_ADDR'], ExtractMailAddress($from), implode('>, <', $recipients)), PRIO_NOTE, __FILE__, __LINE__);

                ModuleFunction('AfterSendMail', array(
                  $userRow['id'],
                  ExtractMailAddress($from),
                  $recipients));

                $saveTo = FOLDER_OUTBOX;
                $mailObj = _new('BMMail', array(
                  0,
                  false,
                  $outboxFP,
                  false));
                $mailObj->Parse();
                $mailObj->ParseInfo();
                $mailbox = _new('BMMailbox', array(
                  $userRow['id'],
                  $userRow['email'],
                  $thisUser));
                $mailbox->StoreMail($mailObj, $saveTo);

                $tpl->assign('pageContentText', '<script type="text/javascript">parent.location.href = "start.php?action=support&article=' . $row['id'] . '&' . SID . '";</script>');
              } else {
                $tpl->assign('msg', $lang_user['sendfailed']);
                $tpl->assign('pageContent', 'li/error.tpl');
              }
              $mail->CleanUp();
            }
          }
        }
      }
      if (isset($_REQUEST['to'])) {
        $tpl->assign('to', $_REQUEST['to']);
      }
      if (isset($_REQUEST['text'])) {
        $tpl->assign('tcsup_text', $_REQUEST['text']);
      }
      // safe code?
      if ($bm_prefs['mail_send_code'] == 'yes') {
        $tpl->assign('codeID', Safecode::RequestCode());
      }
      $tpl->assign('possibleSenders', $possibleSenders);
      $tpl->assign('defaultSender', $userRow['defaultSender']);
      $tpl->display($this->_templatePath('tcsup.user.kb.send.tpl'));
    }
  }

  function _redirectUser() {
    global $thisUser;
    if ($thisUser != null) {
      header('Location: start.php?action=support&' . SID);
    } else {
      header('Location: index.php');
    }
    exit();
  }

  function _loadArticle() {
    global $db, $tpl, $lang_user;
    $res = $db->Query('SELECT * FROM {pre}tcsup_plugin_kbarticle WHERE id = ?', $_REQUEST['article']);
    $row = $res->FetchArray();
    $res->Free();
    if (empty($row)) {
      $this->_redirectUser();
    }
    $row['body'] = $this->_parseBody($row['body']);
    $this->_loadBreadcrumb($row['kbcategory_id']);
    $db->Query('UPDATE {pre}tcsup_plugin_kbarticle SET visits = visits + 1 WHERE id = ?', $row['id']);
    $tpl->assign('tcsup_article', $row);
    $tpl->assign('pageTitle', $lang_user['tcsup.knowledgebase'] . ' - ' . $row['title']);
    $this->_loadRating();
    return $row;
  }

  function _doRating() {
    global $db, $userRow;
    if (!isset($_REQUEST['rating']) || !in_array($_REQUEST['rating'], array('ok',
      'nok'))) {
      $this->_redirectUser();
    }
    $db->Query('REPLACE INTO {pre}tcsup_plugin_kbrating(kbarticle_id, user_id, positive) VALUES (?, ?, ?)', $_REQUEST['article'], $userRow['id'], $_REQUEST['rating'] == 'ok');
    header('Location: start.php?action=support&article=' . $_REQUEST['article'] . '&' . SID);
    exit();
  }

  function _getRating($articleId) {
    global $db;
    $res = $db->Query('SELECT COUNT(*), SUM(positive) FROM {pre}tcsup_plugin_kbrating WHERE kbarticle_id = ?', $articleId);
    list ($total, $positive) = $res->FetchArray(MYSQL_NUM);
    if ($total != 0) {
      $happy = round(100 / $total * $positive);
      $unhappy = 100 - $happy;
      return array('positive' => $happy, 'positive_numb' => $positive, 'negative' => $unhappy, 'negative_numb' => $total - $positive, 'total_numb' => $total);
    }
    return array();
  }

  function _loadRating() {
    global $tpl;
    $tpl->assign('tcsup_rating', $this->_getRating($_REQUEST['article']));
  }

  function _parseBody($body) {
    global $userRow, $bm_prefs;
    $body = str_replace('%%user%%', $userRow['email'], $body);
    $body = str_replace('%%wddomain%%', str_replace('@', '.', $userRow['email']), $body);
    $body = str_replace('%%selfurl%%', $bm_prefs['selfurl'], $body);
    $body = str_replace('%%hostname%%', $bm_prefs['b1gmta_host'], $body);
    $body = str_replace('%%projecttitle%%', $bm_prefs['titel'], $body);
    return $body;
  }

  function _loadCategory() {
    global $db, $tpl, $lang_user;
    if (empty($_REQUEST['category'])) {
      $this->_loadArticles('popular');
      $this->_loadArticles('new');
    } else {
      $res = $db->Query('SELECT id, name, description FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $_REQUEST['category']);
      $row = $res->FetchArray();
      if (empty($row) && !ADMIN_MODE) {
        $this->_redirectUser();
      }
      $tpl->assign('tcsup_categoryActive', $row['id']);
      $tpl->assign('tcsup_categoryDetails', $row);
      $tpl->assign('pageTitle', $lang_user['tcsup.knowledgebase'] . ' - ' . $row['name']);
      $res->Free();
      $this->_loadArticles($_REQUEST['category']);
    }
    $this->_loadBreadcrumb();
  }

  function _loadCategoryDropdown() {
    global $db, $tpl;
    $res = $db->Query('SELECT `id`, `name`, `parent`, `language` FROM {pre}tcsup_plugin_kbcategory ORDER BY `language` ASC, `name` ASC');
    $categories = array();
    $languages = GetAvailableLanguages();
    while (($row = $res->FetchArray(MYSQL_ASSOC)) !== false) {
      $categories[] = $row;
    }
    $dropdown = array();
    foreach (array_keys($languages) as $language) {
      $dropdown[$language] = $this->_getCategoryDropdown($categories, $language);
    }
    $noLang = $this->_getCategoryDropdown($categories, 'null');
    if(!empty($noLang)) {
      $dropdown['0'] = $noLang;
    }
    $tpl->assign('languages', $languages);
    $tpl->assign('tcsup_categoryDropdown', $dropdown);
    $res->Free();
  }

  function _getCategoryDropdown($categories, $language, $parent = null) {
    $result = array();
    foreach ($categories as $category) {
      if ($category['parent'] == 0) {
        $category['parent'] = null;
      }
      if ($category['parent'] == $parent && $category['language'] == $language) {
        $key = $category['id'];
        $result[$key] = $category;
        $result[$key]['sub'] = $this->_getCategoryDropdown($categories, $language, $category['id']);
      }
    }
    return $result;
  }

  function _loadBreadcrumb($category = null) {
    global $db, $tpl;
    if ($category === null) {
      if (!isset($_REQUEST['category'])) {
        return;
      }
      $category = $_REQUEST['category'];
    }
    $breadcrumb = array();
    while (true) {
      $res = $db->Query('SELECT id, name, parent FROM {pre}tcsup_plugin_kbcategory WHERE id = ?', $category);
      $row = $res->FetchArray();
      $res->Free();
      if (empty($row)) {
        return;
      }
      if (isset($breadcrumb[$row['id']])) {
        return;
      }
      $breadcrumb[$row['id']] = $row;
      if (empty($row['parent'])) {
        break;
      }
      $category = $row['parent'];
    }
    $breadcrumb = array_reverse($breadcrumb, true);
    $tpl->assign('tcsup_breadcrumb', $breadcrumb);
  }

  function _addRatingsToRow($row) {
    if($row['votes'] != 0) {
      $row['positive_num'] = $row['positive'];
      $row['negative_num'] = $row['votes'] - $row['positive'];
      $row['positive'] = 100/$row['votes']*$row['positive_num'];
      $row['negative'] = 100-$row['positive'];
    }
    return $row;
  }

  function _loadArticles($category, $sortBy = null, $sortOrder = null) {
    global $db, $tpl, $currentLanguage;
    if ($sortBy == null) {
      $sortBy = 'title';
    }
    $sortBy = '`' . $db->Escape($sortBy) . '`';
    if ($sortOrder == null) {
      $sortOrder = 'ASC';
    }
    $sortOrder = $db->Escape($sortOrder);
    $special = array('popular' => 'visits DESC, title ASC', 'new' => 'date DESC');
    $sql = 'SELECT id, title, short_body, COUNT(kbarticle_id) AS votes, SUM(positive) AS positive FROM {pre}tcsup_plugin_kbarticle ';
    $sql .= 'LEFT OUTER JOIN {pre}tcsup_plugin_kbrating ON id = kbarticle_id ';
    if (!isset($special[$category])) {
      if (!is_numeric($category)) {
        return;
      }
      $sql .= 'WHERE kbcategory_id = ' . (int) $category . ' GROUP BY id ORDER BY ' . $sortBy . ' ' . $sortOrder;
      $name = 'articles';
    } else {
      $sql .= 'WHERE kbcategory_id IN (SELECT id FROM {pre}tcsup_plugin_kbcategory WHERE `language` = \'' . $db->Escape($currentLanguage) . '\')';
      $sql .= ' GROUP BY id ORDER BY ' . $special[$category] . ' LIMIT 10';
      $name = $category;
    }
    $res = $db->Query($sql);
    $articles = array();
    while (($row = $res->FetchArray()) !== false) {
      $articles[] = $this->_addRatingsToRow($row);
    }
    $tpl->assign('tcsup_' . $name, $articles);
    $res->Free();
  }

  function _loadCategories() {
    global $db, $tpl, $currentLanguage;
    $condition = '(parent IS NULL OR parent = 0)';
    $res = $db->Query('SELECT id, name, description FROM {pre}tcsup_plugin_kbcategory WHERE ' . $condition . ' AND `language` = ? ORDER BY name ASC', $currentLanguage);
    $cats = array();
    while (($row = $res->FetchArray()) !== false) {
      $row['count'] = $this->_countArticles($row['id']);
      $cats[] = $row;
    }
    $tpl->assign('tcsup_categories', $cats);
    $res->Free();
    return $cats;
  }

  function _loadCategoryOverview($languageCheck = true, $sortBy = null, $sortOrder = null) {
    global $db, $tpl, $currentLanguage;
    if ($sortBy == null) {
      $sortBy = 'name';
    }
    $sortBy = '`' . $db->Escape($sortBy) . '`';
    if ($sortOrder == null) {
      $sortOrder = 'ASC';
    }
    $sortOrder = $db->Escape($sortOrder);
    $condition = '';
    if (empty($_REQUEST['category'])) {
      $condition .= '(parent IS NULL OR parent = 0)';
    } else {
      $condition .= 'parent = ' . (int) $_REQUEST['category'];
    }
    if ($languageCheck) {
      $condition .= ' AND `language` = "' . $db->Escape($currentLanguage) . '"';
    }
    $res = $db->Query('SELECT id, name, description, `language` FROM {pre}tcsup_plugin_kbcategory WHERE ' . $condition . ' ORDER BY `language` ASC, ' . $sortBy . ' ' . $sortOrder);
    $cats = array();
    while (($row = $res->FetchArray()) !== false) {
      $row['count'] = $this->_countArticles($row['id']);
      $cats[] = $row;
    }
    $tpl->assign('tcsup_categoryOverview', $cats);
    $res->Free();
  }

  function _countArticles($category) {
    global $db;
    $res = $db->Query('SELECT COUNT(*) FROM {pre}tcsup_plugin_kbarticle WHERE kbcategory_id = ?', $category);
    $row = $res->FetchArray(MYSQL_NUM);
    $res->Free();
    if (empty($row)) {
      return 0;
    }
    $count = $row[0];
    $res = $db->Query('SELECT id FROM {pre}tcsup_plugin_kbcategory WHERE parent = ?', $category);
    while (($row = $res->FetchArray(MYSQL_NUM)) !== false) {
      $count += $this->_countArticles($row[0]);
    }
    return $count;
  }
}

/**
 * register plugin
 */
$plugins->registerPlugin('TCCleverSupport');

function TCSUPCategoryOptions($params) {
  $categories = $params['categories'];
  $html = '';
  $level = isset($params['level']) ? $params['level'] : 1;
  $more = isset($params['more']) ? $params['more'] : 1;
  //$ignore = isset($params['ignore']) ? $params['ignore'] : -1;
  $default = isset($params['default']) ? $params['default'] : -1;
  $categoryCount = 0;
  foreach ($categories as $category) {
    $categoryCount++;
    $indent = '-- ';
    if ($level > 0) {
      //$indent .= str_repeat('|&nbsp;', $level - $more) . str_repeat('&nbsp;&nbsp;&nbsp;', max(0, $more - 1));
      $indent .= str_repeat('-- ', max(0, $more - 1));
    }
    $html .= '<option value="' . $category['id'] . '"';
    if ($default == $category['id']) {
      $html .= ' selected="selected"';
    }
    $html .= '>' . $indent . htmlspecialchars($category['name']) . '</option>' . "\n";
    /*if ($ignore == $category['id']) {
      continue;
    }*/
    $html .= TCSUPCategoryOptions(array('categories' => &$category['sub'],
      'level' => $level + 1,
      'more' => $more + 1,
      'default' => $default));
  }

  return $html;
}

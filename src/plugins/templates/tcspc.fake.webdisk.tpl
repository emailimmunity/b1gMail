<?php
/* Das ist eigentlich gar kein Template... */
if (!class_exists('BMWebdisk')) {
    @include (B1GMAIL_DIR . 'serverlib/webdisk.class.php');
}
class TCSpacePlugin_BMWebdisk extends BMWebdisk {
    function GetUsedSpace() {
        global $db, $plugins;

        $res = $db->Query('SELECT gruppe, diskspace_used, mailspace_used FROM {pre}users WHERE id=?', $this->_userID);
        assert('$res->RowCount() != 0');
        list ($group, $diskSpace, $mailSpace) = $res->FetchArray(MYSQL_NUM);
        $res->Free();

        $active = $plugins->GetGroupOptionValue($group, 'TCSpacePlugin', 'tcspc_eingeschaltet', false);
		$spc = TCSpacePlugin::_getUserSettings($this->_userID);
		if(!$active || ($active == 1 && (!is_array($spc) || !$spc['automatisch']))) {
			return $diskSpace;
		}
        
        return ($diskSpace + $mailSpace);
    }
}
?>
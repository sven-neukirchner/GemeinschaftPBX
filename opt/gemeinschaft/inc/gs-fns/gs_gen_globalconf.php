<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5723 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Soeren Sprenger <soeren.sprenger@amooma.de>
* 
* This program is free software; you can redistribute it and/or
* modify it under the terms of the GNU General Public License
* as published by the Free Software Foundation; either version 2
* of the License, or (at your option) any later version.
* 
* This program is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
* GNU General Public License for more details.
* 
* You should have received a copy of the GNU General Public License
* along with this program; if not, write to the Free Software
* Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston,
* MA 02110-1301, USA.
\*******************************************************************/

defined('GS_VALID') or die('No direct access.');
include_once( GS_DIR .'inc/gs-lib.php' );

/***********************************************************
*    Arrays for Translating and Section-Names
***********************************************************/

$path_section_names = array(	'/' 	=> __('Globale Konfiguration'),
				'/gui'	=> __('Oberflächen Konfiguration'),
				'/ldap' => __('LDAP Konfiguration'),
				'/prov' => __('Provisioning Konfiguration'),
				'/prov/aastra' => __('Provisioning AAstra'),
				'/prov/siemens' => __('Provisioning Siemens'),
				'/prov/snom' => __('Provisioning SNOM'),
				'/prov/grandstream' => __('Provisioning Grandstream'),
				'/log'  => __('Logverhalten Konfiguration'),
				'/fax'  => __('Fax Konfiguration'),
				'/boi'  => __('Branch-Office-Integration'), //FIXME - German translation
				'/canonize'  => __('Kanonisierung'),
				'/dialplan'  => __('Rufnummernplan'),
				'/phonebook'  => __('Telefonbuch'),
				'/misc'  => __('Sonstiges')
			);

$option_short_descr = array (
"DB_SIP_REG_UPDATE" => __('SIP-Registrierungen in Slave-Datenbank speichern?'),
"LDAP_HOST" => __('LDAP Host'),
"LDAP_SSL" => __('SSL-Verschluesselung fuer LDAP nutzen?'),
"LDAP_PORT" => __('LDAP Port'),
"LDAP_BINDDN" => __('LDAP Bind-Addresse'),
"LDAP_PWD" => __('LDAP Passwort'),
"LDAP_PROTOCOL" => __('LDAP Protokoll-Version'),
"LDAP_SEARCHBASE" => __('X'),
"LDAP_PROP_USER" => __('X'),
"LDAP_PROP_UID" => __('X'),
"LDAP_PROP_FIRSTNAME" => __('X'),
"LDAP_PROP_LASTNAME" => __('X'),
"LDAP_PROP_PHONE" => __('X'),
"LDAP_PROP_EMAIL" => __('X'),
"GUI_AUTH_METHOD" => __('GUI-Authentifizierungs-Methode'),
"GUI_NUM_RESULTS" => __('Anzahl Ergibnisse fuer GUI (z.b. User)'),
"GUI_SUDO_ADMINS" => __('Administratoren (GUI)'),
"GUI_SUDO_EXTENDED" => __('X'),
"GUI_PERMISSIONS_METHOD" => __('X'),
"GUI_USER_MAP_METHOD" => __('X'),
"GUI_QUEUE_SHOW_NUM_CALLS" => __('X'),
"GUI_QUEUE_INFO_FROM_DB" => __('X'),
"GUI_MON_NOQUEUEBLUE" => __('X'),
"GUI_MON_PEERS_ENABLED" => __('X'),
"GUI_SHUTDOWN_ENABLED" => __('Herunterfahren in GUI aktivieren?'),
"GUI_LANGS" => __('X'),
"GUI_ADDITIONAL_STYLESHEET" => __('X'),
"EXTERNAL_NUMBERS_BACKEND" => __('X'),
"EXTERNAL_NUMBERS_LDAP_PROP" => __('X'),
"NOBODY_EXTEN_PATTERN" => __('Nobody Extension Muster'),
"NOBODY_CID_NAME" => __('Nobody Callerid-Name'),
"PROV_HOST" => __('Provisioning Host'),
"PROV_PORT" => __('X'),
"PROV_SCHEME" => __('X'),
"PROV_PATH" => __('X'),
"PROV_AUTO_ADD_PHONE" => __('Telefone Automatisch hinzufuegen?'),
"PROV_AUTO_ADD_PHONE_HOST" => __('Host, auf dem Dummy-User angelegt werden'),
"PROV_DIAL_LOG_LIFE" => __('Lebensdauer des Dial-Log'),
"PROV_PROXIES_TRUST" => __('X'),
"PROV_PROXIES_XFF_HEADER" => __('X'),
"PROV_ALLOW_NET" => __('X'),
"PROV_LAN_NETS" => __('X'),
"PROV_MODELS_ENABLED_SNOM" => __('X'),
"PROV_MODELS_ENABLED_SIEMENS" => __('X'),
"PROV_MODELS_ENABLED_AASTRA" => __('X'),
"PROV_MODELS_ENABLED_GRANDSTREAM" => __('X'),
"SNOM_PROV_ENABLED" => __('SNOM-Provisioning aktiv?'),
"SNOM_PROV_HTTP_USER" => __('X'),
"SNOM_PROV_HTTP_PASS" => __('X'),
"SNOM_PROV_PB_NUM_RESULTS" => __('X'),
"SNOM_PROV_FW_UPDATE" => __('X'),
"SNOM_PROV_FW_6TO7" => __('X'),
"SNOM_PROV_FW_DEFAULT_300" => __('X'),
"SNOM_PROV_FW_DEFAULT_320" => __('X'),
"SNOM_PROV_FW_DEFAULT_360" => __('X'),
"SNOM_PROV_FW_DEFAULT_370" => __('X'),
"SNOM_PROV_KEY_BLACKLIST" => __('X'),
"SNOM_PROV_M3_ACCOUNTS" => __('X'),
"SIEMENS_PROV_ENABLED" => __('Siemens-Provisioning aktiv?'),
"SIEMENS_PROV_PREFER_HTTP" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS20" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS40" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS60" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS80" => __('X'),
"SIEMENS_PROV_KEY_BLACKLIST" => __('X'),
"AASTRA_PROV_ENABLED" => __('AAstra-Provisioning aktiv?'),
"AASTRA_PROV_PB_NUM_RESULTS" => __('X'),
"AASTRA_PROV_FW_DEFAULT_51I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_53I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_55I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_57I" => __('X'),
"AASTRA_PROV_KEY_BLACKLIST" => __('X'),
"GRANDSTREAM_PROV_ENABLED" => __('Grandstream-Provisioning aktiv?'),
"GRANDSTREAM_PROV_HTTP_PASS" => __('X'),
"GRANDSTREAM_PROV_NTP" => __('X'),
"GRANDSTREAM_PROV_KEY_BLACKLIST" => __('X'),
"CANONIZE_OUTBOUND" => __('X'),
"CANONIZE_INTL_PREFIX" => __('X'),
"CANONIZE_COUNTRY_CODE" => __('X'),
"CANONIZE_NATL_PREFIX" => __('X'),
"CANONIZE_NATL_PREFIX_INTL" => __('X'),
"CANONIZE_AREA_CODE" => __('X'),
"CANONIZE_LOCAL_BRANCH" => __('X'),
"CANONIZE_SPECIAL" => __('X'),
"CANONIZE_CBC_PREFIX" => __('X'),
"DP_SUBSYSTEM" => __('X'),
"DP_EMERGENCY_POLICE" => __('X'),
"DP_EMERGENCY_POLICE_MAP" => __('X'),
"DP_EMERGENCY_FIRE" => __('X'),
"DP_EMERGENCY_FIRE_MAP" => __('X'),
"DP_DIALTIMEOUT_IN" => __('X'),
"DP_PRV_CALL_PREFIX" => __('X'),
"DP_FORWARD_REQ_EXT_NUM" => __('X'),
"DP_ALLOW_DIRECT_DIAL" => __('X'),
"DP_CONNID" => __('X'),
"PB_IMPORTED_ENABLED" => __('X'),
"PB_IMPORTED_ORDER" => __('X'),
"PB_IMPORTED_TITLE" => __('X'),
"PB_INTERNAL_TITLE" => __('X'),
"PB_PRIVATE_TITLE" => __('X'),
"LOCK_DIR" => __('X'),
"CALL_INIT_FROM_NET" => __('X'),
"MONITOR_FROM_NET" => __('X'),
"LVM_USER_6_DIGIT_INT" => __('X'),
"LVM_CALL_INIT_USERS_500000" => __('X'),
"CC_TIMEOUT" => __('X'),
"INTL_LANG" => __('X'),
"INTL_USE_GETTEXT" => __('X'),
"INTL_ASTERISK_LANG" => __('X'),
"USERCOMMENT_OFFTIME" => __('X'),
"EMAIL_PATTERN_VALID" => __('X'),
"EMAIL_DELIVERY" => __('X'),
"FAX_ENABLED" => __('X'),
"FAX_TSI_PREFIX" => __('X'),
"FAX_PREFIX" => __('X'),
"FAX_TSI" => __('X'),
"FAX_HYLAFAX_HOST" => __('X'),
"FAX_HYLAFAX_PORT" => __('X'),
"FAX_HYLAFAX_ADMIN" => __('X'),
"FAX_HYLAFAX_PASS" => __('X'),
"FAX_HYLAFAX_PATH" => __('X'),
"BOI_ENABLED" => __('Branch-Office-Integration aktiv?'),
"BOI_API_DEFAULT" => __('X'),
"BOI_BRANCH_NETMASK" => __('X'),
"BOI_BRANCH_PBX" => __('X'),
"BOI_NOBODY_EXTEN_PATTERN" => __('X'),
"BOI_GUI_REVERSE_PROXY" => __('X'),
"BOI_GUI_HOME_USER" => __('X'),
"BOI_GUI_HOME_ADMIN" => __('X'),
"LOG_TO" => __('X'),
"LOG_FILE" => __('X'),
"LOG_GMT" => __('X'),
"LOG_SYSLOG_FACILITY" => __('X'),
"LOG_LEVEL" => __('X')
);

$option_long_descr = array (
"DB_SIP_REG_UPDATE" => __('Asterisk will update the fields on the *slave* database connection, so if you use this make sure the slave is in fact not a slave but a node of a MySQL cluster! It\'s safe to use this with INSTALLATION_TYPE = "single".'),
"LDAP_HOST" => __('X'),
"LDAP_SSL" => __('X'),
"LDAP_PORT" => __('X'),
"LDAP_BINDDN" => __('i.e. the rootdn'),
"LDAP_PWD" => __('X'),
"LDAP_PROTOCOL" => __('X'),
"LDAP_SEARCHBASE" => __('e.g. "ou=People,dc=example,dc=com" | "ou=users,o=Company,c=de"'),
"LDAP_PROP_USER" => __('the user name in the LDAP attribute LDAP_PROP_USER must match the user name/code you use in Gemeinschaft e.g. "uid" | "employeenumber"'),
"LDAP_PROP_UID" => __('LDAP_PROP_UID is the "primary key" in the "dn", normally "uid" for users (or "cn").'),
"LDAP_PROP_FIRSTNAME" => __('e.g. "givenname"'),
"LDAP_PROP_LASTNAME" => __('e.g. "sn"'),
"LDAP_PROP_PHONE" => __('e.g. "telephonenumber"'),
"LDAP_PROP_EMAIL" => __('e.g. "mail"'),
"GUI_AUTH_METHOD" => __('"gemeinschaft": Authenticate users against our internal database. "webseal"     : Trust the non-standard "IV-User" HTTP header. Make sure every access goes through WebSeal and nobody can access our GUI directly!'),
"GUI_NUM_RESULTS" => __('X'),
"GUI_SUDO_ADMINS" => __('comma separated list of admin users who can manage *all* accounts'),
"GUI_SUDO_EXTENDED" => __('Whether to include htdocs/gui/inc/permissions.php and consult gui_sudo_allowed() to find out if a user can act as a certain other user. (You may need to adjust this function!). For the method used see GUI_PERMISSIONS_METHOD.'),
"GUI_PERMISSIONS_METHOD" => __('determines the method used to find out if a user can act as a certain other user. "gemeinschaft" or "lvm". ugly solution. see GUI_SUDO_EXTENDED, GUI_MON_PEERS_ENABLED and htdocs/gui/inc/permissions.php. deprecated.'),
"GUI_USER_MAP_METHOD" => __('determines the method used to map legacy usernames to usernames in Gemeinschaft. "" or "lvm". something like the "lvm" method (see gs_legacy_user_map() in htdocs/gui/inc/session.php) can be handy if GUI_AUTH_METHOD is "webseal".'),
"GUI_QUEUE_SHOW_NUM_CALLS" => __('show number of completed calls for each member in Monitor->Queues'),
"GUI_QUEUE_INFO_FROM_DB" => __('get queue statistics for Monitor->Queues from database (table queue_log)? otherwise the stats are taken from the manager interface. does not make sense if you don\'t set up a cron job for /opt/gemeinschaft/sbin/gs-queuelog-to-db (every minute)'),
"GUI_MON_NOQUEUEBLUE" => __('used in Monitor->Peers. if true idle users who are not member of a queue get a blue led instead of a green one'),
"GUI_MON_PEERS_ENABLED" => __('Whether to enable the peers monitor. The visible peers for each user depend on the GUI_PERMISSIONS_METHOD setting. For GUI_PERMISSIONS_METHOD=="lvm" an LDAP with Kostenstelle is required, see gui_monitor_which_peers() in htdocs/gui/inc/permissions.php'),
"GUI_SHUTDOWN_ENABLED" => __('enable shutdown via web interface?'),
"GUI_LANGS" => __('X'),
"GUI_ADDITIONAL_STYLESHEET" => __('X'),
"EXTERNAL_NUMBERS_BACKEND" => __('"db"|"ldap"'),
"EXTERNAL_NUMBERS_LDAP_PROP" => __('e.g. "externaltelephone"'),
"NOBODY_EXTEN_PATTERN" => __('The only wildcard is "x" which can only occur at the end of the pattern - once or multiple times. Take care that there is enough room for all of your phones! E.g. "95xxxx" can hold a maximum of 9999 phones. It it strongly recommended not to change this value! Call scripts/gs_nobodies_change if you ever change this!'),
"NOBODY_CID_NAME" => __('The CallerID name prefix. Call scripts/gs_nobodies_change if you ever change this!'),
"PROV_HOST" => __('X'),
"PROV_PORT" => __('X'),
"PROV_SCHEME" => __('X'),
"PROV_PATH" => __('with starting and trailing "/" URL is build like this: <PROV_SCHEME>://<PROV_HOST>:<PROV_PORT><PROV_PATH>snom/dial-log.php'),
"PROV_AUTO_ADD_PHONE" => __('X'),
"PROV_AUTO_ADD_PHONE_HOST" => __('X'),
"PROV_DIAL_LOG_LIFE" => __('X'),
"PROV_PROXIES_TRUST" => __('X'),
"PROV_PROXIES_XFF_HEADER" => __('X'),
"PROV_ALLOW_NET" => __('X'),
"PROV_LAN_NETS" => __('X'),
"PROV_MODELS_ENABLED_SNOM" => __('X'),
"PROV_MODELS_ENABLED_SIEMENS" => __('X'),
"PROV_MODELS_ENABLED_AASTRA" => __('X'),
"PROV_MODELS_ENABLED_GRANDSTREAM" => __('X'),
"SNOM_PROV_ENABLED" => __('X'),
"SNOM_PROV_HTTP_USER" => __('X'),
"SNOM_PROV_HTTP_PASS" => __('X'),
"SNOM_PROV_PB_NUM_RESULTS" => __('X'),
"SNOM_PROV_FW_UPDATE" => __('X'),
"SNOM_PROV_FW_6TO7" => __('X'),
"SNOM_PROV_FW_DEFAULT_300" => __('X'),
"SNOM_PROV_FW_DEFAULT_320" => __('X'),
"SNOM_PROV_FW_DEFAULT_360" => __('X'),
"SNOM_PROV_FW_DEFAULT_370" => __('X'),
"SNOM_PROV_KEY_BLACKLIST" => __('X'),
"SNOM_PROV_M3_ACCOUNTS" => __('X'),
"SIEMENS_PROV_ENABLED" => __('X'),
"SIEMENS_PROV_PREFER_HTTP" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS20" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS40" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS60" => __('X'),
"SIEMENS_PROV_FW_DEFAULT_OS80" => __('X'),
"SIEMENS_PROV_KEY_BLACKLIST" => __('X'),
"AASTRA_PROV_ENABLED" => __('X'),
"AASTRA_PROV_PB_NUM_RESULTS" => __('X'),
"AASTRA_PROV_FW_DEFAULT_51I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_53I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_55I" => __('X'),
"AASTRA_PROV_FW_DEFAULT_57I" => __('X'),
"AASTRA_PROV_KEY_BLACKLIST" => __('X'),
"GRANDSTREAM_PROV_ENABLED" => __('X'),
"GRANDSTREAM_PROV_HTTP_PASS" => __('X'),
"GRANDSTREAM_PROV_NTP" => __('X'),
"GRANDSTREAM_PROV_KEY_BLACKLIST" => __('X'),
"CANONIZE_OUTBOUND" => __('X'),
"CANONIZE_INTL_PREFIX" => __('X'),
"CANONIZE_COUNTRY_CODE" => __('X'),
"CANONIZE_NATL_PREFIX" => __('X'),
"CANONIZE_NATL_PREFIX_INTL" => __('X'),
"CANONIZE_AREA_CODE" => __('X'),
"CANONIZE_LOCAL_BRANCH" => __('X'),
"CANONIZE_SPECIAL" => __('X'),
"CANONIZE_CBC_PREFIX" => __('X'),
"DP_SUBSYSTEM" => __('X'),
"DP_EMERGENCY_POLICE" => __('X'),
"DP_EMERGENCY_POLICE_MAP" => __('X'),
"DP_EMERGENCY_FIRE" => __('X'),
"DP_EMERGENCY_FIRE_MAP" => __('X'),
"DP_DIALTIMEOUT_IN" => __('X'),
"DP_PRV_CALL_PREFIX" => __('X'),
"DP_FORWARD_REQ_EXT_NUM" => __('X'),
"DP_ALLOW_DIRECT_DIAL" => __('X'),
"DP_CONNID" => __('X'),
"PB_IMPORTED_ENABLED" => __('X'),
"PB_IMPORTED_ORDER" => __('X'),
"PB_IMPORTED_TITLE" => __('X'),
"PB_INTERNAL_TITLE" => __('X'),
"PB_PRIVATE_TITLE" => __('X'),
"LOCK_DIR" => __('X'),
"CALL_INIT_FROM_NET" => __('X'),
"MONITOR_FROM_NET" => __('X'),
"LVM_USER_6_DIGIT_INT" => __('X'),
"LVM_CALL_INIT_USERS_500000" => __('X'),
"CC_TIMEOUT" => __('X'),
"INTL_LANG" => __('X'),
"INTL_USE_GETTEXT" => __('X'),
"INTL_ASTERISK_LANG" => __('X'),
"USERCOMMENT_OFFTIME" => __('X'),
"EMAIL_PATTERN_VALID" => __('X'),
"EMAIL_DELIVERY" => __('X'),
"FAX_ENABLED" => __('X'),
"FAX_TSI_PREFIX" => __('X'),
"FAX_PREFIX" => __('X'),
"FAX_TSI" => __('X'),
"FAX_HYLAFAX_HOST" => __('X'),
"FAX_HYLAFAX_PORT" => __('X'),
"FAX_HYLAFAX_ADMIN" => __('X'),
"FAX_HYLAFAX_PASS" => __('X'),
"FAX_HYLAFAX_PATH" => __('X'),
"BOI_ENABLED" => __('X'),
"BOI_API_DEFAULT" => __('X'),
"BOI_BRANCH_NETMASK" => __('X'),
"BOI_BRANCH_PBX" => __('X'),
"BOI_NOBODY_EXTEN_PATTERN" => __('X'),
"BOI_GUI_REVERSE_PROXY" => __('X'),
"BOI_GUI_HOME_USER" => __('X'),
"BOI_GUI_HOME_ADMIN" => __('X'),
"LOG_TO" => __('X'),
"LOG_FILE" => __('X'),
"LOG_GMT" => __('X'),
"LOG_SYSLOG_FACILITY" => __('X'),
"LOG_LEVEL" => __('X')
);




require_once( GS_DIR .'inc/get-listen-to-ids.php' );
include_once( GS_DIR .'inc/gs-fns/gs_hosts_get.php' );


/***********************************************************
*    reloads all active asterisks
*    $host_ids=false for all
***********************************************************/

function gs_generate_autoconf_php_hosts()
{
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# get hosts
	#
	$hosts = @ gs_hosts_get();
	if (isGsError( $hosts ))
		return new GsError( $hosts->getMsg() );
	if (! is_array( $hosts ))
		return new GsError( 'Failed to get hosts.' );
	
	$GS_INSTALLATION_TYPE_SINGLE = gs_get_conf('GS_INSTALLATION_TYPE_SINGLE');
	if (! $GS_INSTALLATION_TYPE_SINGLE) {
		# get our host IDs
		#
		$our_host_ids = @ gs_get_listen_to_ids();
		if (isGsError( $our_host_ids ))
			return new GsError( $our_host_ids->getMsg() );
		if (! is_array( $our_host_ids ))
			return new GsError( 'Failed to get our host IDs.' );
	}
	
	# are we root? do we have to sudo?
	#
	$uid = @ posix_geteuid();
	$uinfo = @ posix_getPwUid($uid);
	$uname = @ $uinfo['name'];
	$sudo = ($uname=='root') ? '' : 'sudo ';
	
	$ok = true;
	$errorhosts = "";

	foreach ($hosts as $host) {
		$cmd = '/opt/gemeinschaft/scripts/gs-gen-config';
		if (! $GS_INSTALLATION_TYPE_SINGLE
		&&  ! in_array($host['id'], $our_host_ids)) {
			# this is not the local node
			$cmd = $sudo .'ssh -o StrictHostKeyChecking=no -o BatchMode=yes -l root '. qsa($host['host']) .' '. qsa($cmd);
		}
		@ exec( $sudo . $cmd .' 1>>/dev/null 2>>/dev/null', $out, $err );
		$ok = $ok && ($err==0);
		$errorhosts .= $host['host'].",";
	}
	if (! $ok)
		return new GsError( 'Failed to generate config on Hosts: '.$errorhosts );
	return true;
}









/***********************************************************
*    function to generate inc/gs_autoconf.php from Database
***********************************************************/
#warning: need to be root to run this, or we need write options to GS_DIR."inc/gs_autoconf.php
function gs_generate_autoconf_php() {
	
	$filename = GS_DIR ."inc/gs_autoconf.php";
	$fh = fopen( $filename , "w");
	if(!$fh)
		return new GsError('Can\'t open "'.GS_DIR."inc/gs_autoconf.php".' for writing');

	$DB = gs_db_master_connect();

	if(!$DB)
		return new GsError('DB Error.');

	fprintf($fh, "<?php\n");
	fprintf($fh, "//auto-generated by Gemeinschaft - do not edit by hand. Please use the GUI or the scripts for configuration.\n\n");

	$config= $DB->execute('SELECT `ident`,`type`,`value`,`default` FROM `config_options`');
	if (@$config) {
		while ($r = @$config->fetchRow()) {
			$value="";
			if($r['value'] == "") 
				$value = $r['default'];
			else
				$value = $r['value'];

			if ($r['type'] == "BOOL")
				if ( $value )
					fprintf($fh, "$%s = 1;\n",  $r['ident']);
				else
					fprintf($fh, "$%s = 0;\n",  $r['ident']);
			else
				fprintf($fh, "$%s = %s;\n",  $r['ident'], qsa($value));
			//hack for creating the option_short_descr - array :-)
			//fprintf($fh, "\"%s\" => __('X'),\n",  $r['ident']);
		}

	}
	fprintf($fh, "?>\n");
	fclose($fh);
	return true;
}


?>
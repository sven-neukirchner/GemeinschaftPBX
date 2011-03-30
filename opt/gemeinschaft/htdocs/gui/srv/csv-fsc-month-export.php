<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5603 $
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

define( 'GS_VALID', true );  /// this is a parent file
require_once( dirName(__FILE__) .'/../../../inc/conf.php' );
require_once( GS_DIR .'htdocs/gui/inc/session.php' );
require_once( GS_DIR .'inc/log.php' );

$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}


function _not_allowed( $errmsg='' )
{
	@header( 'HTTP/1.0 403 Forbidden', true, 403 );
	@header( 'Status: 403 Forbidden' , true, 403 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not authorized.');
	exit(1);
}

function _server_error( $errmsg='' )
{
	@header( 'HTTP/1.0 500 Internal Server Error', true, 500 );
	@header( 'Status: 500 Internal Server Error' , true, 500 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Internal Server Error.');
	exit(1);
}

function _not_found( $errmsg='' )
{
	@header( 'HTTP/1.0 404 Not Found', true, 404 );
	@header( 'Status: 404 Not Found' , true, 404 );
	@header( 'Content-Type: text/plain' );
	echo ($errmsg ? $errmsg : 'Not found.');
	exit(1);
}

function _not_modified( $etag='', $attach=false, $fake_filename='' )
{
	header( 'HTTP/1.0 304 Not Modified', true, 304 );
	header( 'Status: 304 Not Modified', true, 304 );
	if (! empty($etag))
		header( 'ETag: '. $etag );
	if (! empty($fake_filename))
		header( 'Content-Disposition: '.($attach ? 'attachment':'inline').'; filename="'.$fake_filename.'"' );
	exit(0);
}



if (! is_array($_SESSION)
||  ! @array_key_exists('sudo_user', @$_SESSION)
||  ! @array_key_exists('info'     , @$_SESSION['sudo_user'])
||  ! @array_key_exists('id'       , @$_SESSION['sudo_user']['info']) )
{
	_not_allowed();
}

$user_id = (int)@$_SESSION['sudo_user']['info']['id'];

$day_t_start  = (int)@$_REQUEST['d_start'];
$day_t_end    = (int)@$_REQUEST['d_end'];
$sql_time    = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')'; 
 
$waittime_level = 15;  # 15 s   

#get fsc groups 
#
$sqlquery=
	'SELECT	
		`id`,
		`title`,
		`company`,
		`location`,
		`country`,
		`language`,
		`project`,
		`svl_time`,
		`svl_percent`

		FROM `_fsc_groups` ORDER BY `order`' ;



$rs = $DB->execute($sqlquery);

header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=fsc-statistik.csv");

//echo __('Datum').';'.__('Firma').';'.__('Ort').';'.__('Land').';'.__('Sprache').';'.__('Projekt').';'.__('SVL Zeit').';'.__('SVL %').';'.__('Anrufer').';'.__('Angenommen').';'.__('im SVL').';'.__('Wartez').';'.__('Sprechz.'). "\r\n";

while ($r = $rs->fetchRow()) {

	$sqlquery='SELECT `_queue_id` FROM `_fsc_groups_member` WHERE _group_id=' .$r['id'];
	$rs_queues = $DB->execute($sqlquery); 

	$queue_ids = array();    
	while ($q_id =  $rs_queues->fetchRow()) {
		$queue_ids[] = $q_id['_queue_id'];
	}
	                               	

	//print_r($sql_time);


	$num_entered    = 0;  # inbound calls 
	$num_connected  = 0;  # connected to an agent
	$calls_in_svl   = 0;  # calls in SVL  
	$sum_waittime   = 0;  # sum waittime  
	$sum_calldur    = 0;  # sum calltime
	$svl			= 0;  # Servicelevel

	# abandoned and not count because waittime < 15s
	#
	$num_abandoned = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		 AND `event`=\'_EXIT\'
		 AND `reason`=\'ABANDON\'
		 AND `waittime`<='. (int)$waittime_level .' 
		 AND '. $sql_time
	);

	# inbound calls
	# 
	$num_entered = (int)@$CDR_DB->executeGetOne(
 	 	'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log`
 		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
 		 AND `event`=\'_ENTER\'
 		 AND '. $sql_time 
	 );
	 $num_count = $num_entered-$num_abandoned;

	# connected to an agent
	#
	$num_connected = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		 AND `event`=\'_CONNECT\'
		 AND '. $sql_time
	);
	

	# calls in SVL
	#
	$calls_in_svl = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log`
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		AND `event`=\'_COMPLETE\'
		AND `waittime` IS NOT NULL
		AND `waittime`<='. $r['svl_time'] .'
		AND '. $sql_time
	);

	# not counted waittime  because call was abandoned and waittime < 15s  
	#
	$sum_wait_fail = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`waittime`) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		 AND `event`=\'_EXIT\'
		 AND `reason`=\'ABANDON\'
		 AND `waittime`<='. (int)$waittime_level .' 
		 AND '. $sql_time
	);
	
	# sum waittime  
	#
	$sum_waittime = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`waittime`) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		AND `event` IN (\'_COMPLETE\', \'_EXIT\')
		AND `waittime` IS NOT NULL
		AND '. $sql_time
	);
	$count_waittime = $sum_waittime-$sum_wait_fail;   

	# $sum calltime
	#
	$sum_calldur = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`calldur`) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		AND `event`=\'_COMPLETE\'
		AND `reason`<>\'INCOMPAT\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);



	echo date('m',$day_t_start)  .'.'. date('Y',$day_t_start) .";"
	. $r['company'] .";"
	. $r['location'] .";"
	. $r['country'] .";"
	. $r['language'] .";"
	. $r['project'] . ";"
	. $r['svl_time'] .";"
	. $r['svl_percent'] .";"  
	. $num_count .";"    
	. $num_connected .";"    
	. $calls_in_svl .";"    
	. $count_waittime .";"    
	. $sum_calldur .    
	 "\r\n";   

}


?>

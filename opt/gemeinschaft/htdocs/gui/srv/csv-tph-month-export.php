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

####################################################


$m 	 		= (int)@$_REQUEST['month'];
$y  		= (int)@$_REQUEST['year'];
$num_days 	= (int)@$_REQUEST['num_days'];  

//$sql_time    = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')'; 

$waittime_level = 4;  # 4 s
$waittime_level_cut   = 20;
$calldur_level_cut = 15;
$svl_intervall = 30;	// calls within 30 Sec



header("Content-Type: text/plain");
header("Content-Disposition: attachment; filename=tph-statistik_month.csv");


for ($i=1; $i<=$num_days; ++$i) {    


 $day_t_start = (int)mkTime(  0, 0, 0 , $m,$i,$y );
 $day_t_end   = (int)mkTime( 23,59,59 , $m,$i,$y ); 
 $mdisplay  = date('m', $day_t_start);                       	 // month of selected  month  for report
 $ddisplay  = date('d', $day_t_start);                       	 // day of selected  month  for report 
 $sql_time    = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')';

$sqlquery='SELECT `_member_id` FROM `_stat_group_members` WHERE _group_id=1' ;
$rs = $DB->execute($sqlquery);

while ($r = $rs->fetchRow()) {
	
	$sqlquery='SELECT `_title` FROM `ast_queues` WHERE _id=' .$r['_member_id'];
	$rs_queues = $DB->execute($sqlquery); 

	//print_r($sql_time);


	$num_entered    = 0;  # inbound calls
	$num_connected  = 0;  # connected to an agent
	$num_abandoned	= 0;
	$calls_in_svl   = 0;  # calls in SVL
	$sum_waittime   = 0;  # sum waittime
	$sum_calldur    = 0;  # sum calltime
	$sum_review     = 0;
	$svl			= 0;  # Servicelevel
	$avg_waittime	= 0;
	$avg_calldur	= 0;
	$avg_review     = 0;
	$num_abandoned_lt = 0;
	$num_abandoned_gt = 0;
	$num_connected_lt	= 0;
	$num_connected_gt	= 0;
	$num_count_entered  = 0;
	$count_calldur_lt   = 0;
	$count_calldur_gt   = 0;
	$calls_in_svl_percent = 0;
	settype($calls_in_svl_percent,"double");

	# Queue Name
	# 
	$queue_name = @$CDR_DB->executeGetOne('SELECT `_title` FROM `ast_queues` WHERE _id=' .$r['_member_id']);



	# inbound calls
	#
	$num_entered = (int)@$CDR_DB->executeGetOne(
 	 	'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log`
 		 WHERE `queue_id`=' .$r['_member_id'].'
 		 AND `event`=\'_ENTER\'
 		 AND '. $sql_time 
	 );


	# abandoned and not count because waittime < 4s
	#
	$num_abandoned_lt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_EXIT\'
		 AND `reason`=\'ABANDON\'
		 AND `waittime`<='. (int)$waittime_level .'
		 AND '. $sql_time
	);
	$num_count_entered = $num_entered-$num_abandoned_lt;

	# connected to an agent an waittime > 20 sec
	#
	$num_connected_gt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		  AND `waittime`>'. (int)$waittime_level_cut .'
		 AND '. $sql_time
	);


	# connected to an agent an waittime < 20 sec
	#
	$num_connected_lt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		  AND `waittime`<='. (int)$waittime_level_cut .'
		 AND '. $sql_time
	);


	# calls in SVL
	#
	# all connected calls	
	$num_connected = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		 AND '. $sql_time
	);

	$calls_in_svl = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
	 	WHERE `queue_id`=' .$r['_member_id'].'
	 	AND `event`=\'_CONNECT\'
		 AND `waittime`<='. (int)$svl_intervall .'
		 AND '. $sql_time
	);
	
	
	if ($num_connected !=0) $calls_in_svl_percent = $calls_in_svl*100/$num_connected;
	

	# $sum calltime
	#
	$sum_calldur = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`calldur`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'  
		AND `event`=\'_COMPLETE\'
		AND `reason`<>\'INCOMPAT\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);


	# not counted waittime  because call was abandoned and waittime < 4s
	#
	$sum_wait_fail = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`waittime`) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_EXIT\'
		 AND `reason`=\'ABANDON\'
		 AND `waittime`<='. (int)$waittime_level .'
		 AND '. $sql_time
	);


	# sum waittime  
	#
	$sum_waittime = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`waittime`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event` IN (\'_COMPLETE\', \'_EXIT\')
		AND `waittime` IS NOT NULL
		AND '. $sql_time
	);


	$count_waittime = $sum_waittime-$sum_wait_fail;
	
		
	# $sum reviewtime
	#
	$sum_review = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`calldur`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_REVIEW_END\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);


	# $avg calltime
	#
	$avg_calldur = (int)@$CDR_DB->executeGetOne(
		'SELECT AVG(`calldur`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_COMPLETE\'
		AND `reason`<>\'INCOMPAT\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);
	


	# avg waittime
	#
	# calls - call which are abandoned within first 4 Sec.
	if ($num_count_entered !=0) $avg_waittime = $count_waittime / $num_count_entered;


# $avg reviewtime
	#
	$avg_review = (int)@$CDR_DB->executeGetOne(
		'SELECT AVG(`calldur`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_REVIEW_END\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);
	

	# $sum calltime       <= 15s
	#
	$count_calldur_lt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_COMPLETE\'
		AND `reason`<>\'INCOMPAT\'
		AND `calldur`<='. (int)$calldur_level_cut .'
		AND '. $sql_time
	);


	# $sum calltime       > 15s
	#
	$count_calldur_gt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_COMPLETE\'
		AND `reason`<>\'INCOMPAT\'
		AND `calldur`>'. (int)$calldur_level_cut .'
		AND '. $sql_time
	);


	#################################
	
	echo $queue_name .";"
	. date('d',$day_t_start) .'.'. date('m',$day_t_start)  .'.'. date('Y',$day_t_start) .";"
	. $num_count_entered .";"
	. $num_abandoned_lt .";"
	. $num_connected_gt .";"
	. $num_connected_lt .";"
	. round($calls_in_svl_percent, 0) .";"
	. $sum_calldur .";" 
	. $count_waittime . ";"
    . $sum_review .";" 
	. $avg_calldur  .";"
	. round($avg_waittime).";"
	. $avg_review  .";"
	. $count_calldur_lt .";"
	. $count_calldur_gt .
	 "\r\n";

}
}
?>

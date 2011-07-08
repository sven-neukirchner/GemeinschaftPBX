<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 6042 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
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

echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH ,'js/tooltips.js"></script>' ,"\n";

$CDR_DB = gs_db_cdr_master_connect();
if (! $CDR_DB) {
	echo 'CDR DB error.';
	return;
}

$duration_level  = 90;  # 90 s = 1:30 min
$waittime_level = 4;  # 4 s
$waittime_level_cut   = 20;
$calldur_level_cut = 15;
$svl_intervall = 30;	// calls within 30 Sec



function _secs_to_minsecs( $s )
{
	$s = (int)$s;
	$m = floor($s/60);
	$s = $s - $m*60;
	return $m .':'. str_pad($s, 2, '0', STR_PAD_LEFT);
}

$sudo_url = (@$_SESSION['sudo_user']['name'] == @$_SESSION['real_user']['name'])
	? '' : ('&amp;sudo='. @$_SESSION['sudo_user']['name']);




$action = @$_REQUEST['action'];
if ($action == 'report') {
	//$queue_id = (int)@$_REQUEST['queue_id'];
	$month_d  = (int)@$_REQUEST['month'   ];
	$day_d    = (int)@$_REQUEST['day'   ];
} else {
	$action   = '';
	//$queue_id =  0;
	//$month_d  = -1; 		# previous month
	$month_d  = 0;  		# current month
	$day_d    = date("j");  # current day  
}

?>


<form method="get" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="action" value="report" />


<label for="ipt-month"><?php echo __('Monat'); ?>:</label>
<select name="month" id="ipt-month">
<?php
$t = time(); 
for ($i=-3; $i<=0; ++$i) {
	echo '<option value="',$i,'"', ($i==$month_d ? ' selected="selected"' : ''),'>', date('m / Y', (int)strToTime("$i months", $t)) ,'&nbsp;</option>' ,"\n";
}
?>
</select>

<?
  $t         = (int)strToTime("$month_d months", $t);     // month now or what selected
  $num_days  = (int)date('t', $t);                      	// num_days of selected month 
  $y         = (int)date('Y', $t);                        // year of selected month 
  $m         = (int)date('n', $t);                        // month of selected  month

  $day_t_start = (int)mkTime(  0, 0, 0 , $m,$day_d,$y );
  $day_t_end   = (int)mkTime( 23,59,59 , $m,$day_d,$y );
  $mdisplay  = date('m', $day_t_start);                       	 // month of selected  month  for report
  $ddisplay  = date('d', $day_t_start);                       	 // day of selected  month  for report 
  $sql_time    = '(`timestamp`>='.$day_t_start .' AND `timestamp`<='.$day_t_end .')';


?>

&nbsp;&nbsp;&nbsp; 

<label for="ipt-day"><?php echo __('Tag'); ?>:</label>
<select name="day" id="ipt-day">
<?php

for ($i=1; $i<=$num_days; ++$i) {
	echo '<option value="',$i,'"', ($i==$day_d ? ' selected="selected"' : ''),'>', $i ,'&nbsp;</option>' ,"\n";
}
?>
</select>



&nbsp;&nbsp;&nbsp;

<input type="submit" value="<?php echo __('Report'); ?>" />
</form>

<hr />

<?php

if ($action == '') return;

#####################################################################


?>

<?php /*
<div id="chart" style="position:absolute; left:189px; right:12px; top:14em; bottom:10px; overflow:scroll; border:1px solid #ccc; background:#fff;">
*/ ?>


<script type="text/javascript">
function chart_fullscreen_toggle()
{
	var chart = document.getElementById('chart');
	var toggle = document.getElementById('chart-fullscreen-toggle');
	if (chart && toggle) {
		if (chart.style.position == 'absolute') {
			chart.style.position = 'static';
			chart.style.top        = '';
			chart.style.left       = '';
			chart.style.right      = '';
			chart.style.bottom     = '';
			chart.style.background = 'transparent';
			chart.style.padding    = '0';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png';
		} else {
			chart.style.position = 'absolute';
			chart.style.top        = '0';
			chart.style.left       = '0';
			chart.style.right      = '0';
			chart.style.bottom     = '0';
			chart.style.background = '#fff';
			chart.style.padding    = '0.4em 0.8em 0.7em 0.8em';
			toggle.src = '<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_nofullscreen.png';
		}
	}
}
</script>



<div id="chart" style="border:0px #000 solid;">
<img id="chart-fullscreen-toggle" class="fr" style="cursor:pointer; margin:0 1px 1px 0;" title="Fullscreen" alt="Fullscreen" onclick="chart_fullscreen_toggle();" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/window_fullscreen.png" />
<small>(<?php echo __('Zeiger &uuml;ber Spalten&uuml;berschriften bewegen f&uuml;r Beschreibung'); ?>)</small>
<br style="clear:right;" />


<table cellspacing="1" class="phonebook" style="border:1px solid #ccc; background:#fff;">
<thead>
<tr>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('<br>Warteschlange'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('<br>Datum'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe<br>gesamt'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit<br>Absprung<br><= 4 Sek.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit<br>Absprung<br>> 4 Sek.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit<br>Wartezeit<br>> 20 Sek.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit<br>Wartezeit<br><= 20 Sek.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('<br>SVL'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('&sum;<br>Sprechzeit'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('&sum;<br>Wartezeit'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('&sum;<br>Nacharbeit'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('&#216;<br>Sprechzeit.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo  __('&#216;<br>Wartezeit'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('&#216;<br>Nacharbeit'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit<br>Sprechzeit<br><= 15 Sek.'); ?></th>
	<th style="font-weight:normal; vertical-align:top;"><?php echo __('Anrufe mit <br>Sprechzeit<br>> 15 Sek.'); ?></th>

</tr>
</thead>
<tbody>

<?php
/*
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

//echo "<pre>";
//print_r($sqlquery);
//echo "</pre>";


$totals = array(
	'num_abandoned'   => 0,
	'sum_wait_fail' => 0,
);

*/


$sqlquery='SELECT `_member_id` FROM `_stat_group_members` WHERE _group_id=1' ;
$rs = $DB->execute($sqlquery);


//echo "<pre>";
//print_r($sqltime);
//echo "</pre>";


while ($r = $rs->fetchrow()) {
	
	# get queues of group
	#
	$sqlquery='SELECT `_title` FROM `ast_queues` WHERE _id=' .$r['_member_id'];
	$rs_queues = $DB->execute($sqlquery); 

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
	$num_count_entered =0;
	$count_calldur_lt   = 0;
	$count_calldur_gt   = 0;
	$calls_in_svl_percent = 0;
	settype($calls_in_svl_percent,"double");


	echo '<tr>', "\n";
	
	$queue_name = @$CDR_DB->executeGetOne('SELECT `_title` FROM `ast_queues` WHERE _id=' .$r['_member_id']);
		
	echo '<td>', $queue_name ,'</td>', "\n";
	echo '<td>', $ddisplay ,'.', $mdisplay ,'.', $y ,'</td>', "\n";
	
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
	echo '<td>', $num_count_entered,'</td>', "\n";
	echo '<td>',  $num_abandoned_lt  ,'</td>', "\n";

	# abandoned > 4s
	#
	$num_abandoned_gt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_EXIT\'
		 AND `reason`=\'ABANDON\'
		 AND `waittime`>'. (int)$waittime_level .'
		 AND '. $sql_time
	);

	echo '<td>',  $num_abandoned_gt  ,'</td>', "\n";


	# connected to an agent an waittime > 20 sec
	#
		$num_connected_gt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		  AND `waittime`>'. (int)$waittime_level_cut .'
		 AND '. $sql_time
	);
	echo '<td>',  $num_connected_gt ,'</td>', "\n";


	# connected to an agent an waittime < 20 sec
	#
	$num_connected_lt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		  AND `waittime`<='. (int)$waittime_level_cut .'
		 AND '. $sql_time
	);
	echo '<td>',  $num_connected_lt ,'</td>', "\n";


	# connected to an agent an waittime > 20 sec
	#
		$num_connected_gt = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		 WHERE `queue_id`=' .$r['_member_id'].'
		 AND `event`=\'_CONNECT\'
		   AND `waittime`>'. (int)$waittime_level_cut .'
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
	//echo '<td>',  round($calls_in_svl_percent , 2) ,'&nbsp;<span style="color:#6a6a6a; float:right;">(', $calls_in_svl ,')</span></td>', "\n";
	echo '<td>',  round($calls_in_svl_percent , 0) ,'</td>', "\n";

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
	echo '<td>', $sum_calldur ,'</td>', "\n";


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
	echo '<td>', $count_waittime ,'</td>', "\n";
	
	
	# $sum reviewtime
	#
	$sum_review = (int)@$CDR_DB->executeGetOne(
		'SELECT SUM(`calldur`) FROM `queue_log`
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_REVIEW_END\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);
	echo '<td>', $sum_review ,'</td>', "\n";

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
	echo '<td>', $avg_calldur ,'</td>', "\n";


	# avg waittime
	#
	# calls - call which are abandoned within first 4 Sec.
	if ($num_count_entered !=0) $avg_waittime = $count_waittime / $num_count_entered;
	echo '<td>', round($avg_waittime) ,'</td>', "\n";  
	
		
	# $avg reviewtime
	#
	$avg_review = (int)@$CDR_DB->executeGetOne(
		'SELECT AVG(`calldur`) FROM `queue_log` 
		  WHERE `queue_id`=' .$r['_member_id'].'
		AND `event`=\'_REVIEW_END\'
		AND `calldur` IS NOT NULL
		AND '. $sql_time
	);
	echo '<td>', $avg_review ,'</td>', "\n";

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
	echo '<td>', $count_calldur_lt ,'</td>', "\n";
	
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
	echo '<td>', $count_calldur_gt ,'</td>', "\n";
	

	echo '</tr>', "\n";
}

?>

</tbody>
</table>
<br>
<?
//echo "<b>Nicht gewertete Anrufe:</b> ". $totals['num_abandoned'] ." <br>";
//echo "Die Wartezeit f&uuml;r Rufe die innerhalb der ersten 4 Sekunden abgebrochen wurden wird nicht summiert.";
echo "<small>(Anrufe, die innerhalb der ersten 4 Sekunden abgebrochen wurden, werden unter \"Anrufe gesamt\" und Wartezeit nicht ber&uuml;cksichtigt.)</small><br><br>";
echo '<a href="', GS_URL_PATH, 'srv/csv-tph-day-export.php?d_start='.$day_t_start.'&d_end='.$day_t_end.'" title="', __('CSV Export b '), '">'.__('CSV Export').'</a>';
?>


</div>

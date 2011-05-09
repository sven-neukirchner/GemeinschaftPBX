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
$waittime_level = 15;  # 15 s


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
  $mdisplay  = date('m', $t);                       	 // month of selected  month  for report
  $ddisplay  = date('d', $t);                       	 // day of selected  month  for report

  $day_t_start = (int)mkTime(  0, 0, 0 , $m,$day_d,$y );
  $day_t_end   = (int)mkTime( 23,59,59 , $m,$day_d,$y );
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

<script type="text/javascript">
//<![CDATA[
function mytip( evt, key )
{
	switch (key) {
		case 'queues':
			return tip(evt, '<?php echo __('Anzahl der Warteschlangen in der Gruppe'); ?>');
		case 'day':
			return tip(evt, '<?php echo __('Tag des gew&auml;hlten Monats'); ?>');
		case 'calls':
			return tip(evt, '<?php echo __('Firmenbezeichnung'); ?>');
		case 'answered':
			return tip(evt, '<?php echo __('Ort des Callcenters'); ?>');
		case 'abandoned':
			return tip(evt, '<?php echo __('Anruferland der Wartesachlange'); ?>');
		case 'timeout':
			return tip(evt, '<?php echo __('Anrufersprache der Warteschlange.'); ?>');
		case 'noag':
			return tip(evt, '<?php echo __('Projektzuordung der Warteschlange'); ?>');
		case 'full':
			return tip(evt, '<?php echo __('In welchem Zeitraum m&uuml;ssen Anrufe angenommen werden um im Servicelevel zu liegen'); ?>');
		case 'squota':
			return tip(evt, '<?php echo __('Wieviel % der Anrufe m&uuml;ssen innerhalb der SVL Zeit angenommen werden um den SVL zu erreichen'); ?>');
		case 'durl':
			return tip(evt, '<?php echo __('Anzahl der Anrufe auf dieser Warteschlange'); ?>');
		case 'durg':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe auf diese Warteschlange'); ?>');
		case 'duravg':
			return tip(evt, '<?php echo __('Anzahl der angenommenen Anrufe innerhalb der Servicelevel Zeit'); ?>');
		case 'holdlsl':
			return tip(evt, '<?php echo __('Summe der Wartezeit aller Anrufer in Sekunden'); ?>');
		case 'speak':
			return tip(evt, '<?php echo __('Summe der Sprechzeit aller Anrufer in Sekunden'); ?>');
	}
	return undefined;
}

</script>


<table cellspacing="1" class="phonebook" style="border:1px solid #ccc; background:#fff;">
<thead>
<tr>
	<th style="font-weight:normal;" onmouseover="mytip(event,'queues');"><?php echo __('&sum; WS'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'day');"><?php echo __('Datum'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'calls');"><?php echo __('Firma'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'answered');"><?php echo __('Ort'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'abandoned');"><?php echo __('Land'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'timeout');"><?php echo __('Sprache'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'noag');"><?php echo __('Projekt'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'full');"><?php echo __('SVL Zeit'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'squota');"><?php echo __('SVL %'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durl');"><?php echo __('Anrufer'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'durg');"><?php echo __('Angen.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'duravg');"><?php echo  __('im SVL'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'holdlsl');"><?php echo __('Wartez.'); ?></th>
	<th style="font-weight:normal;" onmouseover="mytip(event,'speak');"><?php echo __('Sprechz.'); ?></th>

</tr>
</thead>
<tbody>

<?php

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



while ($r = $rs->fetchrow()) {
	
	# get queues of group
	#
	$sqlquery='SELECT `_queue_id` FROM `_fsc_groups_member` WHERE _group_id=' .$r['id'];
	$rs_queues = $DB->execute($sqlquery); 

	$queue_ids = array();    
	while ($q_id =  $rs_queues->fetchRow()) {
		$queue_ids[] = $q_id['_queue_id'];
	}
	
	$number_queues=count($queue_ids);

	//echo "<pre>";
	//print_r($queue_ids);
	//echo "</pre>";

	$num_entered    = 0;  # inbound calls
	$num_connected  = 0;  # connected to an agent
	$calls_in_svl   = 0;  # calls in SVL
	$sum_waittime   = 0;  # sum waittime
	$sum_calldur    = 0;  # sum calltime
	$svl			= 0;  # Servicelevel


	echo '<tr>', "\n";
	echo '<td>', $number_queues ,'</td>', "\n";
	echo '<td>', $ddisplay ,'.', $mdisplay ,'.', $y ,'</td>', "\n";
	echo '<td>', $r['company'] ,'</td>', "\n";
	echo '<td>', $r['location'] ,'</td>', "\n";
	echo '<td>', $r['country'] ,'</td>', "\n";
	echo '<td>', $r['language'] ,'</td>', "\n";
	echo '<td>', $r['project'] ,'</td>', "\n";
	echo '<td>', $r['svl_time'] ,'</td>', "\n";
	echo '<td>', $r['svl_percent'] ,'</td>', "\n";

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
	$totals['num_abandoned']+=$num_abandoned;

	# inbound calls
	#
	$num_entered = (int)@$CDR_DB->executeGetOne(
 	 	'SELECT COUNT(DISTINCT(`ast_call_id`)) FROM `queue_log`
 		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
 		 AND `event`=\'_ENTER\'
 		 AND '. $sql_time 
	 );

	$num_count = $num_entered-$num_abandoned;
	//echo '<td>',  $num_count ,' (', $num_entered ,')</td>', "\n";
	echo '<td><span style="float:left;">', $num_count ,'</span>&nbsp;<span style="color:#6a6a6a; float:right;"> ('. $num_entered .')</span></td>', "\n";

	# connected to an agent
	#
	$num_connected = (int)@$CDR_DB->executeGetOne(
		'SELECT COUNT(*) FROM `queue_log` 
		  WHERE `queue_id` IN(\''.implode('\',\'',$queue_ids).'\')
		 AND `event`=\'_CONNECT\'
		 AND '. $sql_time
	);
	echo '<td>',  $num_connected ,'</td>', "\n";

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
	# $num_svl = $num_connected

	echo '<td>', $calls_in_svl ,'</td>', "\n";

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
	$totals['sum_wait_fail']+=$sum_wait_fail;

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
	echo '<td>', $count_waittime ,'</td>', "\n";
	//echo '<td><span style="float:left;">', $count_waittime ,'</span>&nbsp;<span style="color:#6a6a6a; float:right;"> ('. $sum_waittime .')</span></td>', "\n";

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
	echo '<td>', $sum_calldur ,'</td>', "\n";

	echo '</tr>', "\n";
}

?>

</tbody>
</table>
<br>
<?
echo "<b>Nicht gewertete Anrufe:</b> ". $totals['num_abandoned'] ." <br>";
echo "<b>Nicht gewertete Wartezeit:</b> ". $totals['sum_wait_fail'] ." Sekunden <br>";
echo "<small>(Anrufer hat beim Warten innerhalb der ersten ". $waittime_level ." Sekunden aufgelegt)</small><br><br>";
echo '<a href="', GS_URL_PATH, 'srv/csv-fsc-day-export.php?d_start='.$day_t_start.'&d_end='.$day_t_end.'" title="', __('CSV Export b '), '">'.__('CSV Export').'</a>';
?>


</div>

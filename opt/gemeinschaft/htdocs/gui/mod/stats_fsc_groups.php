<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision$
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* Soeren Sprenger <soeren.sprenger@amooma.de>
* Sascha Daniels <sd@alternative-solution.de>
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
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo '</h2>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";


$edit     		= (int)trim(@$_REQUEST['edit'    ]);
$save   		= (int)trim(@$_REQUEST['save'    ]);
$qstat_group    = (int)trim(@$_REQUEST['qstat_group'    ]);


$per_page = (int)gs_get_conf('GS_GUI_NUM_RESULTS');
if ($per_page < 1) $per_page = 1;
$page     = (int)@$_REQUEST['page'];



if ($save) {
	
	//$db = gs_db_master_connect();
	$is_in_group = 0;

	$sql_query = 'SELECT `_group_id` FROM `_fsc_groups_member` WHERE `_queue_id`='. $save;
	$is_in_group = $DB->executeGetOne( $sql_query );

	// delte queue from fsc-member if set to "-nicht zugeordnet-"
	if ($is_in_group > 0 && $qstat_group == 0) {
		$sql_query = 'DELETE FROM `_fsc_groups_member` WHERE `_queue_id` ='. $save;
	}

	
	// queue is new ín fsc group and not  "-nicht zugeordnet-"
	if ($is_in_group == 0 && $qstat_group != 0) {  //queue not in fscgroup
		$sql_query = 'INSERT INTO `_fsc_groups_member` (`_group_id`, `_queue_id`) VALUES ('.$qstat_group.', ' .$save. ')';
	}
	
	// queue was alrady in fsc group and is not set to  "-nicht zugeordnet-"
	if ($is_in_group > 0 && $qstat_group != 0){
		$sql_query = 'UPDATE `_fsc_groups_member` SET	`_group_id`='. $qstat_group .' WHERE `_queue_id`='. $save;
	}
	$rs = $DB->execute($sql_query);

	//echo "<pre>";	
	//print_r($rs);
	//echo "<pre>";
	
}

#####################################################################
#                             view list {
#####################################################################


	# get fsc groups
	#
	$sql_query = 'SELECT `id`, `title`, `description` FROM `_fsc_groups`	ORDER BY `title`';
	$rs = $DB->execute($sql_query);
	$fsc_groups = array();
	while ($r = $rs->fetchRow()) {
	$fsc_groups[$r['id']] = array(
		'title'			=> $r['title'],
		'description'	=> htmlEnt($r['description'])
	);
	$fsc_groups[0] = array(  
		'title'      => '- nicht zugeordnet -',
		'description' => '' 
	);
	}

	//echo "<pre>";	
	//print_r($fsc_groups);
	//echo "<pre>";


	# get queues
	#

	$sql_query =
	'SELECT SQL_CALC_FOUND_ROWS
		`q`.`_id`,  `q`.`name`, `q`.`_title`,  `g`.`_group_id`
	FROM `ast_queues` `q`
	LEFT JOIN `_fsc_groups_member` `g`
	ON `g`.`_queue_id` = `q`.`_id`
	ORDER BY `name`
	LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);

	// echo "<pre>";
	// print_r($_REQUEST);
	// echo "<pre>";

?>

<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<?php
if ($edit > 0) {
	echo '<input type="hidden" name="page" value="', htmlEnt($page), '" />', "\n";
	echo '<input type="hidden" name="save" value="', $edit , '" />', "\n";
}
?>

<table cellspacing="1">
<thead>
<tr>
	<th style="width:75px;"><?php echo __('Warteschlange'); ?> </th>
	<th style="width:200px;"><?php echo __('Bezeichnung'); ?></th>
	<th style="width:300px;"><?php echo __('FSC-Gruppe'); ?></th>
	<th style="width:50px;" class="r">
<?php
		echo '<nobr>', ($page+1) ,' / ', $num_pages ,'</nobr> &nbsp; ',"\n";
		echo '<nobr>';
		if ($page > 0) {
			echo
			'<a href="',  gs_url($SECTION, $MODULE, null, 'page='.($page-1)), '" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
			'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
			'</a>', "\n";
		} else {
			echo
			'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
		}
		if ($page < $num_pages-1) {
			echo
			'<a href="',  gs_url($SECTION, $MODULE, null, 'page='.($page+1)), '" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
			'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
			'</a>', "\n";
		} else {
			echo
			'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
		}
		echo '</nobr>';
?>
	</th>
</tr>
</thead>
<tbody>

<?php
	$i=0;
	while ($r = $rs->fetchRow()) {
			
		if (!$r['_group_id']) {
			$r['_group_id'] = 0;
			$fscgroup_name =  '<i>- nicht zugeordnet -</i>';
		} else{ 
			$fscgroup_name =  '<b>'. $fsc_groups[$r['_group_id']]['title'] .'</b>&nbsp;('. $fsc_groups[$r['_group_id']]['description'] .')';
		}

		if ($edit === $r['_id']) {


			echo '<tr class="', ((++$i%2) ? 'odd':'even'), '">', "\n";
			echo '<td>', htmlEnt($r['name']) ,'</td>',"\n";
			echo '<td>', htmlEnt($r['_title']) ,'</td>',"\n";

			echo '<td>';

			echo '<select name="qstat_group" style="width:250px">', "\n";
			foreach ($fsc_groups as $fsc_group_id => $gt) {
				echo '<option value="', $fsc_group_id, '"', ( $fsc_group_id == $r['_group_id'] ? ' selected="selected"' : ''), '>',  $gt['title'],'&nbsp;&nbsp;' ,$gt['description'], '</option>', "\n";  
			}
			echo '</select>', "\n";

			echo '</td>';

			echo '<td>'; 
				echo '<button type="submit" title="', __('Speichern'), '" class="plain">';
				echo '<img alt="', __('Speichern') ,'" src="', GS_URL_PATH,'crystal-svg/16/act/filesave.png" />';
				echo '</button>';
				echo '</td>',"\n"; 
		
		} else {

			echo '<tr class="', ((++$i%2) ? 'odd':'even'), '">', "\n";
			echo '<td>', htmlEnt($r['name']) ,'</td>',"\n";
			echo '<td>', htmlEnt($r['_title']) ,'</td>',"\n";
			echo '<td>', $fscgroup_name ,'</td>';
			echo '<td>';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['_id'] .'&amp;page='.$page) ,'" title="', __('bearbeiten'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp;';
			echo '</td>',"\n";
		}

		echo '</tr>',"\n";
	}
	
?>

</tbody>
</table>
</form>

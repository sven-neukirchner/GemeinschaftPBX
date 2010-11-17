<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5712 $
* 
* Copyright 2007, amooma GmbH, Bachstr. 126, 56566 Neuwied, Germany,
* http://www.amooma.de/
* Stefan Wintermeyer <stefan.wintermeyer@amooma.de>
* Philipp Kempgen <philipp.kempgen@amooma.de>
* Peter Kozak <peter.kozak@amooma.de>
* 
* Author: Sven Neukirchner <s.neukirchner@konabi.de>
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
include_once( GS_DIR .'inc/gs-fns/gs_specialrule_add.php' );
include_once( GS_DIR .'inc/gs-fns/gs_specialrule_del.php' );
include_once( GS_DIR .'inc/gs-fns/gs_specialrule_line_del.php' );
include_once( GS_DIR .'lib/utf8-normalize/gs_utf_normal.php' );

include_once( GS_DIR .'inc/pcre_check.php' );
require_once( GS_DIR .'inc/quote_shell_arg.php' );


echo '<h2>';
if (@$MODULES[$SECTION]['icon'])
	echo '<img alt=" " src="', GS_URL_PATH, str_replace('%s', '32', $MODULES[$SECTION]['icon']), '" /> ';
if (count( $MODULES[$SECTION]['sub'] ) > 1 )
	echo $MODULES[$SECTION]['title'], ' - ';
//echo $MODULES[$SECTION]['sub'][$MODULE]['title'];
echo __('Sonderregeln');
echo '</h2>', "\n";

echo '<script type="text/javascript" src="', GS_URL_PATH, 'js/arrnav.js"></script>', "\n";
echo '<script type="text/javascript">
//<![CDATA[
function confirm_delete() {
	return confirm(', utf8_json_quote(__("Wirklich l\xC3\xB6schen?")) ,');
}
//]]>
</script>' ,"\n";

$edit           = (int)trim(@$_REQUEST['edit'    ]);
$per_page       = (int)GS_GUI_NUM_RESULTS;
$page           = (int)@$_REQUEST['page'    ] ;
$title          =      trim(@$_REQUEST['title'   ]);
$description    =      trim(@$_REQUEST['description'   ]);    
$delete         = (int)trim(@$_REQUEST['delete'  ]);
$action         =      trim(@$_REQUEST['action'    ]);     
$line_id        =      trim(@$_REQUEST['line_id'    ]);    
$rule_id        =      trim(@$_REQUEST['rule_id'    ]);    
$id             = (int)@$_REQUEST['id'];        
$delete_line    =      trim(@$_REQUEST['delete_line'    ]);      



// delete a rule
if ($delete) {
	$ret = gs_specialrule_del( $delete );
	if (isGsError( $ret )) echo $ret->getMsg();
	$cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
	@exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null &' );
}

// add a rule 
if ($title) {
	$ret = gs_specialrule_add( $title, $description );
	if (isGsError( $ret )) echo $ret->getMsg();
}	

// delete a line from rule
if ($action == 'delete') {
	$ret = gs_specialrule_line_del( $line_id );
	if (isGsError( $ret )) echo $ret->getMsg();
	$edit = $rule_id;    
} 

// add a line to rule


#if ($line && $rule_id) {
#    $ord = (int)$DB->executeGetOne( 'SELECT MAX(`ord`) FROM `specialrules_lines`' ) + 1; 
#    $ret = gs_specialrule_line_add( $line, $rule_id, $ord );
#    if (isGsError( $ret )) echo $ret->getMsg();
#    $edit = $rule_id;       
#}     



#####################################################################
if ($action == 'move-up' || $action == 'move-down') {
    
    if ($line_id > 0) {
        gs_db_start_trans($DB);
        $rs = $DB->execute( 'SELECT `id` FROM `specialrules_lines` WHERE `rule_id`=' .$rule_id. ' ORDER BY `ord`' );
        $ord = 4;
          
        while ($r = $rs->fetchRow()) {
            if ($r['id'] != $line_id)
                $DB->execute( 'UPDATE `specialrules_lines` SET `ord`='. $ord .' WHERE `id`='. (int)$r['id'] );
            else
                $DB->execute( 'UPDATE `specialrules_lines` SET `ord`='. ($ord + ($action=='move-up' ? -3 : 3)) .' WHERE `id`='. (int)$r['id'] );
            $ord += 2;
        }
        gs_db_commit_trans($DB);
        
        @$DB->execute( 'OPTIMIZE TABLE `routes_in`' );
        @$DB->execute( 'ANALYZE TABLE `routes_in`' );
    }
    
    $edit = $rule_id;
}



#####################################################################
#                                {save 
#####################################################################

# save or update lines in a rule
if ($action == 'save') {
      
    $rs = $DB->execute( 'SELECT `id` FROM `specialrules_lines`  WHERE `rule_id`='. $rule_id .'' );
    $db_ids = array();
    while ($r = $rs->fetchRow())
        $db_ids[] = (string)(int)$r['id'];
    $db_ids[] = 0;  # add 0 for the new rule
     
    foreach ($db_ids as $dbid) {    
       
        $line = $DB->escape(trim(@$_REQUEST['line_'.$dbid.'']));      
 
        if ($dbid > 0 && $line != '') { 
           $query = "UPDATE `specialrules_lines` 
                     SET `rule_id` = '" .$rule_id. "',
                         `line`    = '" .$line. "' 
                     WHERE `id`= '" .$dbid. "' "; 

        }
        elseif ($dbid == 0 && $line != '')  {       
           
           $ord = (int)$DB->executeGetOne( 'SELECT MAX(`ord`) FROM `specialrules_lines`' ) + 1;    
           $query = "INSERT INTO `specialrules_lines` (`id`, `rule_id`, `ord`, `line` ) VALUES (NULL, '" .$rule_id. "' , '" .$ord. "',  '" .$line. "')";     
                                                                                                                           
       }
       $ok = $DB->execute($query);  
     }
    
    @$DB->execute( 'OPTIMIZE TABLE `specialrules_lines`' );
    @$DB->execute( 'ANALYZE TABLE `specialrules_lines`' );
    
    $cmd = '/opt/gemeinschaft/sbin/start-asterisk 1>>/dev/null 2>>/dev/null';
    @exec( 'sudo sh -c '. qsa($cmd) .' 1>>/dev/null 2>>/dev/null &' );
    
    $edit = $rule_id;
     
}

#####################################################################
#                               save }
#####################################################################
  
#####################################################################
#                               {edit 
#####################################################################
# shows lines on a rule for adding delete or modify
if ($edit) {
	
	echo '<div class="fr"><a href="', gs_url($SECTION, $MODULE, null, 'page='.$page) ,'">', __('zur&uuml;ck') ,'</a></div>' ,"\n";
?>
<p class="text"><small><?php echo __('Hier k&ouml;nnen Asterisk Dialplan Applicationen eingetragen werden. Diese werden von oben nach unten der Reihenfolge abgearbeitet.<br> Eine &Uuml;bersicht &uuml;ber Asterisk Dialplan Applikationen finden Sie <a href="http://www.voip-info.org/wiki-Asterisk+-+documentation+of+application+commands" target="_blank">hier</a>'); ?></small></p>



<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>
<input type="hidden" name="rule_id" value="<?php echo $edit; ?>" />
<input type="hidden" name="action" value="save" />   

<table cellspacing="1" class="phonebook">
<thead>
<tr>
    <th width="50px"><?php echo __('Priorit&auml;t'); ?></th>  
    <th width="300px"><?php echo __('Asterisk Befehl'); ?></th>
    <th width="100px"><?php echo __('Reihenfolge'); ?></th>
</tr>
</thead>
<tbody>

<?php

    
    $rs = $DB->execute(
'SELECT
    `id`, `line`
FROM `specialrules_lines`
WHERE `rule_id`='. $edit . '
ORDER BY `ord`'
    );
    
    $i=0;
    while ($r = $rs->fetchRow()) {
        $count=$i+1;
        
        echo '<td>' .$count ;
        echo '</td>', "\n";   
        echo '</td>', "\n";
        echo '<td>';
        echo '<input type="text" name="line_' .$r['id']. '" value="' .htmlEnt($r['line']). '" size="30" maxlength="200" class="pre" style="font-weight:bold;" />';
        echo '</td>', "\n";
       
        echo '<td>';
        if ($i > 0)
            echo '<a href="', gs_url($SECTION, $MODULE, null, 'rule_id=' .$edit. '&amp;action=move-up&amp;line_id=' .$r['id']). '"><img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up.gif" /></a>';
        else
            echo '<img alt="&uarr;" src="', GS_URL_PATH, 'img/move_up_d.gif" />';
        if ($i < $rs->numRows()-1)
            echo '&thinsp;<a href="', gs_url($SECTION, $MODULE, null, 'rule_id=' .$edit. '&amp;action=move-down&amp;line_id=' .$r['id']). '"><img alt="&darr;" src="', GS_URL_PATH, 'img/move_down.gif" /></a>';
        else
            echo '&thinsp;<img alt="&darr;" src="', GS_URL_PATH, 'img/move_down_d.gif" />';
        echo ' &nbsp; <a href="', gs_url($SECTION, $MODULE, null, 'rule_id=' .$edit. '&amp;action=delete&amp;line_id='.$r['id']). '"><img alt="-;" src="', GS_URL_PATH, 'img/minus.gif" /></a>';
        echo '</td>', "\n";
        
        echo '</tr>', "\n";
        ++$i;
    }
    
    
    echo '<tr>', "\n";
    echo '<td class="transp">&nbsp;</td>', "\n";
    echo '<td class="transp">&nbsp;</td>', "\n";
    echo '<td class="transp">';
    echo '<input type="submit" value="', __('Speichern'), '" />';
    echo '</td>', "\n";
    echo '</tr>', "\n";
       
    $id = 0;
    
    echo '<tr class="', ($i%2 ? 'even':'odd'), '">', "\n";
    echo '<td>&nbsp;';
    echo '</td>', "\n";
    echo '<td>';
    echo '<input type="text" name="line_0" value="" size="30" maxlength="200" class="pre" style="font-weight:bold;" />';
    echo '</td>', "\n";
    echo '<td>&nbsp;';
    echo '</td>', "\n";
    echo '</tr>', "\n";
    ++$i;
    
?>

</tbody>
</table>
</form>
 
<?php
    
}
#####################################################################
#                               edit }
#####################################################################


#####################################################################
#  show special rules {
#####################################################################
if (! $edit) {
	
	$sql_query =
'SELECT SQL_CALC_FOUND_ROWS 
	`id`, `title`, `description`
FROM
	`specialrules` 
ORDER BY `id`        
LIMIT '. ($page*(int)$per_page) .','. (int)$per_page;
	
	$rs = $DB->execute($sql_query);
	
	$num_total = @$DB->numFoundRows();
	$num_pages = ceil($num_total / $per_page);


?>
  <p class="text"><small><?php echo __('Spezialregeln k&ouml;nnen eingehenden Routen zugeordnet werden.'); ?></small></p>


<form method="post" action="<?php echo GS_URL_PATH; ?>">
<?php echo gs_form_hidden($SECTION, $MODULE); ?>

<table cellspacing="1" class="phonebook">
<thead>
<tr>
	<th style="width:30px;"><?php echo __('ID'); ?></th>
	<th style="width:100px;"><?php echo __('Name'); ?></th>
	<th style="width:200px;"><?php echo __('Beschreibung'); ?></th>
	<th style="width:80px;">
<?php
	
	//echo __('S.') ,' ';
	echo ($page+1), ' / ', $num_pages, '&nbsp; ',"\n";
	
	if ($page > 0) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page-1)) ,'" title="', __('zur&uuml;ckbl&auml;ttern'), '" id="arr-prev">',
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('zur&uuml;ck'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/previous_notavail.png" />', "\n";
	}
	
	if ($page < $num_pages-1) {
		echo
		'<a href="', gs_url($SECTION, $MODULE, null, 'page='.($page+1)) ,'" title="', __('weiterbl&auml;ttern'), '" id="arr-next">',
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next.png" />',
		'</a>', "\n";
	} else {
		echo
		'<img alt="', __('weiter'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/next_notavail.png" />', "\n";
	}
?>
	</th>
</tr>
</thead>
<tbody>

<?php
	if (@$rs) {
		$i = 0;
		while ($r = $rs->fetchRow()) {
			echo '<tr class="', ((++$i % 2) ? 'odd':'even'), '">', "\n";
		    
            echo '<td class="r">', htmlEnt($r['id']) ,'</td>',"\n";
	  	    echo '<td>', htmlEnt($r['title']) ,'</td>',"\n";
			echo '<td>', htmlEnt($r['description']) ,'</td>',"\n";       
			echo '<td>',"\n";       
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'edit='.$r['id'] .'&amp;page='.$page) ,'" title="', __('bearbeitent'), '"><img alt="', __('bearbeiten'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/edit.png" /></a> &nbsp; ';
			echo '<a href="', gs_url($SECTION, $MODULE, null, 'delete='.$r['id'] .'&amp;page='.$page) ,'" title="', __('l&ouml;schen'), '" onclick="return confirm_delete();"><img alt="', __('entfernen'), '" src="', GS_URL_PATH, 'crystal-svg/16/act/editdelete.png" /></a>';
			echo '</td>',"\n";
			
            echo '</tr>',"\n";
		}
	}
?>


		<tr class="', ((++$i % 2) ? 'odd':'even'), '">

		<td>&nbsp;</td>
		<td>
			<input type="text" name="title" value="" size="15" maxlength="40" />
		</td>
		<td>
            <input type="text" name="description" value="" size="25" maxlength="40" />        
        </td>
		<td>
			<button type="submit" title="<?php echo __('Regel anlegen'); ?>" class="plain">
				<img alt="<?php echo __('Speichern'); ?>" src="<?php echo GS_URL_PATH; ?>crystal-svg/16/act/filesave.png" />
			</button>
		</td>
        </tr>

</tbody>
</table>
</form>

<?php

}
#####################################################################
#  show special rules }
#####################################################################

?>
 

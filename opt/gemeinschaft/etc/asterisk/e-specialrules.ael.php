#!/usr/bin/php -q
<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4800 $
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

define( 'GS_VALID', true );  /// this is a parent file

ini_set('implicit_flush', 1);
ob_implicit_flush(1);

error_reporting(0);

require_once( dirName(__FILE__) .'/../../inc/conf.php' );
require_once( GS_DIR .'inc/util.php' );
set_error_handler('err_handler_quiet');
require_once( GS_DIR .'inc/db_connect.php' );


echo "\n";
echo '// (auto-generated)' ,"\n";
echo "\n";

$db = gs_db_slave_connect();
if (! $db) die();
//FIXME - should probably write a message to gs_log() before dying



$rs = $db->execute(
	'SELECT 
		`t1`.`ord`, `t1`.`line`, `t1`.`rule_id`, `t2`.`title` , `t1`.`id` 
	FROM 
		`specialrules_lines` `t1`
	JOIN 
		`specialrules` `t2` ON (`t1`.`rule_id`=`t2`.`id`)
	ORDER BY  `t1`.`ord`' 
);

if (! $rs) {
	echo '//ERROR' ,"\n";
	die();
	//FIXME - should probably write a message to gs_log() before dying
}

while ($row = $rs->fetchRow()) {

    $specialrules[$row['rule_id']][] = $row['line']; 
}

echo 'macro pass-specialrule() {' ,"\n";   
echo "\t", 'switch (${specialrule_id}){' ,"\n";
echo "\t\t",'case 0:' ,"\n";   
echo "\t\t\t", 'NoOp(nothing todo);' ,"\n"; 
echo "\t\t\t", 'break;' ,"\n"; 

foreach ($specialrules as $id => $line) {

	echo "\t\t",'case ', $id ,':' ,"\n";
		foreach( $line as $value ) {
			echo "\t\t\t", $value ,';' ,"\n";
		}
		echo "\t\t\t", 'break;' ,"\n";
}
echo "\t\t",'default:' ,"\n";
echo "\t\t\t", 'break;' ,"\n";
echo "\t", '}' ,"\n";
echo '}' ,"\n";


?>
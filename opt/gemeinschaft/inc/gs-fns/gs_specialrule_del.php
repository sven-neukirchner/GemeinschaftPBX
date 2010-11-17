<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 4947 $
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
include_once( GS_DIR .'inc/gs-lib.php' );


/***********************************************************
*    deletes a specialrule and all lines from it
***********************************************************/

function gs_specialrule_del( $rule_id )
{
	$rule_id = (int)$rule_id;
	if ($rule_id < 1)
		return new GsError( 'Bad specialrule ID.' );
	
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check specialrule id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `specialrules` WHERE `id`='. $rule_id );
	if ($num < 1)
		return new GsError( 'Unknown specialrule ID.' );
	
	# remove lines from the rule
 	#
    $ok = $db->execute( 'DELETE FROM `specialrules_lines` WHERE `rule_id`='. $rule_id );
	if (! $ok)
		return new GsError( 'Failed to remove lines from the specialrule.' );
	
	
    # remove rule
    #
    $ok = $db->execute( 'DELETE FROM `specialrules` WHERE `id`='. $rule_id );
	if (! $ok)
		return new GsError( 'Failed to remove specialrule.' );
	
	return true;
}


?>
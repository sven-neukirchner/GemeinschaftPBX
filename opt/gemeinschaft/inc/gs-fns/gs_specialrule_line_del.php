<?php
/*******************************************************************\
*            Gemeinschaft - asterisk cluster gemeinschaft
* 
* $Revision: 5619 $
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
include_once( GS_DIR .'inc/gs-fns/gs_prov_phone_checkcfg.php' );


/***********************************************************
*    deletes a user from a pickup group
***********************************************************/

function gs_specialrule_line_del( $line_id )
{
		
	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );
	
	# check line id
	#
	$num = (int)$db->executeGetOne( 'SELECT COUNT(*) FROM `specialrules_lines` WHERE `id`='. $line_id );
	if ($num < 1)
		return new GsError( 'Unknown special line ID.' );
	
		
	# remove line from the group
	#
	$ok = $db->execute( 'DELETE FROM `specialrules_lines` WHERE `id`='. $line_id );
	if (! $ok)
		return new GsError( 'Failed to remove line from special rule' );
	
		
	return true;
}


?>
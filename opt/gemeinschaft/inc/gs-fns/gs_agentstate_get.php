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
*    returns a user's call forwards
***********************************************************/

function gs_agentstate_get_by_uid( $user_id )
{
	if (! preg_match( '/^[0-9]+$/', $user_id ))
		return new GsError( 'User ID must be numeric.' );

	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );

	# get agent states
	#
	$rs = $db->execute( 'SELECT `state`  FROM `agent_state` WHERE `_user_id`='. $user_id );
	$agentstate = $rs->getRow();
	if (! $agentstate) $agentstate['state'] = 0;
	return $agentstate;
}

function gs_agentstate_get_by_ext ($extension )
{
	if (! preg_match( '/^[0-9]+$/', $extension ))
		return new GsError( 'Extension must be numeric.' );

	# connect to db
	#
	$db = gs_db_master_connect();
	if (! $db)
		return new GsError( 'Could not connect to database.' );

	# get user_id
	#
	$user_id = $db->executeGetOne( 'SELECT `_user_id` FROM `ast_sipfriends` WHERE `name`='. $extension );
	if (! $user_id)
		return new GsError( 'Unknown user.' );

	## get agent states
	#
	$agentstate = $db->executeGetOne( 'SELECT `state`  FROM `agent_state` WHERE `_user_id`='. $user_id );
	
	$extstate =0; 
	if (! $agentstate) 	  $extstate =0; //extension is not a queuemember
	if ($agentstate == 0) $extstate = 0;
	if ($agentstate == 2) $extstate = 64;
	if ($agentstate == 3) $extstate = 128;

	return $extstate;
}
?>
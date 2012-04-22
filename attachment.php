<?php /************************************************************/

////////////////////////////////////////////////////////////////////
// PMOS Help Desk
// -----------------------------------------------------------------
//
// License info can be found in license.txt.  You must leave this
// notice as is.
// 
// Application: PMOS Help Desk
//      Author: John Heathco
//         Web: http://www.h2desk.com/pmos
//
// Use this software at your own risk.  It is neither supported nor
// actively developed.
//
// If you are looking for a supported and developed help desk,
// please check out the h2desk at http://www.h2desk.com
//
// -----------------------------------------------------------------
////////////////////////////////////////////////////////////////////

include "settings.php";
include "include.php";

$_GET[file] = str_replace( "..", "", $_GET[file] );

$res = mysql_query( "SELECT id FROM {$pre}ticket WHERE ( ticket_id = '{$_GET[id]}' && email = '{$_GET[email]}' )" );
$row = mysql_fetch_array( $res );
if( $row )
{
  Header( "Content-type: application/octet-stream" );
  Header( "Content-disposition: inline; filename={$_GET[file]}" ); 

  $fp = @fopen( "{$HD_TICKET_FILES}/{$row[id]}/{$_GET[file]}", "r" );
  if( $fp )
  {
    while( !feof( $fp ) )
      echo fread( $fp, 10240 );

    fclose( $fp );
  }
}

/********************************************************** PHP */?>
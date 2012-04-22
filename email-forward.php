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
include "email-parse.php";

$stdin = fopen( "php://stdin", "r" );

while( !feof( $stdin ) )
  $email .= fread( $stdin, 10240 );

fclose( $stdin );

return parse_email_to_ticket( $email, "" );

/********************************************************** PHP */?>

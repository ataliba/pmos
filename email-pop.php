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

include_once "settings.php";
include_once "include.php";
include_once "popwrapper.php";
include_once "email-parse.php";

$res = mysql_query( "SELECT * FROM {$pre}pop" );
$pop3 = new POP3( );

$error = array( );

while( $row = mysql_fetch_array( $res ) )
{
  if( $pop3->connect( $row[server], $row[port] ) )
  {
    $count = $pop3->login( $row[username], $row[password] );
    if( $count === false )
      array_push( $error, "<b>[ERROR]</b> Could not login to {$row[server]} on port {$row[port]}" );
    else
    {
      for( $i = 1; $i <= $count; $i++ )
      {
        $email = $pop3->get( $i );
        if( $email && count( $email ) )
        {
          $content = "";
          for( $j = 0; $j < count( $email ); $j++ )
            $content .= $email[$j];          

          parse_email_to_ticket( $content, $row[email] );

          if( $row[del] )
            $pop3->delete( $i );
        }
      }
      
      array_push( $error, "<b>[SUCCESS]</b> Retrieved $count messages from {$row[server]}" );
    }
  }
  else
    array_push( $error, "<b>[ERROR]</b> Could not connect to {$row[server]} on port {$row[port]}" );

  $pop3->quit( );
}

/********************************************************** PHP */?>

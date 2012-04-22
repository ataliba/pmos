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

// Richard Heyes' MIME decoding PHP script.  This would have been
// very difficult to do without... thanks!
include "mimedecode.php";

function get_message_parts( $structure )
{
  global $email_parts;

  echo $HD_TICKET_FILES;

  if( isset( $structure->body ) )
  {
    $part = array( "body"=>$structure->body, "type_primary"=>$structure->ctype_primary, "type_secondary"=>$structure->ctype_secondary, "parameters"=>$structure->ctype_parameters, "dparameters"=>$structure->d_parameters, "headers"=>$structure->headers );

    array_push( $email_parts, $part );
  }
  
  if( isset( $structure->parts ) )
  {
    for( $i = 0; $i < count( $structure->parts ); $i++ )
      get_message_parts( $structure->parts[$i] );
  }

}

function parse_email_to_ticket( $email, $receiver )
{
  global $pre;
  global $email_parts;
  global $HD_TICKET_FILES;
 
  $options = array( "header", "footer", "logo", "title", "background", "outsidebackground", "border", "topbar", "menu", "styles", "email", "url", "emailheader", "emailfooter", "email_ticket_created_subject", "email_ticket_created", "email_notify_create_subject", "email_notify_create", "email_notify_reply_subject", "email_notify_reply", "floodcontrol", "email_notifysms_create_subject", "email_notifysms_create", "email_notifysms_reply_subject", "email_notifysms_reply", "email_ticket_notify_subject", "email_ticket_notify" );
  $data = get_options( $options );

  if( strpos( $email, "\r" ) === false )
    $crlf = "\n";
  else
    $crlf = "\r\n";

  $params = array( 'input'          => $email,
           			   'crlf'           => $crlf,
                   'include_bodies' => TRUE,
                   'decode_headers' => TRUE,
                   'decode_bodies'  => TRUE );

  $structure = Mail_mimeDecode::decode( $params, $crlf );
  if( !$structure )
  {
    $res = mysql_query( "SELECT email FROM {$pre}user WHERE ( admin = '1' )" );
    $row = mysql_fetch_array( $res );
    if( $row )
    {
      mail( $row[0], "Help Desk - Failed email", "The following email could not be received by the help desk due to an error.  It has been forwarded to you to make sure it won't be lost:\n\n{$email}", "From: {$data[email]}" );
     
      return false;
    }
  }
    
  $email_parts = array( );
  get_message_parts( $structure );

  $i = count( $structure->headers[received] ) - 1;
  if( $i >= 0 )
  {
    if( preg_match( "/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}/", $structure->headers[received][$i], $match ) )
      $ip = $match[0];
    else
      $ip = "";
  }
  else
    $ip = "";

  $subject = $structure->headers[subject];

  if( preg_match( "/^([^<]+)<([^>]+)>/i", $structure->headers[from], $match ) )
  {
    $name = str_replace( "\"", "", $match[1] ); // Get rid of any quotes
    $email = $match[2];
  }
  else if( preg_match( "/^<?([^>]+)>?/i", $structure->headers[from], $match ) )
    $name = $email = $match[1];

  if( preg_match( "/^([^<]+)<([^>]+)>/i", $structure->headers[to], $match ) )
    $to = $match[2];
  else if( preg_match( "/^<?([^>]+)>?/i", $structure->headers[to], $match ) )
    $to = $match[1];

  $ticket = new_ticket_id( );

  $message = "";
  for( $i = 0; $i < count( $email_parts ); $i++ )
  {
    if( ($email_parts[$i][type_primary] == "text" && $email_parts[$i][type_secondary] == "plain") ||
        ($email_parts[$i][type_primary] == "plain" && $email_parts[$i][type_secondary] == "text") )
      $message = addslashes( $email_parts[$i][body] );

    // Replaces all CID's in HTML files, etc., with the file name that will exist in the directory.  That way
    // HTML files will show images and everything.  Pretty cool stuff...
    else if( isset( $email_parts[$i]["headers"]["content-id"] ) )
    {
      $search = "cid:" . trim( $email_parts[$i]["headers"]["content-id"] );
      $search = str_replace( "<", "", $search );
      $search = str_replace( ">", "", $search );

      for( $j = 0; $j < count( $email_parts ); $j++ )
      {
        if( $j != $i )
        {
          if( isset( $email_parts[$i][parameters][name] ) )
            $email_parts[$j][body] = str_replace( $search, "?id={$ticket}&email={$email}&file=" . urlencode( $email_parts[$i][parameters][name] ), $email_parts[$j][body] );
          else if( isset( $email_parts[$i][dparameters][filename] ) )
            $email_parts[$j][body] = str_replace( $search, "?id={$ticket}&email={$email}&file=" . urlencode( $email_parts[$i][parameters][filename] ), $email_parts[$j][body] );
        }
      }
    }
  }

  // Make sure the help desk isn't sending a ticket to itself, in which case it'd get stuck in an endless loop
  if( $data[email] == $email )
    return false;

  $res = mysql_query( "SELECT email FROM {$pre}pop" );
  while( $row = mysql_fetch_array( $res ) )
    if( $row[0] == $email )
      return false;
  
  // Determine if this user is banned
  if( get_row_count( "SELECT COUNT(*) FROM {$pre}options WHERE ( (name = 'banned_emails' && text LIKE '%{$email}%') || (name = 'banned_ips' && text LIKE '%{$ip}%') ) " ) )
    return false;
  
  // Make sure slashes are added so this stuff can be put in a SQL query
  $name = addslashes( trim( $name ) );
  $email = addslashes( trim( $email ) );
  $subject = addslashes( trim( $subject ) );
  $to = addslashes( trim( $to ) );

  if( trim( $name ) == "" )
    $name = "No Name";

  $email_priority .= $structure->headers["x-priority"];
  if( trim( $email_priority ) == "" )
    $priority = $GLOBALS[PRIORITY_LOW];
  else
  {
    if( $email_priority == 1 )
      $priority = $GLOBALS[PRIORITY_HIGH];
    else if( $email_priority == 3 )
      $priority = $GLOBALS[PRIORITY_MEDIUM];
    else
      $priority = $GLOBALS[PRIORITY_LOW];
  }

  // Receiver is specified if coming from POP processing.  This allows it to work
  // with email forwards.  Otherwise, it uses the 'to' address from the message.
  if( trim( $receiver ) != "" )
    $to = $receiver;

  $res = mysql_query( "SELECT dept.id, dept.name FROM {$pre}dept AS dept, {$pre}pop AS pop WHERE ( pop.dept_id = dept.id && pop.email = '$to' ) LIMIT 1" );
  if( $row = mysql_fetch_array( $res ) )
  {
    $dept_id = $row[0];
    $department = $row[1];
  }
  else
  {
    $dept_id = 0;
    $department = "Global";
  }

  // Check to see if this is a reply to a ticket, not a new ticket
  $subject_words = split( " ", $subject );
  $exists = 0;
  for( $i = 0; $i < count( $subject_words ); $i++ )
  {
    if( trim( $subject_words[$i] ) != "" )
    {
      // Checks to see if there is a ticket with the ID from the subject and the same email address as who created the ticket OR
      // that a staff memeber has posted a reply with that ticket ID as the subject

      $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( ticket_id = '{$subject_words[$i]}' && email = '$email' )" ) || (get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( ticket_id = '{$subject_words[$i]}' )" ) && get_row_count( "SELECT COUNT(*) FROM {$pre}user WHERE ( email = '$email' )" ));
      if( $exists )
      {
        $ticket = $subject_words[$i];
        break;
      }
    }
  }

  if( !$exists )
  {
    // Checks for a duplicate ticket if flood control is enabled
    if( $data[floodcontrol] )
    {
      $res_check = mysql_query( "SELECT id, ticket_id FROM {$pre}ticket WHERE ( name = '$name' && email = '$email' && subject = '$subject' )" );
      while( $row_check = mysql_fetch_array( $res_check ) )
      {
        $res_check_post = mysql_query( "SELECT message FROM {$pre}post WHERE ( ticket_id = '{$row_check[id]}' && user_id = '-1' ) ORDER BY date LIMIT 1" );
        $row_check_post = mysql_fetch_array( $res_check_post );

        if( trim( $row_check_post[message] ) == trim( stripslashes( $message ) ) )
          return false;
      }
    }

    mysql_query( "INSERT INTO {$pre}ticket ( ticket_id, dept_id, email, name, subject, date, status, notify, priority, lastactivity ) VALUES ( '$ticket', '$dept_id', '$email', '$name', '$subject', '" . time( ) . "', '$HD_STATUS_OPEN', '1', '$priority', '" . time( ) . "' )" );

    $id = mysql_insert_id( );

    mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message, ip ) VALUES ( '$id', '-1', '" . time( ) . "', '$subject', '$message', '$ip' )" );

    $post_id = mysql_insert_id( );

    $autoreply = "";
    $res = mysql_query( "SELECT reply, phrase FROM {$pre}reply WHERE ( dept_id = '0' || dept_id = '$dept_id' )" );
    while( $row = mysql_fetch_array( $res ) )
    {
      if( $row[phrase] == "" )
      {
        $autoreply = "{$row[reply]}\n\n";
        break;
      }
      else if( strstr( strtoupper( $subject ), strtoupper( $row[phrase] ) ) )
      {
        $autoreply = "{$row[reply]}\n\n";
        break;
      }
    }

    $name = stripslashes( $name );
    $email = stripslashes( $email );
    $message = stripslashes( $message );
     
    eval( "\$email_subject = \"{$data[email_ticket_created_subject]}\";" );
    eval( "\$email_message = \"{$data[email_ticket_created]}\";" );
    mail( $email, $email_subject, $email_message, "From: {$data[email]}" );

    // Notification messages
    $res_user = mysql_query( "SELECT DISTINCT user.email, user.sms FROM {$pre}user AS user, {$pre}privilege AS priv WHERE ( user.id = priv.user_id && (priv.dept_id = '0' || priv.dept_id = '$dept_id') && user.notify & {$GLOBALS[HD_NOTIFY_CREATION]} > '0' )" );
    
    while( $row_user = mysql_fetch_array( $res_user ) )
    {
      eval( "\$email_subject = \"{$data[email_notify_create_subject]}\";" );
      eval( "\$email_message = \"{$data[email_notify_create]}\";" );
      mail( $row_user[email], $email_subject, $email_message, "From: {$data[email]}" );

      if( trim( $row_user[sms] ) != "" )
      {
        eval( "\$email_subject = \"{$data[email_notifysms_create_subject]}\";" );
        eval( "\$email_message = \"{$data[email_notifysms_create]}\";" );
        mail( $row_user[sms], $email_subject, $email_message, "From: {$data[email]}" );
      }
    }
  }
  else
  {
    $res = mysql_query( "SELECT id FROM {$pre}ticket WHERE ( ticket_id = '$ticket' )" );
    $row = mysql_fetch_array( $res );
    $id = $row[0];

    // Checks for a duplicate posting if flood protection is enabled
    $res_check = mysql_query( "SELECT subject, message FROM {$pre}post WHERE ( ticket_id = '$id' ) ORDER BY date DESC LIMIT 1" );
    $row_check = mysql_fetch_array( $res_check );
    if( $data[floodcontrol] && (trim( $row_check[message] ) == trim( stripslashes( $message ) )) )
      return false;

    // Check to see if this is a staff memeber posting, or a customer posting.
    $res_check = mysql_query( "SELECT id FROM {$pre}user WHERE ( email = '$email' )" );
    $row_check = mysql_fetch_array( $res_check );
    if( $row_check ) // It's staff's posting
    {
      $res = mysql_query( "SELECT email, notify FROM {$pre}ticket WHERE ( ticket_id = '$ticket' )" );
      $row = mysql_fetch_array( $res );
      
      mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message ) VALUES ( '$id', '{$row_check[id]}', '" . time( ) . "', '$subject', '$message' )" );

      $name = stripslashes( $name );
      $email = stripslashes( $email );
      $message = stripslashes( $message );

      if( $row[notify] )
      {
        eval( "\$email_subject = \"{$data[email_ticket_notify_subject]}\";" );
        eval( "\$email_message = \"{$data[email_ticket_notify]}\";" );

        $addresses = split( " ", $row[cc] );
        array_push( $addresses, $row[email] );

        for( $i = 0; $i < count( $addresses ); $i++ )
          mail( $addresses[$i], $email_subject, $email_message, "From: {$data[email]}" );
      }
    }
    else // It's customer's posting
    {
      mysql_query( "INSERT INTO {$pre}post ( ticket_id, user_id, date, subject, message ) VALUES ( '$id', '-1', '" . time( ) . "', '$subject', '$message' )" );

      // Notification messages
      $res_user = mysql_query( "SELECT DISTINCT user.email, user.sms FROM {$pre}user AS user, {$pre}privilege AS priv, {$pre}post AS post WHERE ( user.id = priv.user_id && (priv.dept_id = '0' || priv.dept_id = '$dept_id') && user.notify & {$GLOBALS[HD_NOTIFY_REPLY]} > '0' && post.user_id = user.id && post.ticket_id = '$id' )" );

      $name = stripslashes( $name );
      $email = stripslashes( $email );
      $message = stripslashes( $message );

      while( $row_user = mysql_fetch_array( $res_user ) )
      {
        eval( "\$email_subject = \"{$data[email_notify_reply_subject]}\";" );
        eval( "\$email_message = \"{$data[email_notify_reply]}\";" );
        mail( $row_user[email], $email_subject, $email_message, "From: {$data[email]}" );

        if( trim( $row_user[sms] ) != "" )
        {
          eval( "\$email_subject = \"{$data[email_notifysms_create_subject]}\";" );
          eval( "\$email_message = \"{$data[email_notifysms_create]}\";" );
          mail( $row_user[sms], $email_subject, $email_message, "From: {$data[email]}" );
        }
      }

      mysql_query( "UPDATE {$pre}ticket SET lastactivity = '" . time( ) . "', lastpost = '-1', status = '$HD_STATUS_OPEN' WHERE ( id = '$id' )" );
    }
  }

  $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'uploads' )" );
  $row = mysql_fetch_array( $res );
  if( !$row )
    $data[uploads] = 0;
  else
    $data[uploads] = $row[0];

  if( $data[uploads] )
  {
    if( !is_dir( "{$HD_TICKET_FILES}/{$id}" ) )
    {
      $oldumask = umask( 0 ); 
      mkdir( "{$HD_TICKET_FILES}/{$id}", 0777 );
      umask( $oldumask );
    }
    
    // Creates files for all the attachments
    for( $i = 0; $i < count( $email_parts ); $i++ )
    {
      if( !(($email_parts[$i][type_primary] == "text" && $email_parts[$i][type_secondary] == "plain") ||
           ($email_parts[$i][type_primary] == "plain" && $email_parts[$i][type_secondary] == "text")) )
      {
        if( isset( $email_parts[$i][parameters][name] ) )
          $filename = $email_parts[$i][parameters][name];
        else if( isset( $email_parts[$i][dparameters][filename] ) )
          $filename = $email_parts[$i][dparameters][filename];
        else
          $filename = sprintf( "%s-%s.%03d", $email_parts[$i][type_primary], $email_parts[$i][type_secondary], $i );

        $fp = fopen( "{$HD_TICKET_FILES}/{$id}/{$filename}", "w" );
        if( $fp )
        {
          fwrite( $fp, $email_parts[$i][body] );
          fclose( $fp );
        }
      }      
    }
  }

  return true;
}

/********************************************************** PHP */?>
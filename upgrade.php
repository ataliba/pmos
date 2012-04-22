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

echo "Upgrading help desk to version 2.4...<br><br>";

//////////////////////////////
// CHANGES FROM 1.3 to 1.31 //
//////////////////////////////
$res = mysql_query( "SHOW FIELDS FROM {$pre}post" );
while( $row = mysql_fetch_array( $res ) )
{
  // Modify ticket_id column - change to INT (was VARCHAR)
  if( $row[Field] == "ticket_id" && strstr( $row[Type], "varchar" ) )
  {
    mysql_query( "ALTER TABLE {$pre}post CHANGE ticket_id ticket_id INT NOT NULL default '0'" );
    echo "Changing ticket_id field from post table... done (1.3-1.31)<br>";
  }
}
// Add 'notify' field to user table
if( !mysql_query( "SELECT COUNT(notify) FROM {$pre}user" ) )
{
  mysql_query( "ALTER TABLE {$pre}user ADD notify INT NOT NULL default '0'" );
  echo "Adding notify field to user table... done (1.3-1.31)<br>";
}


//////////////////////////////
// CHANGES FROM 1.31 to 1.4 //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}pop" ) )
{
  mysql_query( "CREATE TABLE {$pre}pop ( id int(11) NOT NULL auto_increment, dept_id int(11) NOT NULL default '0', server varchar(255) NOT NULL default '', port int(11) NOT NULL default '110', username varchar(255) NOT NULL default '', password varchar(255) NOT NULL default '',  email varchar(255) NOT NULL default '', del tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo "Adding new email table... done (1.31-1.4)<br>";
}

if( !mysql_query( "SELECT COUNT(*) FROM {$pre}faq" ) )
{
  mysql_query( "CREATE TABLE {$pre}faq ( id int(11) NOT NULL auto_increment, description text NOT NULL, symptoms text NOT NULL, solution text NOT NULL, category int(11) NOT NULL default '0', parent int(11) NOT NULL default '0', date int(11) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo "Adding new knowledge base table... done (1.31-1.4)<br>";
}

//////////////////////////////
// CHANGES FROM 1.4 to 1.5  //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(options) FROM {$pre}dept" ) )
{
  mysql_query( "ALTER TABLE {$pre}dept ADD options INT NOT NULL default '0'" );
  echo "Adding options field to department table... done (1.4-1.5)<br>";
}

//////////////////////////////
// CHANGES FROM 1.5 to 1.6  //
//////////////////////////////
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'uploads' )" ) ) )
{
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'uploads', '', '0' )" );
  echo "Adding file upload support.  If you want this enabled, be sure to create a subdirectory 'files' and CHMOD it to 777.  Also check the 'allow file uploads' in the general settings section... done (1.5-1.6)<br>";
}

//////////////////////////////
// CHANGES FROM 1.6 to 2.0  //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}survey" ) )
{
  mysql_query( "CREATE TABLE {$pre}survey ( id int(11) NOT NULL auto_increment, ticket_id int(11) NOT NULL default '0', rating1 tinyint(4) NOT NULL default '0', rating2 tinyint(4) NOT NULL default '0', rating3 tinyint(4) NOT NULL default '0', rating4 tinyint(4) NOT NULL default '0', rating5 tinyint(4) NOT NULL default '0', rating6 tinyint(4) NOT NULL default '0', rating7 tinyint(4) NOT NULL default '0', rating8 tinyint(4) NOT NULL default '0', rating9 tinyint(4) NOT NULL default '0', rating10 tinyint(4) NOT NULL default '0', comments text NOT NULL,  date int(11) NOT NULL default '0', email varchar(255) NOT NULL default '', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo "Adding new survey table to support client feedback, more info in manual... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_ticket_created' )" ) ) )
{
  // Add email message for new ticket creation
  $data = "{\$data[emailheader]}" .
          "\$name,\n\n" .
          "Your support ticket has been created and successfully dispatched to the \$department\n" .
          "department.  Here is the information you will need to access your ticket:\n\n" .
          "Ticket ID: \$ticket\n" .
          "Email: \$email\n" .
          "Link to view ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_TICKET_VIEW]}?cmd=view&id={\$ticket}&email={\$email}\n\n" .
          "\$autoreply" .
          "{\$data[title]}\n" .
          "{\$data[url]}" .
          "{\$data[emailfooter]}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_created', '', '$data' )" );
  $data = "\$ticket - New Ticket Created";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_created_subject', '', '$data' )" );
  echo "Adding ticket creation email to database... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_ticket_notify' )" ) ) )
{
  // Add email message for reply to ticket
  $data = "{\$data[emailheader]}" .
          "\$name,\n\n" .
          "Your ticket concerning \'\$subject\' has been responded to.  You can view\n" .
          "this response (and reply to it if necessary) using the information or link below.\n\n" .
          "Ticket ID: \$ticket\n" .
          "Email: \$email\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_TICKET_VIEW]}?cmd=view&id={\$ticket}&email={\$email}\n\n" .
          "Here is the response made to your ticket.  Please do not reply to this email directly, use the link above\n" .
          "to reply.\n\n" .
          "\$message\n\n" .
          "{\$data[title]}\n" .
          "{\$data[url]}" .
          "{\$data[emailfooter]}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_notify', '', '$data' )" );
  $data = "\$ticket - Reply To Your Ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_notify_subject', '', '$data' )" );
  echo "Adding ticket notification email to database... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_ticket_survey' )" ) ) )
{
  // Add email message for reply to ticket
  $data = "{\$data[emailheader]}" .
          "\$name,\n\n" .
          "Your ticket concerning \'\$subject\' has been closed.  Please take a moment to complete\n" .
          "a short survey that will help us to serve you better in the future.\n\n" .
          "Link to survey: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_TICKET_SURVEY]}?id={\$ticket}&email={\$email}\n\n" .
          "We appreciate your time!\n\n" .
          "{\$data[title]}\n" .
          "{\$data[url]}" .
          "{\$data[emailfooter]}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_survey', '', '$data' )" );
  $data = "\$ticket - Please Survey Our Support";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_survey_subject', '', '$data' )" );
  echo "Adding ticket survey email to database... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'autosurvey' )" ) ) )
{
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ('autosurvey', '', '0');" );
  echo "Adding autosurvey field into table.  Can be enabled in survey configuration... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'repeatsurvey' )" ) ) )
{
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ('repeatsurvey', '', '0');" );
  echo "Adding repeat survey field into table.  Can be enabled in survey configuration... done (1.6-2.0)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'survey1' )" ) ) )
{
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ('survey1', '1', 'Overall Support Rating');" );
  echo "Adding survey question into table.  You can change the survey questions in the survey section... done (1.6-2.0)<br>";
}

//////////////////////////////
// CHANGES FROM 2.0 to 2.1  //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(sms) FROM {$pre}user" ) )
{
  mysql_query( "ALTER TABLE {$pre}user ADD sms varchar(255) NOT NULL default ''" );
  echo "Adding sms email field to user table... done (2.0-2.1)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_notify_reply' )" ) ) )
{
  // Add email message for notification when a ticket is replied to
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "One of the tickets in which you have posted has been replied to by the customer.\n\n" .
          "Here is the ticket information:\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n" . 
          "Reply contents below:\n\n" .
          "{\$message}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notify_reply', '', '$data' )" );
  $data = "Help Desk Notification - New Ticket Reply: \$ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notify_reply_subject', '', '$data' )" );
  echo "Adding notify ticket reply email to database... done (2.0-2.1)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_notify_create' )" ) ) )
{
  // Add email message for notification when a ticket is created
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "A new ticket has been created in one of the departments you have been assigned to.\n\n" .
          "Here is the ticket information:\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n" .
          "Ticket contents below:\n\n" .
          "{\$message}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notify_create', '', '$data' )" );
  $data = "Help Desk Notification - New Ticket Created: \$ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notify_create_subject', '', '$data' )" );
  echo "Adding notify ticket create email to database... done (2.0-2.1)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_notifysms_reply' )" ) ) )
{
  // Add email message for notification when a ticket is replied to (SMS)
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "One of the tickets in which you have posted has been replied to by the customer.\n\n" .
          "Here is the ticket information:\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n" . 
          "Reply contents below:\n\n" .
          "{\$message}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notifysms_reply', '', '$data' )" );
  $data = "Help Desk Notification - New Ticket Reply: \$ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notifysms_reply_subject', '', '$data' )" );
  echo "Adding notify SMS ticket reply email to database... done (2.0-2.1)<br>";
}
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_notifysms_create' )" ) ) )
{
  // Add email message for notification when a ticket is created (SMS)
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "A new ticket has been created in one of the departments you have been assigned to.\n\n" .
          "Here is the ticket information:\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n" .
          "Ticket contents below:\n\n" .
          "{\$message}";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notifysms_create', '', '$data' )" );
  $data = "Help Desk Notification - New Ticket Created: \$ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_notifysms_create_subject', '', '$data' )" );
  echo "Adding notify SMS ticket create email to database... done (2.0-2.1)<br>";
}
if( !mysql_query( "SELECT COUNT(ip) FROM {$pre}post" ) )
{
  mysql_query( "ALTER TABLE {$pre}post ADD ip varchar(20) NOT NULL default ''" );
  echo "Adding IP field to post table... done (2.0-2.1)<br>";
}
if( !mysql_query( "SELECT COUNT(lastpost) FROM {$pre}ticket" ) )
{
  mysql_query( "ALTER TABLE {$pre}ticket ADD lastpost INT NOT NULL default '-1'" );
  $res = mysql_query( "SELECT id FROM {$pre}ticket" );
  while( $row = mysql_fetch_array( $res ) )
  {
    $res_post = mysql_query( "SELECT user_id FROM {$pre}post WHERE ( ticket_id = '{$row[id]}' ) ORDER BY date DESC LIMIT 1" );
    $row_post = mysql_fetch_array( $res_post );

    mysql_query( "UPDATE {$pre}ticket SET lastpost = '{$row_post[user_id]}' WHERE ( id = '{$row[id]}' )" );
  }

  echo "Adding lastpost field to ticket table... done (2.1-2.11)<br>";
}

//////////////////////////////
// CHANGES FROM 2.11 to 2.2 //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(private) FROM {$pre}post" ) )
{
  mysql_query( "ALTER TABLE {$pre}post ADD private tinyint(4) NOT NULL default '0'" );
  echo "Adding private field to post table... done (2.11-2.2)<br>";
}
if( !mysql_query( "SELECT COUNT(flag) FROM {$pre}ticket" ) )
{
  mysql_query( "ALTER TABLE {$pre}ticket ADD flag int(11) NOT NULL default '-1'" );
  echo "Adding flag field to ticket table... done (2.11-2.2)<br>";
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}message" ) )
{
  mysql_query( "CREATE TABLE {$pre}message ( id int(11) NOT NULL auto_increment, ticket_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0', viewed tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo "Adding message table for messaging functionality... done (2.11-2.2)<br>";
}

//////////////////////////////
// CHANGES FROM 2.2 to 2.3  //
//////////////////////////////
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_ticket_lookup' )" ) ) )
{
  // Add email message for ticket lookup
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "Here are your all of your tickets for the help desk.  The most recent tickets are shown first:\n\n" .
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_lookup', '', '$data' )" );
  $data = "Help Desk Notification - Ticket Lookup";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_lookup_subject', '', '$data' )" );
  echo "Adding ticket-lookup email to database... done (2.2-2.3)<br>";
}
if( !mysql_query( "SELECT COUNT(sortnum) FROM {$pre}dept" ) )
{
  mysql_query( "ALTER TABLE {$pre}dept ADD sortnum int(11) NOT NULL default '0'" );

  $res = mysql_query( "SELECT id FROM {$pre}dept" );
  $i = 0;
  while( $row = mysql_fetch_array( $res ) )
  {
    mysql_query( "UPDATE {$pre}dept SET sortnum = '$i' WHERE ( id = '{$row[id]}' )" );
    $i++;
  }

  echo "Adding sorting field to department table... done (2.2-2.3)<br>";
}
if( !mysql_query( "SELECT COUNT(description) FROM {$pre}dept" ) )
{
  mysql_query( "ALTER TABLE {$pre}dept ADD description varchar(255) NOT NULL default ''" );
  echo "Adding description field to department table... done (2.2-2.3)<br>";
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}field" ) )
{
  mysql_query( "CREATE TABLE {$pre}field ( id int(11) NOT NULL auto_increment, dept_id int(11) NOT NULL default '0', name varchar(255) NOT NULL default '', required tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo "Adding custom field table... done (2.2-2.3)<br>";
}

//////////////////////////////
// CHANGES FROM 2.3 to 2.31 //
//////////////////////////////
if( !mysql_num_rows( mysql_query( "SELECT * FROM {$pre}options WHERE ( name = 'email_ticket_flagged' )" ) ) )
{
  // Add email message for ticket lookup
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "A ticket has been flagged to you (and possibly other staff):\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_flagged', '', '$data' )" );
  $data = "Help Desk Notification - Flagged Ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_flagged_subject', '', '$data' )" );
  echo "Adding ticket-lookup email to database... done (2.3-2.31)<br>";
}

//////////////////////////////
// CHANGES FROM 2.31 to 2.4 //
//////////////////////////////
if( !mysql_query( "SELECT COUNT(pwkey) FROM {$pre}user" ) )
{
  $res = mysql_query( "SELECT * FROM {$pre}user" );
  while( $row = mysql_fetch_array( $res ) )
    mysql_query( "UPDATE {$pre}user SET password = '" . crypt( $row[password], $ENCRYPT_KEY ) . "' WHERE ( id = '{$row[id]}' )" );

  mysql_query( "ALTER TABLE {$pre}user ADD pwkey varchar(255) NOT NULL default ''" );
  echo "Adding password key field to user table... done (2.31-2.4)<br>";
  echo "Encrypting passwords... done (2.31-2.4)<br>";
}

if( !mysql_query( "SELECT COUNT(cc) FROM {$pre}ticket" ) )
{
  mysql_query( "ALTER TABLE {$pre}ticket ADD cc text NOT NULL" );
  echo "Adding carbon copy to tickets... done (2.31-2.4)<br>";
}

echo "<br>Done upgrading help desk to version 2.4! Enjoy!";

/********************************************************** PHP */?>
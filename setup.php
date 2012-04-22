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
include "header.php";
/********************************************************** PHP */?>

<?php /************************************************************/
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}ticket" ) )
{
  mysql_query( "CREATE TABLE {$pre}ticket ( id int(11) NOT NULL auto_increment, ticket_id varchar(40) NOT NULL default '', dept_id int(11) NOT NULL default '0', email varchar(255) NOT NULL default '', name varchar(20) NOT NULL default '', subject varchar(255) NOT NULL default '', date int(11) NOT NULL default '0', status int(11) NOT NULL default '0', notify tinyint(4) NOT NULL default '1', priority tinyint(4) NOT NULL default '0', custom text NOT NULL, lastactivity int(11) NOT NULL default '0',  lastpost int(11) NOT NULL default '-1', flag int(11) NOT NULL default '-1', private int(4) NOT NULL default '0', cc text NOT NULL, PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}post" ) )
{
  mysql_query( "CREATE TABLE {$pre}post ( id int(11) NOT NULL auto_increment, ticket_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0', date int(11) NOT NULL default '0', subject varchar(255) NOT NULL default '', message text NOT NULL, ip varchar(20) NOT NULL default '', private tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}message" ) )
{
  mysql_query( "CREATE TABLE {$pre}message ( id int(11) NOT NULL auto_increment, ticket_id int(11) NOT NULL default '0', user_id int(11) NOT NULL default '0', viewed tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}dept" ) )
{
  mysql_query( "CREATE TABLE {$pre}dept ( id int(11) NOT NULL auto_increment, name varchar(255) NOT NULL default '', options int(11) NOT NULL default '0', sortnum int(11) NOT NULL default '0', description varchar(255) NOT NULL default '', PRIMARY KEY  (id)) TYPE=MyISAM;" );
  echo mysql_error( );

  mysql_query( "INSERT INTO {$pre}dept ( name ) VALUES ( 'Global (All Departments)' )" );
  mysql_query( "UPDATE {$pre}dept SET id = '0' WHERE ( name = 'Global (All Departments)' )" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}user" ) )
{
  mysql_query( "CREATE TABLE {$pre}user ( id int(11) NOT NULL auto_increment, name varchar(70) NOT NULL default '', email varchar(255) NOT NULL default '', sms varchar(255) NOT NULL default '', signature text NOT NULL, password varchar(20) NOT NULL default '', admin tinyint(4) NOT NULL default '0', date int(11) NOT NULL default '0', lastlogin int(11) NOT NULL default '0', notify int(11) NOT NULL default '0',  pwkey varchar(255) NOT NULL default '', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}privilege" ) )
{
  mysql_query( "CREATE TABLE {$pre}privilege ( id int(11) NOT NULL auto_increment, user_id int(11) NOT NULL default '0', dept_id int(11) NOT NULL default '0', admin int(11) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}field" ) )
{
  mysql_query( "CREATE TABLE {$pre}field ( id int(11) NOT NULL auto_increment, dept_id int(11) NOT NULL default '0', name varchar(255) NOT NULL default '', required tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}options" ) )
{
  mysql_query( "DELETE FROM {$pre}options" );

  mysql_query( "CREATE TABLE {$pre}options ( id int(11) NOT NULL auto_increment, name varchar(50) NOT NULL default '', num int(11) NOT NULL default '0', text text NOT NULL,  PRIMARY KEY  (id)) TYPE=MyISAM;" );

  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'background', '', '#FFFFFF');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'outsidebackground', '', '#94BECE');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'border', '', '#182C5A');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'topbar', '', '#31799C');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'menu', '', '#DDDDDD');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'styles', '', 'a\r\n{ \r\n  color: navy;  \r\n  text-decoration: underline;\r\n}\r\na:visited\r\n{ \r\n  color: navy;  \r\n  text-decoration: underline;\r\n}\r\na:active\r\n{\r\n  color: navy;  \r\n  text-decoration: underline;\r\n}\r\na:hover\r\n{\r\n  color: navy;  \r\n  text-decoration: underline;\r\n}\r\n.normal\r\n{\r\n  font: 9pt Verdana, Arial, Helvetica;\r\n}\r\n.title\r\n{\r\n  font: bold 14pt Arial, Helvetica, Verdana;\r\n  color: #182C5A;\r\n}');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'tags', '', '1');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'uploads', '', '0');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'autosurvey', '', '0');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'repeatsurvey', '', '0');" );
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'survey1', '1', 'Overall Support Rating');" );

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

  // Add email message for surveys
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

  // Add email message for ticket lookup
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "Here are your all of your tickets for the help desk.  The most recent tickets are shown first:\n\n";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_lookup', '', '$data' )" );
  $data = "Help Desk Notification - Ticket Lookup";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_lookup_subject', '', '$data' )" );

  // Add email message for ticket lookup
  $data = "{\$data[title]}\n" .
          "------------------------------\n\n" .
          "A ticket has been flagged to you (and possibly other staff):\n\n" .
          "Ticket ID: {\$ticket}\n" .
          "Link to ticket: {\$GLOBALS[PATH_TO_HELPDESK]}{\$GLOBALS[HD_URL_ADMINVIEW]}?id={\$ticket}\n\n";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_flagged', '', '$data' )" );
  $data = "Help Desk Notification - Flagged Ticket";
  mysql_query( "INSERT INTO {$pre}options ( name, num, text ) VALUES ( 'email_ticket_flagged_subject', '', '$data' )" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}reply" ) )
{
  mysql_query( "CREATE TABLE {$pre}reply ( id int(11) NOT NULL auto_increment, dept_id int(11) NOT NULL default '0', reply text NOT NULL, phrase varchar(255) NOT NULL default '',  PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}pop" ) )
{
  mysql_query( "CREATE TABLE {$pre}pop ( id int(11) NOT NULL auto_increment, dept_id int(11) NOT NULL default '0', server varchar(255) NOT NULL default '', port int(11) NOT NULL default '110', username varchar(255) NOT NULL default '', password varchar(255) NOT NULL default '',  email varchar(255) NOT NULL default '', del tinyint(4) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}faq" ) )
{
  mysql_query( "CREATE TABLE {$pre}faq ( id int(11) NOT NULL auto_increment, description text NOT NULL, symptoms text NOT NULL, solution text NOT NULL, category int(11) NOT NULL default '0', parent int(11) NOT NULL default '0', date int(11) NOT NULL default '0', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}survey" ) )
{
  mysql_query( "CREATE TABLE {$pre}survey ( id int(11) NOT NULL auto_increment, ticket_id int(11) NOT NULL default '0', rating1 tinyint(4) NOT NULL default '0', rating2 tinyint(4) NOT NULL default '0', rating3 tinyint(4) NOT NULL default '0', rating4 tinyint(4) NOT NULL default '0', rating5 tinyint(4) NOT NULL default '0', rating6 tinyint(4) NOT NULL default '0', rating7 tinyint(4) NOT NULL default '0', rating8 tinyint(4) NOT NULL default '0', rating9 tinyint(4) NOT NULL default '0', rating10 tinyint(4) NOT NULL default '0', comments text NOT NULL,  date int(11) NOT NULL default '0', email varchar(255) NOT NULL default '', PRIMARY KEY  (id)) TYPE=MyISAM;" );
}
 
/********************************************************** PHP */?>
<div class="title">Setting Up <?php echo $script_name ?></div><br />
<?php /************************************************************/

$admin_exists = get_row_count( "SELECT COUNT(user.id) FROM {$pre}user AS user, {$pre}privilege AS privilege WHERE ( privilege.dept_id = '0' && privilege.admin = '1' && privilege.user_id = user.id )" );

if( $_POST[cmd] == "admin" )
{
  $already_exist = get_row_count( "SELECT COUNT(user.id) FROM {$pre}user AS user WHERE ( user.email = '$_POST[email]' )" );
  
  if( trim( $_POST[name] ) == "" ||
      trim( $_POST[email] ) == "" ||
      trim( $_POST[password1] ) == "" ||
      $_POST[password1] != $_POST[password2] ||
      $already_exist )
  {
    $errors = 1;
    echo "<div class=\"errorbox\">Please fill in all fields and make sure that your passwords match.  Also make sure that the email you entered isn't already assigned to another account.</div><br />";
  }
  else
  {
    if( !$admin_exists )
    {
      mysql_query( "INSERT INTO {$pre}user ( name, email, password, admin, date ) VALUES ( '{$_POST[name]}', '{$_POST[email]}', '" . crypt( $_POST[password1], $ENCRYPT_KEY ) . "', '1', '" . time( ) . "' )" );

      $id = mysql_insert_id( );
      mysql_query( "INSERT INTO {$pre}privilege ( user_id, dept_id, admin ) VALUES ( '$id', '0', '1' )" );

      echo "<div class=\"successbox\">New administrator created successfully.  You may now <a href=\"$HD_URL_LOGIN\">login</a> and begin using the help desk.</div><br />";

      $admin_exists = -1;
    }
  }
}

if( !$admin_exists )
{
/********************************************************** PHP */?>

<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    You must create at least one global administrator account for the help desk.  This administrator will
    be able to manage departments, create accounts, and everything else related to the operation
    of the help desk.  You will be able to change your other profile information once you
    are logged in.
  </div>
</td></tr>
</table>
</div><br />

<table>
<form action="setup.php" method="post">
  <input type="hidden" name="cmd" value="admin" /> 
  <tr><td><label for="name">Name: </td><td><input type="text" name="name" size="30" value="<?php echo field( $_POST[name] ) ?>" /></label></td></tr>
  <tr><td><label for="email">Email: </td><td><input type="text" name="email" size="30" value="<?php echo field( $_POST[email] ) ?>" /></label></td></tr>
  <tr><td><label for="password1">Password: </td><td><input type="password" name="password1" size="30" /></label></td></tr>
  <tr><td><label for="password2">Password Again: </td><td><input type="password" name="password2" size="30" /></label></td></tr>
  <tr><td><br /><input type="submit" value="Create Account" /></td></tr>
</form>
</table>

<?php /************************************************************/
}
else if( $admin_exists != -1 ) // Wasn't just created, already had existed
{
/********************************************************** PHP */?>
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    A global administrator already exists for the help desk.  If you forgot your login information,
    you may <a href="<?php echo $HD_URL_LOGIN ?>">retrieve</a> it.
  </div>
</td></tr>
</table>
<?php /************************************************************/
}
/********************************************************** PHP */?>

<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
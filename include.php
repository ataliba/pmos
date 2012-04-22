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

include "language.php"; // Language pack file

$script_name = "PMOS Help Desk";
$script_info = "PMOS Help Desk 2.4";

$LOGIN_INVALID = 0;
$LOGIN_USER = 1;
$LOGIN_CUST = 2;

$PRIORITY_LOW = 0;
$PRIORITY_MEDIUM = 1;
$PRIORITY_HIGH = 2;

$HD_STATUS_OPEN = 0;
$HD_STATUS_CLOSED = 1;
$HD_STATUS_HELD = 2;

$HD_NOTIFY_CREATION = 1;
$HD_NOTIFY_REPLY = 2;
$HD_NOTIFY_SAVELOGIN = 4;

$HD_DEPARTMENT_INVISIBLE = 1;

$HD_TICKET_FILES = "files";

$HD_URL_LOGIN = "login.php";
$HD_URL_USER = "user.php";
$HD_URL_DEPARTMENT = "department.php";
$HD_URL_PROFILE = "profile.php";
$HD_URL_FORM = "form.php";
$HD_URL_GENERAL = "general.php";
$HD_URL_REPLIES = "replies.php";
$HD_URL_REPLIESVIEW = "repliesview.php";
$HD_URL_BROWSE = "browse.php";
$HD_URL_ADMINVIEW = "adminview.php";
$HD_URL_SETUP = "setup.php";
$HD_URL_EMAIL = "email.php";
$HD_URL_MANUAL = "manual.php";
$HD_URL_EMAILS = "emails.php";
$HD_URL_FAQADMIN = "faqadmin.php";
$HD_URL_BACKUP = "backup.php";
$HD_URL_MASSREPLY = "massreply.php";
$HD_URL_STATS = "stats.php";
$HD_URL_ADMINTICKET = "adminticket.php";
$HD_URL_SURVEY = "adminsurvey.php";
$HD_URL_PRINTABLE = "printable.php";
$HD_URL_MESSAGES = "messages.php";
$HD_URL_ATTACHMENT = "attachment.php";
$HD_URL_PASSWORD = "password.php";

$HD_URL_TICKET_HOME = "index.php";
$HD_URL_TICKET_TAGS = "tickettags.php";
$HD_URL_TICKET_VIEW = "ticketview.php";
$HD_URL_TICKET_LOST = "ticket.php";
$HD_URL_TICKET_SURVEY = "survey.php";
$HD_URL_FAQ = "faq.php";

$ENCRYPT_KEY = "IV";

$CODEFROM = array(
  "/([^=a-z0-9\._-]|^)([a-z_-][a-z0-9\._-]*@[a-z0-9_-]+(\.[a-z0-9_-]+)+)/is",
  "/([^=\]]|^)(https?:\/\/[^<>()\s]+)/is",
  "/\[url\](.+?)\[\/url\]/is",
  "/\[url=(.+?)\](.+?)\[\/url\]/is",
  "/\[b\](.+?)\[\/b\]/is",
  "/\[i\](.+?)\[\/i\]/is",
  "/\[u\](.+?)\[\/u\]/is",
  "/\[s\](.+?)\[\/s\]/is",
  "/\[img\](.+?)\[\/img\]/i",
  "/\[color=([\w#]+)\](.*?)\[\/color\]/is",
  "/\[black\](.+?)\[\/black\]/is",
  "/\[white\](.+?)\[\/white\]/is",
  "/\[red\](.+?)\[\/red\]/is",
  "/\[green\](.+?)\[\/green\]/is",
  "/\[blue\](.+?)\[\/blue\]/is",
  "/\[font=(.+?)\](.+?)\[\/font\]/is",
  "/\[size=(.+?)\](.+?)\[\/size\]/is",
	"/\[pre\](.+?)\[\/pre\]/is",
	"/\[left\](.+?)\[\/left\]/is",
	"/\[right\](.+?)\[\/right\]/is",
	"/\[center\](.+?)\[\/center\]/is",
	"/\[sub\](.+?)\[\/sub\]/is",
	"/\[sup\](.+?)\[\/sup\]/is",
	"/\[table\](.+?)\[\/table\]/is",
	"/\[tr\](.+?)\[\/tr\]/is",
	"/\[td\](.+?)\[\/td\]/is",
	"/\[ftp\](.+?)\[\/ftp\]/is",
	"/\[ftp=(.+?)\](.+?)\[\/ftp\]/is",
  "/\[email\](.+?)\[\/email\]/is",
  "/\[hr\]/i",
  "/\[list\]/",
  "/\[\/list\]/",
  "/\[\*\](.+?)/s",
  "/\[code\](.+?)\[\/code\]/is"
);

$CODETO = array(
  "$1<a href=\"mailto:$2\">$2</a>",
  "$1<a href=\"$2\" target=\"_blank\">$2</a>",
  "<a href=\"$1\" target=\"_blank\">$1</a>",
  "<a href=\"$1\" target=\"_blank\">$2</a>",
  "<b>$1</b>",
  "<i>$1</i>",
  "<u>$1</u>",
  "<s>$1</s>",
  "<img src=\"$1\">",
  "<font color=\"$1\">$2</font>",
  "<font color=\"#000000\">$1</font>",
  "<font color=\"#FFFFFF\">$1</font>",
  "<font color=\"#FF0000\">$1</font>",
  "<font color=\"#00FF00\">$1</font>",
  "<font color=\"#0000FF\">$1</font>",
  "<font face=\"$1\">$2</font>",
  "<font size=$1>$2</font>",
  "<pre>$1</pre>",
  "<div align=\"left\">$1</div>",
  "<div align=\"right\">$1</div>",
  "<div align=\"center\">$1</div>",
	"<sub>$1</sub>",
	"<sup>$</sub>",
	"<table>$1</table>",
	"<tr>$1</tr>",
	"<td>$1</td>",
	"<a href=\"$1\" target=\"_blank\">$1</a>",
  "<a href=\"$1\" target=\"_blank\">$2</a>",
  "<a href=\"$1\" target=\"_blank\">$1</a>",
  "<hr>",
  "<ul>",
  "</ul>",
  "<li>$1",
  "<br><table width=\"100%\" border=0 cellspacing=0 cellpadding=5><tr><td><font face=\"Courier New\" size=2>$1</font></td></tr></table><br>"
);

$version = phpversion( );
if( $version[0] < 3 || ($version[0] == 4 && $version[2] < 1) )
{
  echo "You are running version $version of PHP.  The Help Desk requires at least version 4.1.0.  Please ask your systems administrator to install the latest version of PHP.";
  exit;
}

$pre = $db_prefix;

if( !get_magic_quotes_gpc( ) )
{
  if( isset( $_POST ) )
    while( list( $key, $val ) = each( $_POST ) )
      $_POST[$key] = addslashes( $val );
  
  if( isset( $_GET ) )
    while( list( $key, $val ) = each( $_GET ) )
      $_GET[$key] = addslashes( $val );

  if( isset( $_COOKIE ) )
    while( list( $key, $val ) = each( $_COOKIE ) )
      $_COOKIE[$key] = addslashes( $val );
}

if( !mysql_connect( $db_host, $db_user, $db_password ) )
  die( "Could not connect to MySQL.  Please check the database settings in settings.php" );

mysql_select_db( $db_name );

// If trying to install...
if( !mysql_query( "SELECT COUNT(*) FROM {$pre}user" ) )
{
  if( strtoupper( basename( $_SERVER[PHP_SELF] ) ) != strtoupper( $HD_URL_SETUP ) )
  {
    echo "Please use setup.php to install the help desk";
    exit;
  }

  $INSTALLED = 0;
}
else // Otherwise, setup sessions and help desk path
{
  $INSTALLED = 1;
  
  if( !headers_sent( ) )
    session_start( );
 
  if( !isset( $_SESSION[user][password] ) && isset( $_COOKIE[iv_helpdesk_password] ) )
  {
    $res = mysql_query( "SELECT * FROM {$pre}user WHERE ( email = '{$_COOKIE[iv_helpdesk_login]}' && password = '{$_COOKIE[iv_helpdesk_password]}' )" );
    $row = mysql_fetch_array( $res );
    if( $row && ($row[notify] & $HD_NOTIFY_SAVELOGIN) )
    {
      $_SESSION[login] = $row[email];
      $_SESSION[password] = $row[password];
      $_SESSION[login_type] = $LOGIN_USER;
      $_SESSION[user] = $row;
      $_SESSION[time] = time( );
    }
  }

  if( !get_row_count( "SELECT COUNT(*) FROM {$pre}user WHERE ( id = '{$_SESSION[user][id]}' && password = '{$_SESSION[user][password]}' )" ) )
    $_SESSION[login_type] = $LOGIN_INVALID;
  else if( (time( ) - $_SESSION[time]) > 1800 )
    $_SESSION[login_type] = $LOGIN_INVALID;
  else
    $_SESSION[time] = time( );

  get_helpdesk_path( );
}

function get_helpdesk_path( )
{
  global $pre, $PATH_TO_HELPDESK; 

  $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'helpdeskurl' )" );
  $row = mysql_fetch_array( $res );

  if( trim( $row[0] ) != "" )
  {
    $PATH_TO_HELPDESK = $row[0];
    if( $PATH_TO_HELPDESK[strlen( $PATH_TO_HELPDESK ) - 1] != "/" )
      $PATH_TO_HELPDESK .= "/";
  }
  else
    $PATH_TO_HELPDESK = "";
}

function field( $data )
{
  return htmlspecialchars( stripslashes( $data ) );
}

function get_row_count( $query )
{
  $res = mysql_query( $query );
  $row = mysql_fetch_array( $res );
  return $row[0];
}

function get_options( $options )
{
  global $pre;

  $data = array( );
  for( $i = 0; $i < count( $options ); $i++ )
  {
    $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = '{$options[$i]}' )" );
    $row = mysql_fetch_array( $res );

    $data[$options[$i]] = $row[0];
  }
  return $data;
}

function parse_tags( $text )
{
  global $CODEFROM, $CODETO;

  $text = htmlspecialchars( $text );

  $text = str_replace( "  ", "&nbsp;", $text );
  $text = str_replace( "\t", "&nbsp;&nbsp;&nbsp;", $text );
  $text = str_replace( "\r", "", $text );
  $text = str_replace( "\n", "<br>", $text );  

  $text = preg_replace( $CODEFROM, $CODETO, $text );

  return $text;
}

function parse_no_tags( $text )
{
  $text = htmlspecialchars( $text );

  $text = str_replace( "  ", "&nbsp;", $text );
  $text = str_replace( "\t", "&nbsp;&nbsp;&nbsp;", $text );
  $text = str_replace( "\r", "", $text );
  $text = str_replace( "\n", "<br>", $text );  
  
  return $text;
}

function send_survey( $id )
{
  global $pre;

  $res = mysql_query( "SELECT text FROM {$pre}options WHERE ( name = 'repeatsurvey' )" );
  $row = mysql_fetch_array( $res );
  $repeat = $row[0];

  $res = mysql_query( "SELECT * FROM {$pre}ticket WHERE ( id = '$id' )" );
  $row = mysql_fetch_array( $res );
  
  if( $row )
  {
    if( $repeat )     // Allow repeat surveys, so don't check for email, only same ticket
      $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}survey WHERE ( ticket_id = '$id' )" );
    else
      $exists = get_row_count( "SELECT COUNT(*) FROM {$pre}survey WHERE ( email = '{$row[email]}' || ticket_id = '$id' )" );

    if( !$exists )
    {
      $options = array( "email", "url", "title", "emailheader", "emailfooter", "email_ticket_survey", "email_ticket_survey_subject" );
      $data = get_options( $options );

      $subject = $row[subject];
      $name = $row[name];
      $ticket = $row[ticket_id];
      $email = $row[email];

      eval( "\$sub = \"{$data[email_ticket_survey_subject]}\";" );
      eval( "\$mes = \"{$data[email_ticket_survey]}\";" );
      mail( $row[email], $sub, $mes, "From: {$data[email]}" );
    }
  }
}

function new_ticket_id( )
{
  global $pre;

  $ticket = strtoupper( base_convert( time( ), 10, 16 ) );
  if( get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( ticket_id = '$ticket' )" ) )
  {
    $res = mysql_query( "SELECT ticket_id FROM {$pre}ticket WHERE ( ticket_id NOT LIKE 'M%' ) ORDER BY ticket_id DESC LIMIT 1" );
    $row = mysql_fetch_array( $res );
    $ticket = strtoupper( base_convert( base_convert( $row[0], 16, 10 ) + 1, 10, 16 ) );
  }
 
  return $ticket;
}

/********************************************************** PHP */?>
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

$HD_CURPAGE = $HD_URL_BACKUP;

if( $_SESSION[login_type] == $LOGIN_INVALID || !$_SESSION[user][admin] )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

if( (trim( $db_path_to_mysql ) != "") && ($db_path_to_mysql[strlen( $db_path_to_mysql ) - 1] != "/") )
  $db_path_to_mysql .= "/";

if( $_GET[cmd] == "send" )
{
  $fp = popen( "{$db_path_to_mysql}mysqldump -u {$db_user} -p{$db_password} {$db_name} {$pre}dept {$pre}faq {$pre}options {$pre}pop {$pre}post {$pre}privilege {$pre}reply  {$pre}ticket {$pre}user {$pre}survey {$pre}field", "r" );

  if( !$fp )
    $msg = "<div class=\"errorbox\">Could not run mysqldump.  Check the '\$db_pathtomysql' variable in settings.php</div><br />";
  else
  {
    Header( "Content-type: application/octet-stream" ); 
    Header( "Content-disposition: attachment; filename=helpdesk.sql" ); 
    
    while( !feof( $fp ) )
      echo fread( $fp, 1024 );

    exit;
  }
}
else if( $_POST[cmd] == "import" )
{
  if( $_FILES[backup][size] )
  { 
    exec( "{$db_path_to_mysql}mysql -u {$db_user} -p{$db_password} -f {$db_name} < {$_FILES[backup][tmp_name]}" );
    $msg = "<div class=\"successbox\">Backup restored.</div><br />";
  }
}

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Help Desk Backup</div><br /><?php echo $msg ?>
<table width="100%" bgcolor="#EEEEEE" border="0" cellpadding="5">
<tr><td>
  <div class="graycontainer">
    You can create a backup of the entire help desk, including users, tickets, settings, etc.
    You may also restore a backup.  Note that when you restore a backup, it will append
    to the database.  If you want to start from scratch, first remove all tables associated
    with the help desk.
  </div>
</td></tr>
</table>
<br />
<div class="normal">
<a href="<?php echo $HD_CURPAGE ?>?cmd=send"><b>Send me the database backup file</b></a><br /><br /><hr height="1" size="1" />
<form action="<?php echo $HD_CURPAGE ?>" method="post" enctype="multipart/form-data">
Select the help desk backup file from your hard-drive to restore to the database:<br /><br />
<input type="hidden" name="cmd" value="import" />
<input type="hidden" name="MAX_FILE_SIZE" value="10000000">
<input type="file" name="backup">&nbsp;&nbsp;<input type="submit" value="Restore Backup" />
</form>
</div>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>

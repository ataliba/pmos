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

$HD_CURPAGE = $HD_URL_STATS;

if( $_SESSION[login_type] == $LOGIN_INVALID )
  Header( "Location: {$HD_URL_LOGIN}?redirect=" . urlencode( $HD_CURPAGE ) );

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> Statistics</div><br /><?php echo $msg ?>
<div class="normal">
<?php /************************************************************/
echo "<ul>";
$res = mysql_query( "SELECT id, date, status FROM {$pre}ticket" );

$time_total = 0;
$time_total_replies = 0;
$unanswered = 0;
/*while( $row = mysql_fetch_array( $res ) )
{
  $res_temp = mysql_query( "SELECT date FROM {$pre}post WHERE ( ticket_id = '{$row[0]}' && user_id != '-1' ) ORDER BY date LIMIT 1" );
  if( $row_temp = mysql_fetch_array( $res_temp ) )
  {
    $time_total += ($row_temp[0] - $row[1]);
    $time_total_replies++;
  }
}*/

$unanswered = get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( lastpost = '-1' && status = '$HD_STATUS_OPEN' )" );

echo "<li>There are currently <b>" . mysql_num_rows( $res ) . "</b> ticket(s) on the help desk";
echo "<ul>";
echo "<li><img src=\"browse_nonew.gif\" /> <b>" . get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( status = '$HD_STATUS_OPEN' )" ) . "</b> ticket(s) open";
echo "<li><img src=\"browse_closed.gif\" /> <b>" . get_row_count( "SELECT COUNT(*) FROM {$pre}ticket WHERE ( status != '$HD_STATUS_OPEN' )" ) . "</b> ticket(s) closed or held";

echo "<li><img src=\"browse_newreply.gif\" /> <b>{$unanswered}</b> ticket(s) awaiting a reply";

$res_temp = mysql_query( "SELECT date FROM {$pre}ticket ORDER BY date DESC LIMIT 1" );
if( $row_temp = mysql_fetch_array( $res_temp ) )
  echo "<li>Last ticket created <b>" . date( "F j, Y g:ia T", $row_temp[0] ) . "</b>";

echo "</ul>";

if( $time_total_replies != 0 )
{
  $avg = ($time_total / $time_total_replies);
  echo "<li>Average reply time is: <b>";
  if( (int)($avg / 86400) )
    echo (int)($avg / 86400) . " day(s) ";
  if( (int)(($avg % 86400) / 3600) )
    echo (int)(($avg % 86400) / 3600) . " hour(s) ";
  if( (int)((($avg % 86400) % 3600) / 60 ) )
    echo (int)((($avg % 86400) % 3600) / 60 ) . " minute(s) ";
  echo "</b>";
}

echo "<ul>";
$res_temp = mysql_query( "SELECT post.date, user.name, user.id FROM {$pre}post AS post, {$pre}user AS user WHERE ( post.user_id = user.id && post.user_id != '-1' ) ORDER BY post.date DESC LIMIT 1" );
if( $row_temp = mysql_fetch_array( $res_temp ) )
  echo "<li>Last reply by <a href=\"$HD_URL_USER?cmd=view&id={$row_temp[2]}\"><b>{$row_temp[1]}</b></a> on <b>" . date( "F j, Y g:ia T", $row_temp[0] ) . "</b>";
echo "</ul>";

echo "</ul>";
/********************************************************** PHP */?>
</div>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
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

$HD_CURPAGE = $HD_URL_MANUAL;

include "header.php";
/********************************************************** PHP */?>
<div class="title"><?php echo $script_name ?> User Manual</div><br /><?php echo $msg ?>
<div class="normal">
<a name="#top"></a>
<ol style="">
<li /><a href="#install">Upgrading And Installing</a>
<li /><a href="#departments">Creating/Managing Departments</a>
<li /><a href="#users">Creating Users</a>
<li /><a href="#profile">Your Profile & Preferences</a>
<li /><a href="#settings">General Help Desk Settings</a>
<li /><a href="#autoreplies">Department Auto-Replies</a>
<li /><a href="#emails">Email Customizations</a>
<li /><a href="#email">Email Processing</a>
<li /><a href="#browse">Browsing Tickets</a><br />
<li /><a href="#configure">Configuring The Ticket Form Template</a>
<li /><a href="#header">Using Header/Footer HTML</a>
<li /><a href="#fields">Custom Input Fields</a>
<li /><a href="#tags">Message Tags</a>
<li /><a href="#predefined">Predefined Replies</a>
<li /><a href="#faq">Knowledge Base</a>
<li /><a href="#backups">Help Desk Backups</a>
<li /><a href="#attach">File Attachments & Private Notes</a>
<li /><a href="#surveys">Surveys</a>
<li /><a href="#messaging">Messaging</a>
<li /><a href="#misc">Miscellaneous</a>
</ol>
<a name="#install"></a>
<b>Upgrading And Installing</b><br /><br />
<i>* Note</i> - If you plan on allowing file attachments to tickets, please read the <a href="#attach">File Attachments</a>
section after following the installation instructions below.<br /><br />
<i>Upgrading from a previous version</i><br />
To upgrade from a previous version, extract all of the files from the latest ZIP file to the same location where
the old help desk is installed, overwriting any existing files.  Make sure that the new settings.php file uploaded
is changed to match your current database settings.  Skip the rest of the installation described below,
and simply access the upgrade.php file through your web browser.  This will make any needed changes.
<br /><br />
<i>First or fresh installs</i><br />
<u>Extracting Files</u><br />
After you have downloaded the help desk (which comes as a ZIP file), extract the zip file to a folder of your choice on your server, or extract the zip file on your own computer, and upload the files to your web server.
<br /><br />
<u>Configuring Files</u><br />
The only file that you will need to manually is settings.php.  This file contains information needed to use a MySQL database (username, password, database name, etc.)  Open this file in a text editor and make the necessary changes.
<br /><br />
<u>Setting Up (Creating a global administrator)</u><br />
After you have made the necessary changes to settings.php, go ahead and access the setup.php file in your browser (http://www.yoursite.com/path_to_helpdesk/setup.php). On this page, you will be able to create a master administrator account.  This account cannot be deleted by any other users, and can create other administrator accounts and so forth.  Follow the directions on this page and you will then be able to login.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#departments"></a>
<b>Creating/Managing Departments</b>
<br /><br />
Choose View/Manage Departments from the menu.  All departments will be shown.  You can create separate and any amount of departments (such as for billing, support, accounts, etc.)  Users for each department will be displayed.  The Global (All Departments) department is where global users can be assigned.  A global user can view and work with tickets sent to any department.  When assigning new users, you can also specify that they be an administrator.  For the global department (global administrators), global administrators can create users, and have access to virtually all components of the help desk.  Administrators in all other departments will be able to manage auto-replies for their department and various other department-related issues.
<br /><br />
All departments (except for the global department), can be deleted by global administrators by clicking on the recycle icon next to the name of the department.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#users"></a>
<b>Creating Users</b>
<br /><br />
Choose View/Manage Users from the menu.  You can create a new user by entering their name and email address in the top area.  An email will be sent to him/her with the login information (and a randomly generated password).  Existing users will be displayed below.  Click on their names or the information ('i') icon on the left to view more information about the user.  Global administrators will be able to keep an eye on other users by viewing tickets they have taken part in.  Global administrators can also delete users by clicking on the recycle icon next to a user.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#profile"></a>
<b>Your Profile & Preferences</b>
<br /><br />
Choose Edit Your Profile & Options from the menu.  You will be able to change various options, including your signature, which is displayed the bottom of all posts made to tickets.  Notifications allow users to receive email notifications when new tickets are created within their departments, and also when a customer replies to a ticket that the user is handling.  The SMS email is available to users who have an email address for their cellphone (ie 5555553449@mobile.mycingular.com).  All ticket notifications will be sent as well to the SMS email address if specified.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#settings"></a>
<b>General Help Desk Settings (Global administrators only)</b>
<br /><br />
Choose General Help Desk Settings from the menu.  You should configure a few of these options before using your help desk.  Information on the web page will lead you through this process.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#autoreplies"></a>
<b>Department Auto-Replies</b>
<br /><br />
Auto-replies can be very useful for providing information to those who create tickets that you already have a possible solution for.  When a user creates a ticket containing a specified phrase in the subject line, it will trigger an auto-reply that will contain information that will be sent along with the ticket notification email.
<br /><br />
To do this, select Department Auto-Replies from the menu.  Each department will be shown with its associated auto-replies.  When creating a new auto-reply, you must select the department, the key phrase, and the message used in the auto-reply.  If you select the global department, then your auto-reply will be triggered when a ticket is submitted to ANY department.  Otherwise, it will only trigger for the department you specify.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#emails"></a>
<b>Email Customizations</b>
<br /><br />
Global administrators can modify the important emails sent by the help desk.  The header and footer will be prepended and appened to all emails, and each email itself for ticket creation, ticket notification, SMS emails, and ticket surveys can be completely customized.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#email"></a>
<b>Email Processing</b>
<br /><br />
Email processing is a powerful feature of the help desk that allows you to have tickets created when emails are sent to specific email addresses.  There are 2 unique ways in which you can do this.  In both methods, you must choose 'Email Processing' from the menu and specify an email address (or addresses) that the help desk will be processing, as well as which department tickets each address should be routed to. Once you have done this, you can choose to either have the help desk connect to your POP3 accounts, or to install a .forward file.  The POP3 method is much easier and does not require any setup on the server.
<br /><br />
<u>POP3 Method</u><br /><br />
To use POP3, specify your POP3 email server, as well as the username and password for each email account (you can even specify a POP3 server that isn't local).  The default port for POP3 is 110.  Once you have done this, a link will appear at the top that allows you to 'Fetch Emails and Create Tickets'.  When this link is clicked, the help desk will connect to all the POP3 email accounts you specified, download the emails from your inbox and create tickets. If you specified to do so, these emails will be deleted after the tickets are created.  You must use the 'Fetch Emails and Create Tickets' link every time you want to import new tickets.  Alternatively, you can automate this process by using a cron job.  You must have the PHP stand-alone binary installed (refer to php.net) which allows PHP to be run from the command line.  Here is what you would execute:<br /><br />
<i>cd /path/to/helpdesk</i><br />
<i>/path/to/php/binary&nbsp;&nbsp;/path/to/helpdesk/email-pop.php</i><br /><br />
A good interval for this cron job to execute is every few minutes or so.  You might or might not need the 'cd /path/to/helpdesk/' line in your cronjob.  It just makes sure the current working directory is the help desk directory.<br /><br />
<u>Forwarding Method</u><br /><br />
Many MTA programs, such as sendmail and qmail, have the ability to pipe incoming emails to other programs, rather than to deliver them to a user's inbox.  In most cases, they make use of a .forward file to do so.  We do not support this option because of the vast differences between servers, making it almost impossible to walk you through the setup.  However, if you do know how to create a .forward file and pipe the output to another program, you have a line similar to:<br /><br />
<i>/usr/bin/php&nbsp;-q&nbsp;/path/to/helpdesk/email-forward.php</i><br /><br />
This method is nice in that it automatically creates tickets when emails are sent, but usually require a sysadmin to setup.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#browse"></a>
<b>Browsing Tickets</b>
<br /><br />
To browse tickets, choose Browse from the menu.  By default, all tickets for departments in which you are assigned will be shown in order of latest activity.  You can use the powerful search engine to search for specific tickets, only show tickets that have not been answered, etc.  Tickets can be flagged.  Flagging a ticket for a user or all users will make a flag icon appear next to the ticket.  One of the uses of ticket flagging is to notify a user or all users that a specific ticket is of high importance.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#configure"></a>
<b>Configuring The Ticket Form Template (Used for creating tickets)</b>
<br /><br />
Choose Edit Your Profile & Options from the menu.  The ticket form template will be used on all the web pages that your clients will use to create tickets.  Using the ticket form template options, you can customize your layout and settings to your needs.  
<br /><br />
The logo and title will change the logo used in the upper-left corner of the template and the title of the web pages if you choose not to use the header and footer.
<br /><br />
To completely change the look of your ticket creation template, you can specify the header and footer HTML that will be used.  Note that with using this method, you must specify the title of web pages and include the <html> tags, etc.
<br /><br />
The CSS styles allow you to customize the colors and fonts used for links and other text on the ticket web pages.  You should specify these even when using your own header and footer HTML, because you will still want to customize the content to match your template.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#header"></a>
<b>Using Header And Footer HTML</b>
<br /><br />
Header and footer HTML allow you to completely customize the look of the help desk pages that your clients will interact with.  You can also use PHP code in the header and footer HTML (you must have working PHP knowledge).  When using the header and footer HTML, the links that appear by default at the top of the ticket template will no longer be there (i.e. 'Create Ticket', 'Staff Login', etc.)  You must specify these manually.  The following are the links that you can use in your header/footer HTML:
<br /><br />
<table>
<tr><td><div class="normal"><i>index.php</i></div></td><td><div class="normal">Used for creating new tickets</div></td></tr>
<tr><td><div class="normal"><i>ticketview.php</i></div></td><td><div class="normal">Viewing tickets</div></td></tr>
<tr><td><div class="normal"><i>ticket.php?cmd=lost</i></div></td><td><div class="normal">Looking up tickets</div></td></tr>
<tr><td><div class="normal"><i>login.php</i></div></td><td><div class="normal">Staff login</div></td></tr>
</table>
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#fields"></a>
<b>Custom Input Fields</b>
<br /><br />
If you want to add more fields to the ticket creation form, you can specify them easily under Custom Input Fields.  Simply specify the field name and check the Required? checkbox if you want the field to be required or not.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#tags"></a>
<b>Message Tags</b>
<br /><br />
Message tags allow posts to contain HTML-similar tags to allow things like bolding of text, creating lists, inserting links, etc.  These will cause no harm and can be quite useful.  They can be enabled or disabled by checking the Enable Message Tags checkbox.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#predefined"></a>
<b>Predefined Replies</b>
<br /><br />
Predefined replies allow you to store commonly used replies for later usage when replying to tickets.  When replying to a ticket, simply check the 'Save as a predefined reply named' box and type the name to give the reply.  Later, you may select this predefined reply from the list, and the message area will be filled in automatically.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#faq"></a>
<b>Knowledge Base</b><br /><br />
The knowledge base (or FAQ) allows global administrators to create a powerful, searchable knowledge base.  Select 'Knowledge Base' from the menu.  Categories can be created (as well as subcategories).  Create knowledge base entries within categories.  Users will then be able to search the knowledge base as well as browse categories.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#backups"></a>
<b>Help Desk Backups</b><br /><br />
Select 'Help Desk Backup' from the menu.  Using this feature, you are able to store a backup of the entire help desk-related tables onto your own computer.  If you ever need to restore the help desk database (for instance, if your server crashes), you can do so simply.  All backup data is appended to the database.  This will make sure that any tickets, etc. created after a fresh install will not be removed.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#attach"></a>
<b>File Attachments & Private Notes</b><br /><br />
To allow files to be attached to tickets, first create a subdirectory in the help desk directory named 'files'.  You must also modify the privileges of this folder so that the help desk may write files to it.  You can do this by CHMOD'ing the folder to 777.<br /><br />
Once you have done this, you must then enable the file attachments through the help desk under the 'General Help Desk Settings' section.
<br /><br />
As of version 2.2, staff can attach private notes that only other staff can read to tickets.  This will appear just as a normal post in a ticket thread, except will have a different background color and will not be viewable by the customer.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#surveys"></a>
<b>Surveys</b><br /><br />
As of version 2.0, you can use the powerful surveys built-in to the help desk to get feedback from your customers.  Global administrators can define a list of up to 10 questions that will be rated from 1 (poor) to 5 (excellent), as well as an extra box for comments.  Other options on the survey page allow you to automatically send out surveys when tickets are closed, and to send surveys to users more than once if they create more than one ticket.  Each question will show average ratings, how many customers chose each rating, as well as a list of the individual surveys.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#messaging"></a>
<b>Messaging</b><br /><br />
As of version 2.2, staff can send messages to one another (to a single user, multiple users, and/or departments).  You can compose and read messages thru the message center link that appears under the miscellaneous links.
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<a name="#misc"></a>
<b>Linking To The Ticket Creation Form, Auto-setting The Subject, Etc.</b>
<br /><br />
You can easily link to the ticket creation form by using the URL http://www.yoursite.com/path_to_helpdesk/index.php. You can also automatically have the subject text box and department fields set by using the following link format:
<br /><br />
http://www.yoursite.com/path_to_helpdesk/index.php?subject=YOUR_SUBJECT&department=DEPARTMENT_NAME
<br /><br /><a href="#top"><b>&lt;&lt; Back To Top</b></a>
<br /><br />
<b>If you have any problems, please visit http://www.heathcosoft.com and create a support ticket through our help desk.</b>
</div>
<?php /************************************************************/
include "footer.php";
/********************************************************** PHP */?>
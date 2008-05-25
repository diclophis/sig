<?php

/*
Plugin Name: SiG Directory System
Plugin URI: http://sig.sf.net/wordpress/
Description: To provide a solution for member Directories
Version: 0.0.3
Author: Jon Bardin
Author URI: http://sig.sf.net

Copyright 2005  Jon Bardin  (email : diclophis@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

$sys_dbhost = 'localhost';
$sys_dbuser = 'root';
$sys_dbpasswd = '';
$sys_dbname = 'sig';
$table_prefix  = '';

define('ABSPATH', '/home/jbardin/sig');

define('CALENDAR_ROOT', ABSPATH.'/include/Calendar/');

require_once('include/Calendar/Factory.php');
require_once('include/Calendar/Week.php');
require_once('include/Calendar/Util/Textual.php');
require_once('include/Calendar/Util/Uri.php');


require_once('models/sig.model.php');
require_once('models/sig.session.php');
require_once('models/sig.admin.model.php');

require_once('controllers/sig.controller.php');
require_once('controllers/sig.admin.controller.php');
require_once('controllers/sig.plugin.controller.php');
require_once('views/sig.view.php');
require_once('views/sig.html.view.php');
require_once('views/sig.admin.view.php');
require_once('views/sig.plugin.view.php');

require_once('include/libNode.php');
require_once('include/libDebug.php');
require_once('include/libQuery.php');
require_once('include/libDatabase.php');
require_once('include/libProperty.php');
require_once('include/libProperty_Type.php');

require_once('include/modules/modSystem_Node.php');

require_once('include/modules/modDeleteSystem_Node.php');
require_once('include/modules/modDeleted_Node.php');
require_once('include/modules/modFolder_Node.php');
require_once('include/modules/modDocumentSystem_Node.php');
require_once('include/modules/modDocument_Node.php');
require_once('include/modules/modMandel_Node.php');
require_once('include/modules/modContent_Node.php');

require_once('include/modules/modGallerySystem_Node.php');
require_once('include/modules/modGallery_Node.php');
require_once('include/modules/modImage_Node.php');

require_once('include/modules/modPollSystem_Node.php');
require_once('include/modules/modPoll_Node.php');
require_once('include/modules/modVote_Node.php');

require_once('include/modules/modWordpressPageSystem_Node.php');
require_once('include/modules/modWordpressPage_Node.php');

require_once('include/modules/modSnippetSystem_Node.php');
require_once('include/modules/modSnippet_Node.php');

require_once('include/modules/modForumSystem_Node.php');
require_once('include/modules/modForum_Node.php');
require_once('include/modules/modThread_Node.php');

require_once('include/modules/modBusinessDirectorySystem_Node.php');
require_once('include/modules/modBusinessDirectory_Node.php');
require_once('include/modules/modBusinessDirectoryCategories_Node.php');
require_once('include/modules/modBusinessDirectoryCategory_Node.php');
require_once('include/modules/modBusinessDirectoryEntries_Node.php');
require_once('include/modules/modBusinessDirectoryEntry_Node.php');

require_once('include/modules/modMandel_Node.php');

require_once('include/modules/modCalendarSystem_Node.php');
require_once('include/modules/modCalendar_Node.php');
require_once('include/modules/modEvent_Node.php');

?>

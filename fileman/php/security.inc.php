<?php
/*
  RoxyFileman - web based file manager. Ready to use with CKEditor, TinyMCE. 
  Can be easily integrated with any other WYSIWYG editor or CMS.

  Copyright (C) 2013, RoxyFileman.com - Lyubomir Arsov. All rights reserved.
  For licensing, see LICENSE.txt or http://RoxyFileman.com/license

  This program is free software: you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation, either version 3 of the License.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program.  If not, see <http://www.gnu.org/licenses/>.

  Contact: Lyubomir Arsov, liubo (at) web-lobby.com
*/

define('_JEXEC', 1);
$JPB = realpath(dirname(__FILE__).'/../../../../../');
$APP = 'site';
if (isset($_COOKIE['rjck_rfmr']) && $_COOKIE['rjck_rfmr']) {
	$JPB .= '/administrator';
	$APP = 'administrator';
}
define('JPATH_BASE', $JPB);
 
require_once ( JPATH_BASE. '/includes/defines.php' );
require_once ( JPATH_BASE. '/includes/framework.php' );
$mainframe = JFactory::getApplication($APP);
$mainframe->initialise();

$session = JFactory::getSession();
$_SESSION['RJCK_RFMR'] = $session->get('RJCK_RFMR');
$_SESSION['RJCK_RFMR'] or die('No Access Allowed');

list($jRoot,$fPath) = explode(':',$_SESSION['RJCK_RFMR']);
$_SESSION['RJCK_RFMR'] = $jRoot.$fPath;

function checkAccess($action){
  if(!session_id())
    session_start();
}

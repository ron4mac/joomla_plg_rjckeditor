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
 
require_once JPATH_BASE.'/includes/defines.php';
require_once JPATH_BASE.'/includes/framework.php';

use \Joomla\CMS\Factory; 
use \Joomla\CMS\Table\Table;

$container = Factory::getContainer();
$container->alias('session.web', 'session.web.site')
->alias('session', 'session.web.site')
->alias('JSession', 'session.web.site')
->alias(\Joomla\CMS\Session\Session::class, 'session.web.site')
->alias(\Joomla\Session\Session::class, 'session.web.site')
->alias(\Joomla\Session\SessionInterface::class, 'session.web.site');
$app = $container->get(\Joomla\CMS\Application\SiteApplication::class);
Factory::$application = $app;

$app->setHeader('Cross-Origin-Opener-Policy','same-origin-allow-popups',true);

$session = Factory::getSession();
$_SESSION['RJCK_RFMR'] = $session->get('RJCK_RFMR');
$_SESSION['RJCK_RFMR'] or die('No Access Allowed');

list($jRoot,$fPath) = explode(':',$_SESSION['RJCK_RFMR']);
$_SESSION['RJCK_RFMR'] = $jRoot.$fPath;

function checkAccess($action){
  if (!session_id())
    session_start();
}

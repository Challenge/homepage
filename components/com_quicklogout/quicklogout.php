<?php
/**
 * @version     1.7.0
 * @package     com_quicklogout
 * @copyright   Copyright (C) 2011. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 * @author      Created by com_combuilder - http://www.notwebdesign.com
 */

defined('_JEXEC') or die;

// Include dependancies
jimport('joomla.application.component.controller');


// lets try to make this simple

$loloc = "Location: index.php?option=com_users&task=user.logout&";
$loloc .= JUtility::getToken();
$loloc .= "=1&return=";
$loloc .= base64_encode(JURI::root() . "\n");
header( $loloc );



// Execute the task.
//$controller	= JController::getInstance('Quicklogout');
//$controller->execute(JRequest::getVar('task',''));
//$controller->redirect();

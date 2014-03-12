<?php
/**
 * @package             DIKULAN
 * @subpackage          Components
 * @author              Ektorus
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import joomla controller library
jimport('joomla.application.component.controller');

// Get an instance of the controller prefixed by dikulan-event
$controller = JController::getInstance('Event');

// Perform the Request task
$controller->execute(JRequest::getCmd('task'));

// Redirect if set by the controller
$controller->redirect();

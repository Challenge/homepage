<?php
class JoomlaScript {
	public function __construct() {
		define( '_JEXEC', 1 );
		define( 'JPATH_BASE', realpath(dirname(__FILE__)));
		define( 'DS', DIRECTORY_SEPARATOR );

		require_once ( JPATH_BASE .DS.'includes'.DS.'defines.php' );
		require_once ( JPATH_BASE .DS.'includes'.DS.'framework.php' );

		jimport( 'joomla.access.access' );
		jimport( 'joomla.user.helper' );

		$mainframe =& JFactory::getApplication('site');
		$mainframe->initialise();
	}

	public function joomlaGetUserID() {
		$user =& JFactory::getUser();
		return $user->id;
	}
	
	public function joomlaGetUserGroups($userID = 0, $groupNames = 0) {
		if ($groupNames) {
			$groups = JUserHelper::getUserGroups($userID);
		} else {
			$groups = JAccess::getGroupsByUser($userID);
		}
		return $groups;
	}
}
?>
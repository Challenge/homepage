<?php
/**
 * @package             DIKULAN
 * @subpackage          Components
 * @author              Ektorus
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted access');

// import Joomla view library
jimport('joomla.application.component.view');

/**
 * HTML View class for the dikulan-event Component
 */
class EventViewEvent extends JView
{
        protected $dbcon;

        function getServerPath() {
                $list = get_html_translation_table(HTML_ENTITIES);
                unset($list['&']);
                unset($list['?']);
                $search = array_keys($list);
                $values = array_values($list);
                return str_replace($search, $values, $_SERVER['REQUEST_URI']);
        }

        function getDatabaseConnection() {
                return $this->dbcon;
        }

	// Overwriting JView display method
	function display($tpl = null) 
	{
                try {
                	$server = "localhost";
                        $username = "dikuland_dikulan";
                        $password = "omgAltsaaHvadTaenkerIpaa";
                        $database = "dikuland_dikulan";
                        
                        $this->dbcon = new PDO("mysql:dbname=" . $database . ";host=" . $server, $username, $password);
                	$this->dbcon->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                }
                catch (PDOException $e) {
                	echo "Could not establish connection to the database.";
                	echo "This is unfortunately a fatal error that cannot be ignored and further execution has been halted." . "<br />";
                	echo "Please contact an administrator immediately";
                	echo "If you don't know any administrators, please visit the contact page." . "<br />";
                	echo "<br />" . "Please give the administrator the following information:" . "<br />";
                	echo $e;
                	die();
                }

                $doc = JFactory::getDocument();
                $doc->addStyleSheet('media/com_dikulan-event/css/site.stylesheet.css');

		// Display the view
		parent::display($tpl);
	}
}






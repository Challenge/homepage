<?php
/**
 *  @Copyright
 *  @package        EasyCalcCheck Plus
 *  @author         Viktor Vogel {@link http://www.kubik-rubik.de}
 *  @version        2.5-1
 *  @date           Created on 24-Jan-2012
 *  @link           Project Site {@link http://joomla-extensions.kubik-rubik.de/ecc-easycalccheck-plus}
 *
 *  @license GNU/GPL
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

defined('_JEXEC') or die('Restricted access');

class plgSystemEasyCalcCheckPlus extends JPlugin
{
    protected $_load_ecc;
    protected $_load_ecc_check;
    protected $_session;
    protected $_extension_info;
    protected $_redirect_url;

    function __construct(&$subject, $config)
    {
        $this->loadLanguage('plg_system_easycalccheckplus', JPATH_ADMINISTRATOR);

        // Check Joomla version
        $version = new JVersion();

        if($version->PRODUCT == 'Joomla!' AND $version->RELEASE != '2.5')
        {
            JError::raiseWarning(100, JText::_('PLG_ECC_WRONGJOOMLAVERSION'));
            return;
        }

        parent::__construct($subject, $config);

        $this->_load_ecc = false;
        $this->_load_ecc_check = false;
        $this->_session = JFactory::getSession();
        $this->_extension_info = array();

        $this->_redirect_url = $this->_session->get('redirect_url', null, 'easycalccheck');
        $this->_session->clear('redirect_url', 'easycalccheck');

        if(empty($this->_redirect_url))
        {
            $this->_redirect_url = JFactory::getURI()->toString();
        }
    }

    public function onAfterRender()
    {
        if($this->_load_ecc == true)
        {
            $option = JRequest::getWord('option');
            $view = JRequest::getWord('view');

            // Google Translator Fix
            $this->loadLanguage('', JPATH_ADMINISTRATOR);

            // Read in content of the output
            $body = JResponse::getBody();

            // Fill in form input values if the check failed
            if($this->params->get('autofill_values') AND JRequest::getCmd('eccp_err', '', 'get') == 'check_failed')
            {
                $this->fill_form($body);
            }

            // Get form of extension
            $pattern_form = '@'.$this->_extension_info[1].'@isU';
            preg_match($pattern_form, $body, $match_extension);

            if(empty($match_extension))
            {
                JError::raiseWarning(100, JText::_('PLG_ECC_WARNING_FORMNOTFOUND'));
            }

            // Hidden field
            if($this->params->get('type_hidden') AND !empty($match_extension))
            {
                $pattern_form = '@'.$this->_extension_info[1].'@isU';
                preg_match($pattern_form, $body, $match_extension);

                $pattern_search_string = '@'.$this->_extension_info[2].'@isU';
                preg_match_all($pattern_search_string, $match_extension[0], $matches);

                if(empty($matches[0]))
                {
                    JError::raiseWarning(100, JText::_('PLG_ECC_WARNING_NOHIDDENFIELD'));
                }
                else
                {
                    $count = mt_rand(0, count($matches[0]) - 1);
                    $search_string_hidden = $matches[0][$count];

                    // Generate random variable
                    $this->_session->set('hidden_field', $this->random(), 'easycalccheck');
                    $this->_session->set('hidden_field_label', $this->random(), 'easycalccheck');

                    // Line width for obfuscation
                    $input_size = 30;

                    $add_string = '<label class="'.$this->_session->get('hidden_class', null, 'easycalccheck').'" for="'.$this->_session->get('hidden_field_label', null, 'easycalccheck').'"></label><input type="text" id="'.$this->_session->get('hidden_field_label', null, 'easycalccheck').'" name="'.$this->_session->get('hidden_field', null, 'easycalccheck').'" size="'.$input_size.'" class="inputbox '.$this->_session->get('hidden_class', null, 'easycalccheck').'" />';

                    if(isset($search_string_hidden))
                    {
                        $body = str_replace($search_string_hidden, $add_string.$search_string_hidden, $body);
                    }
                }
            }

            // Encode fields - only in core components
            if($this->params->get('encode'))
            {
                if($option == 'com_contact' OR $option == 'com_users')
                {
                    // Set random variable
                    $random = $this->random();
                    $random2 = $this->random();
                    $random3 = $this->random();
                    $random4 = $this->random();
                    $random5 = $this->random();
                    $random6 = $this->random();

                    if($option == 'com_contact')
                    {
                        $this->_session->set('jform[contact_name]', $random, 'easycalccheck');
                        $this->_session->set('jform[contact_email]', $random2, 'easycalccheck');
                        $this->_session->set('jform[contact_subject]', $random3, 'easycalccheck');
                        $this->_session->set('jform[contact_message]', $random4, 'easycalccheck');

                        $name_old = array('name="jform[contact_name]"', 'name="jform[contact_email]"', 'name="jform[contact_subject]"', 'name="jform[contact_message]"');
                        $name_new = array('name="'.$random.'"', 'name="'.$random2.'"', 'name="'.$random3.'"', 'name="'.$random4.'"');

                        $id_old = array('jform_contact_email_copy', 'jform_contact_name', 'jform_contact_email', 'jform_contact_emailmsg', 'jform_contact_message');
                        $id_new = array($this->random(), $this->random(), $this->random(), $this->random(), $this->random());

                        $body = str_replace($name_old, $name_new, $body);
                        $body = str_replace($id_old, $id_new, $body);
                    }
                    elseif($option == 'com_users' AND $view == 'registration')
                    {
                        $this->_session->set('jform[name]', $random, 'easycalccheck');
                        $this->_session->set('jform[username]', $random2, 'easycalccheck');
                        $this->_session->set('jform[email1]', $random3, 'easycalccheck');
                        $this->_session->set('jform[email2]', $random4, 'easycalccheck');
                        $this->_session->set('jform[password1]', $random5, 'easycalccheck');
                        $this->_session->set('jform[password2]', $random6, 'easycalccheck');

                        $name_old = array('name="jform[name]"', 'name="jform[username]"', 'name="jform[email1]"', 'name="jform[email2]"', 'name="jform[password1]"', 'name="jform[password2]"');
                        $name_new = array('name="'.$random.'"', 'name="'.$random2.'"', 'name="'.$random3.'"', 'name="'.$random4.'"', 'name="'.$random5.'"', 'name="'.$random6.'"');

                        $id_old = array('jform_name', 'jform_username', 'jform_password1', 'jform_password2', 'jform_email1', 'jform_email2');
                        $id_new = array($this->random(), $this->random(), $this->random(), $this->random(), $this->random(), $this->random());

                        $body = str_replace($name_old, $name_new, $body);
                        $body = str_replace($id_old, $id_new, $body);
                    }
                }
            }

            // Calc check
            if(($this->params->get('type_calc') OR $this->params->get('recaptcha') OR $this->params->get('question')) AND !empty($match_extension))
            {
                // Without overrides
                $pattern_output = '@'.$this->_extension_info[3].'@isU';

                if(preg_match($pattern_output, $match_extension[0], $matches))
                {
                    $search_string_output = $matches[0];
                }
                else // With overrides
                {
                    // Artisteer Template
                    $pattern = '@<span class="art-button-wrapper">@isU';

                    if(preg_match($pattern, $match_extension[0], $matches))
                    {
                        $search_string_output = $matches[0];
                    }

                    // Rockettheme Template
                    $pattern = '@<div class="readon">@isU';

                    if(preg_match($pattern, $match_extension[0], $matches))
                    {
                        $search_string_output = $matches[0];
                    }

                    if(!isset($search_string_output))
                    {
                        // Alternative search string from settings
                        $string_alternative = $this->params->get('string_alternative');

                        if(!empty($string_alternative))
                        {
                            $pattern = '@'.$string_alternative.'@isU';

                            if(preg_match($pattern, $match_extension[0], $matches))
                            {
                                $search_string_output = $matches[0];
                            }
                        }

                        if(!isset($search_string_output))
                        {
                            $pattern = '@<[^>]*type="submit".*>@isU';

                            if(preg_match($pattern, $match_extension[0], $matches))
                            {
                                $search_string_output = $matches[0];
                            }
                        }
                    }
                }

                $add_string = '<!-- EasyCalcCheck Plus - Joomla! 2.5 --><div id="easycalccheckplus">';

                if($this->params->get('type_calc'))
                {
                    $this->_session->set('spamcheck', $this->random(), 'easycalccheck');
                    $this->_session->set('rot13', mt_rand(0, 1), 'easycalccheck');

                    // Determine operator
                    if($this->params->get('operator') == 2)
                    {
                        $tcalc = mt_rand(1, 2);
                    }
                    elseif($this->params->get('operator') == 1)
                    {
                        $tcalc = 2;
                    }
                    else
                    {
                        $tcalc = 1;
                    }

                    // Determine max. operand
                    $max_value = $this->params->get('max_value', 20);

                    if(($this->params->get('negativ') == 0) AND ($tcalc == 2))
                    {
                        $spam_check_1 = mt_rand($max_value / 2, $max_value);
                        $spam_check_2 = mt_rand(1, $max_value / 2);
                        if($this->params->get('operand') == 3)
                        {
                            $spam_check_3 = mt_rand(0, $spam_check_1 - $spam_check_2);
                        }
                    }
                    else
                    {
                        $spam_check_1 = mt_rand(1, $max_value);
                        $spam_check_2 = mt_rand(1, $max_value);
                        if($this->params->get('operand') == 3)
                        {
                            $spam_check_3 = mt_rand(0, $max_value);
                        }
                    }

                    if($tcalc == 1) // Addition
                    {
                        if($this->_session->get('rot13', null, 'easycalccheck') == 1) // ROT13 coding
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $this->_session->set('spamcheckresult', str_rot13(base64_encode($spam_check_1 + $spam_check_2)), 'easycalccheck');
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $this->_session->set('spamcheckresult', str_rot13(base64_encode($spam_check_1 + $spam_check_2 + $spam_check_3)), 'easycalccheck');
                            }
                        }
                        else
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $this->_session->set('spamcheckresult', base64_encode($spam_check_1 + $spam_check_2), 'easycalccheck');
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $this->_session->set('spamcheckresult', base64_encode($spam_check_1 + $spam_check_2 + $spam_check_3), 'easycalccheck');
                            }
                        }
                    }
                    elseif($tcalc == 2) // Subtraction
                    {
                        if($this->_session->get('rot13', null, 'easycalccheck') == 1)
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $this->_session->set('spamcheckresult', str_rot13(base64_encode($spam_check_1 - $spam_check_2)), 'easycalccheck');
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $this->_session->set('spamcheckresult', str_rot13(base64_encode($spam_check_1 - $spam_check_2 - $spam_check_3)), 'easycalccheck');
                            }
                        }
                        else
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $this->_session->set('spamcheckresult', base64_encode($spam_check_1 - $spam_check_2), 'easycalccheck');
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $this->_session->set('spamcheckresult', base64_encode($spam_check_1 - $spam_check_2 - $spam_check_3), 'easycalccheck');
                            }
                        }
                    }

                    $add_string .= '<div><label for="'.$this->_session->get('spamcheck', null, 'easycalccheck').'">';

                    $add_string .= JText::_('PLG_ECC_SPAMCHECK').': ';
                    if($tcalc == 1)
                    {
                        if($this->params->get('converttostring'))
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $add_string .= $this->converttostring($spam_check_1).' '.JText::_('PLG_ECC_PLUS').' '.$this->converttostring($spam_check_2).' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $add_string .= $this->converttostring($spam_check_1).' '.JText::_('PLG_ECC_PLUS').' '.$this->converttostring($spam_check_2).' '.JText::_('PLG_ECC_PLUS').' '.$this->converttostring($spam_check_3).' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                        }
                        else
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $add_string .= $spam_check_1.' '.JText::_('PLG_ECC_PLUS').' '.$spam_check_2.' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $add_string .= $spam_check_1.' '.JText::_('PLG_ECC_PLUS').' '.$spam_check_2.' '.JText::_('PLG_ECC_PLUS').' '.$spam_check_3.' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                        }
                    }
                    elseif($tcalc == 2)
                    {
                        if($this->params->get('converttostring'))
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $add_string .= $this->converttostring($spam_check_1).' '.JText::_('PLG_ECC_MINUS').' '.$this->converttostring($spam_check_2).' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $add_string .= $this->converttostring($spam_check_1).' '.JText::_('PLG_ECC_MINUS').' '.$this->converttostring($spam_check_2).' '.JText::_('PLG_ECC_MINUS').' '.$this->converttostring($spam_check_3).' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                        }
                        else
                        {
                            if($this->params->get('operand') == 2)
                            {
                                $add_string .= $spam_check_1.' '.JText::_('PLG_ECC_MINUS').' '.$spam_check_2.' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                            elseif($this->params->get('operand') == 3)
                            {
                                $add_string .= $spam_check_1.' '.JText::_('PLG_ECC_MINUS').' '.$spam_check_2.' '.JText::_('PLG_ECC_MINUS').' '.$spam_check_3.' '.JText::_('PLG_ECC_EQUALS').' ';
                            }
                        }
                    }
                    $add_string .= '</label>';
                    $add_string .= '<input type="text" name="'.$this->_session->get('spamcheck', null, 'easycalccheck').'" id="'.$this->_session->get('spamcheck', null, 'easycalccheck').'" size="3" class="inputbox '.$this->random().' validate-numeric required" value="" required="required" />';
                    $add_string .= '</div>';

                    // Show warnings
                    if($this->params->get('warn_ref') AND !$this->params->get('autofill_values'))
                    {
                        $add_string .= '<p><img src="'.JURI::root().'plugins/system/easycalccheckplus/easycalccheckplus/warning.png" alt="'.JText::_('PLG_ECC_WARNING').'" /> ';
                        $add_string .= '<strong>'.JText::_('PLG_ECC_WARNING').'</strong><br /><small>'.JText::_('PLG_ECC_WARNINGDESC').'</small>';

                        if($this->params->get('converttostring'))
                        {
                            $add_string .= '<br /><small>'.JText::_('PLG_ECC_CONVERTWARNING').'</small><br />';
                        }

                        $add_string .= '</p>';
                    }
                    elseif($this->params->get('converttostring'))
                    {
                        $add_string .= '<p><small>'.JText::_('PLG_ECC_CONVERTWARNING').'</small></p>';
                    }
                }

                // ReCaptcha
                if($this->params->get('recaptcha') AND $this->params->get('recaptcha_publickey') AND $this->params->get('recaptcha_privatekey'))
                {
                    require_once(dirname(__FILE__).DS.'easycalccheckplus'.DS.'recaptchalib.php');
                    $publickey = $this->params->get('recaptcha_publickey');

                    $add_string .= recaptcha_get_html($publickey).'<br />';
                }

                // Own Question
                if($this->params->get('question') AND $this->params->get('question_q') AND $this->params->get('question_a'))
                {
                    $this->_session->set('question', $this->random(), 'easycalccheck');

                    $size = strlen($this->params->get('question_a')) + mt_rand(0, 2);

                    $add_string .= '<p>'.$this->params->get('question_q').' <input type="text" name="'.$this->_session->get('question', null, 'easycalccheck').'" id="'.$this->_session->get('question', null, 'easycalccheck').'" size="'.$size.'" class="inputbox '.$this->random().'" value="" /> *</p>';
                }

                if($this->params->get('poweredby') == 1)
                {
                    $add_string .= '<div class="protectedby"><a href="http://joomla-extensions.kubik-rubik.de/" title="EasyCalcCheck Plus for Joomla! 2.5 - No more spam in Joomla! forms!" target="_blank">'.JText::_('PLG_ECC_PROTECTEDBY').'</a></div>';
                }

                $add_string .= '</div>';

                if(isset($search_string_output))
                {
                    $body = str_replace($search_string_output, $add_string.$search_string_output, $body);
                }

            }

            // Set body
            JResponse::setBody($body);

            // Time Lock
            if($this->params->get('type_time'))
            {
                $this->_session->set('time', time(), 'easycalccheck');
            }

            // Get IP Address
            $this->_session->set('ip', getenv('REMOTE_ADDR'), 'easycalccheck');

            // Set session variable for error output - Phoca Guestbook / Easybook Reloaded
            if($option == 'com_phocaguestbook')
            {
                $this->_session->set('phocaguestbook', 0, 'easycalccheck');
            }
            if($option == 'com_easybookreloaded')
            {
                $this->_session->set('easybookreloaded', 0, 'easycalccheck');
            }

            // Set redirect url
            $this->_session->set('redirect_url', JFactory::getURI()->toString(), 'easycalccheck');
        }
    }

    function onAfterRoute()
    {
        // Check if ECC has to be loaded
        $option = JRequest::getWord('option');
        $view = JRequest::getWord('view');
        $task = JRequest::getCmd('task');
        $func = JRequest::getWord('func');
        $layout = JRequest::getWord('layout');

        $this->loadEcc($option, $task, $view, $func, $layout);

        if($this->_load_ecc_check == true OR $this->_load_ecc == true)
        {
            // Clean cache of component if ECC+ has to be loaded
            $cache = JFactory::getCache($option);
            $cache->clean();
        }

        if($this->_load_ecc_check == true)
        {
            // Decode all input fields
            if($this->params->get('encode'))
            {
               $this->decodeFields($option, $task);
            }

            // Save entered values in session for autofill
            if($this->params->get('autofill_values'))
            {
                $this->saveData();
            }

            // Call checks for forms
            $this->callChecks($option, $task);
        }

        if($this->_load_ecc == true)
        {
            // Load error notice if needed
            if(JRequest::getCmd('eccp_err', '', 'get') == 'check_failed')
            {
                if(($option == 'com_phocaguestbook' AND $this->_session->get('phocaguestbook', null, 'easycalccheck') == 0) OR ($option == 'com_easybookreloaded' AND $this->_session->get('easybookreloaded', null, 'easycalccheck') == 0))
                {
                    // No message - message is raised by components
                }
                else
                {
                    JError::raiseWarning(100, JText::_('PLG_ECC_YOUHAVENOTRESOLVEDOURSPAMCHECK'));
                }
            }

            // Write head data
            $head = array();
            $head[] = '<style type="text/css">#easycalccheckplus {margin: 8px 0 !important; padding: 2px !important;}</style>';

            if($this->params->get('poweredby'))
            {
                $head[] = '<style type="text/css">.protectedby {font-size: x-small !important; text-align: right !important;}</style>';
            }

            if($this->params->get('type_hidden'))
            {
                $this->_session->set('hidden_class', $this->random(), 'easycalccheck');
                $head[] = '<style type="text/css">.'.$this->_session->get('hidden_class', null, 'easycalccheck').' {display: none !important;}</style>';

                if($this->params->get('foxcontact') AND $option == "com_foxcontact")
                {
                    $head[] = '<style type="text/css">label.'.$this->_session->get('hidden_class', null, 'easycalccheck').' {margin: 0 !important; padding: 0 !important;}</style>';
                }
            }

            if($this->params->get('kunena') AND $this->params->get('recaptcha') AND $option == "com_kunena")
            {
                $head[] = '<style type="text/css">div#recaptcha_area{margin: auto !important;}</style>';
            }

            if($this->params->get('recaptcha_theme'))
            {
                if($this->params->get('recaptcha_theme') == 1)
                {
                    $theme = 'white';
                }
                elseif($this->params->get('recaptcha_theme') == 2)
                {
                    $theme = 'blackglass';
                }
                elseif($this->params->get('recaptcha_theme') == 3)
                {
                    $theme = 'clean';
                }

                $head[] = '<script type="text/javascript">var RecaptchaOptions = { theme : "'.$theme.'" };</script>';
            }

            $head = "\n".implode("\n", $head)."\n";
            $document = JFactory::getDocument();

            if($document->getType() == 'html')
            {
                $document->addCustomTag($head);
            }
        }
    }

    function onAfterInitialise()
    {
        // Clean page cache (system cache plugin)
        $cache = JFactory::getCache();
        $cache->remove($cache->makeId(), 'page');

        // Bot-Trap
        // Further informations: http://www.bot-trap.de
        // File has to be named page.restrictor.php and should be saved in plugins/system/bottrap
        if($this->params->get('bottrap'))
        {
            $app = JFactory::getApplication();

            if($app->isAdmin())
            {
                $path = '../plugins/system/easycalccheckplus/bottrap/';
            }
            else
            {
                $path = 'plugins/system/easycalccheckplus/bottrap/';
            }

            if(file_exists($path.'page.restrictor.php'))
            {
                if($this->params->get('btWhitelistIP'))
                {
                    $btWhitelistIP = str_replace(',', '|', $this->params->get('btWhitelistIP'));
                    define('PRES_WHITELIST_IP', $btWhitelistIP);
                }

                if($this->params->get('btWhitelistIPRange'))
                {
                    $btWhitelistIPRange = str_replace(',', '|', $this->params->get('btWhitelistIPRange'));
                    define('PRES_WHITELIST_IPR', $btWhitelistIPRange);
                }

                if($this->params->get('btWhitelistUA'))
                {
                    $btWhitelistUA = str_replace(',', '|', $this->params->get('btWhitelistUA'));
                    define('PRES_WHITELIST_UA', $btWhitelistUA);
                }

                if($this->params->get('btBlacklistIP'))
                {
                    $btBlacklistIP = str_replace(',', '|', $this->params->get('btBlacklistIP'));
                    define('PRES_BLACKLIST_IP', $btBlacklistIP);
                }

                if($this->params->get('btBlacklistIPRange'))
                {
                    $btBlacklistIPRange = str_replace(',', '|', $this->params->get('btBlacklistIPRange'));
                    define('PRES_BLACKLIST_IPR', $btBlacklistIPRange);
                }

                if($this->params->get('btBlacklistUA'))
                {
                    $btBlacklistUA = str_replace(',', '|', $this->params->get('btBlacklistUA'));
                    define('PRES_BLACKLIST_UA', $btBlacklistUA);
                }

                include_once($path.'page.restrictor.php');
            }
            else
            {
                JError::raiseWarning(100, JText::_('PLG_ECC_ERRORBOTTRAP'));
            }
        }

        // Credit: Marco's SQL Injection Plugin
        // Further informations: http://www.mmleoni.net/sql-iniection-lfi-protection-plugin-for-joomla
        if($this->params->get('sqlinjection-lfi'))
        {
            $mainframe = JFactory::getApplication();
            $p_dbprefix = $mainframe->getCfg('dbprefix');
            $p_errorCode = 500;
            $p_errorMsg = 'Internal Server Error - SQL Injection detected!';
            $p_nameSpaces = 'GET,POST,REQUEST';

            foreach(explode(',', $p_nameSpaces) as $nsp)
            {
                switch($nsp)
                {
                    case 'GET':
                        $nameSpace = $_GET;
                        break;

                    case 'POST':
                        $nameSpace = $_POST;
                        break;

                    case 'REQUEST':
                        $nameSpace = $_REQUEST;
                        break;
                }

                foreach($nameSpace as $k => $v)
                {
                    if(is_numeric($v))
                    {
                        continue;
                    }

                    if(is_array($v))
                    {
                        continue;
                    }

                    $a = preg_replace('@/\*.*?\*/@s', ' ', $v);

                    if(preg_match('@UNION(?:\s+ALL)?\s+SELECT@i', $a))
                    {
                        JError::raiseError($p_errorCode, $p_errorMsg);
                        return;
                    }

                    $ta = array('/(\s+|\.|,)`?(#__)/', '/(\s+|\.|,)`?(jos_)/i', "/(\s+|\.|,)`?({$p_dbprefix}_)/i");

                    foreach($ta as $t)
                    {
                        if(preg_match($t, $v))
                        {
                            JError::raiseError($p_errorCode, $p_errorMsg);
                            return;
                        }
                    }

                    if(in_array($k, array('controller', 'view', 'model', 'template')))
                    {
                        $recurse = str_repeat('\.\.\/', 2);

                        while(preg_match("@$recurse@", $v))
                        {
                            JError::raiseError($p_errorCode, $p_errorMsg);
                            return;
                        }
                    }

                    unset($v);
                }
            }
        }

        // Backend protection
        if($this->params->get('backendprotection'))
        {
            $app = JFactory::getApplication();

            if($app->isAdmin())
            {
                $user = JFactory::getUser();

                if($user->guest)
                {
                    $token = $this->params->get('token');
                    $request = JRequest::get();
                    $tokensession = $this->_session->get('token', null, 'easycalccheck');

                    if(!isset($request['token']))
                    {
                        $request['token'] = 0;
                    }

                    if(!isset($tokensession))
                    {
                        $tokensession = $this->_session->set('token', 0, 'easycalccheck');
                    }

                    if(utf8_encode($request['token']) == $token) // Conversion to UTF8 (german umlaute)
                    {
                        $tokensession = $this->_session->set('token', 1, 'easycalccheck');
                    }
                    elseif(utf8_encode($request['token']) != $token)
                    {
                        $url = $this->params->get('urlfalsetoken');

                        if(empty($url))
                        {
                            $url = JURI::root();
                        }

                        if($tokensession != 1)
                        {
                            $tokensession = $this->_session->clear('token', 'easycalccheck');
                            $this->redirect($url);
                        }
                    }
                }
            }
        }
    }

    // Check the result of the calc check submittet by the contact form
    public function onValidateContact($contact, $post)
    {
        if($this->_load_ecc_check == true)
        {
            $option = JRequest::getWord('option');

            if($this->params->get('contact') AND $option == 'com_contact')
            {
                if(!$this->performChecks())
                {
                    $url = $this->buildFailedUrl();
                    $this->redirect($url);
                }
            }

            return true;
        }
    }

    // Check the result of the checks submittet by the registration form
    public function onUserBeforeSave($user, $isnew, $new)
    {
        if($this->_load_ecc_check == true)
        {
            if(!empty($isnew))
            {
                $option = JRequest::getWord('option');

                if(($this->params->get('user_reg') AND $option == 'com_users') OR ($this->params->get('communitybuilder') AND $option == 'com_comprofiler'))
                {
                    if(!$this->performChecks())
                    {
                        $url = $this->buildFailedUrl();
                        $this->redirect($url);
                    }
                }
            }
        }
    }

    // Do the checks!
    private function performChecks()
    {
        $perfomchecks_result = true;
        $request = JRequest::get();

        // Calc check
        if($this->params->get('type_calc'))
        {
            if($this->_session->get('rot13', null, 'easycalccheck') == 1)
            {
                $spamcheckresult = base64_decode(str_rot13($this->_session->get('spamcheckresult', null, 'easycalccheck')));
            }
            else
            {
                $spamcheckresult = base64_decode($this->_session->get('spamcheckresult', null, 'easycalccheck'));
            }

            $spamcheck = JRequest::getInt($this->_session->get('spamcheck', null, 'easycalccheck'), '', 'post');

            $this->_session->clear('rot13', 'easycalccheck');
            $this->_session->clear('spamcheck', 'easycalccheck');
            $this->_session->clear('spamcheckresult', 'easycalccheck');

            if(!is_numeric($spamcheckresult) || $spamcheckresult != $spamcheck)
            {
                $perfomchecks_result = false; // Failed
            }
        }

        // Hidden field
        if($this->params->get('type_hidden'))
        {
            $hidden_field = $this->_session->get('hidden_field', null, 'easycalccheck');
            $this->_session->clear('hidden_field', 'easycalccheck');

            if(JRequest::getVar($hidden_field, '', 'post'))
            {
                $perfomchecks_result = false; // Hidden field was filled out - failed
            }
        }

        // Time lock
        if($this->params->get('type_time'))
        {
            $time = $this->_session->get('time', null, 'easycalccheck');
            $this->_session->clear('time', 'easycalccheck');

            if(time() - $this->params->get('type_time_sec') <= $time)
            {
                $perfomchecks_result = false; // Submitted too fast - failed
            }
        }

        // Own Question
        // Conversion to lower case
        if($this->params->get('question'))
        {
            $answer = strtolower(JRequest::getString($this->_session->get('question', null, 'easycalccheck'), '', 'post'));
            $this->_session->clear('question', 'easycalccheck');

            if($answer != strtolower($this->params->get('question_a')))
            {
                $perfomchecks_result = false; // Question wasn't answered - failed
            }
        }

        // StopForumSpam - Check the IP Address
        // Further informations: http://www.stopforumspam.com
        if($this->params->get('stopforumspam'))
        {
            $url = 'http://www.stopforumspam.com/api?ip='.$this->_session->get('ip', null, 'easycalccheck');

            // Function test - Comment out to test - Important: Enter a active Spam-IP
            // $ip = '88.180.52.46';
            // $url = 'http://www.stopforumspam.com/api?ip='.$ip;

            $response = false;
            $is_spam = false;

            if(function_exists('curl_init'))
            {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
            }

            if($response)
            {
                preg_match('#<appears>(.*)</appears>#', $response, $out);
                $is_spam = $out[1];
            }
            else
            {
                $response = @ fopen($url, 'r');

                if($response)
                {
                    while(!feof($response))
                    {
                        $line = fgets($response, 1024);

                        if(preg_match('#<appears>(.*)</appears>#', $line, $out))
                        {
                            $is_spam = $out[1];
                            break;
                        }
                    }
                    fclose($response);
                }
            }

            if($is_spam == 'yes' AND $response == true)
            {
                $perfomchecks_result = false; // Spam-IP - failed
            }
        }
        // Honeypot Project
        // Further informations: http://www.projecthoneypot.org/home.php
        // BL ACCESS KEY - http://www.projecthoneypot.org/httpbl_configure.php
        if($this->params->get('honeypot'))
        {
            require_once(dirname(__FILE__).DS.'easycalccheckplus'.DS.'honeypot.php');
            $http_blKey = $this->params->get('honeypot_key');

            if($http_blKey)
            {
                $http_bl = new http_bl($http_blKey);
                $result = $http_bl->query($this->_session->get('ip', null, 'easycalccheck'));

                // Function test - Comment out to test - Important: Enter a active Spam-IP
                // $ip = '117.21.224.251';
                // $result = $http_bl->query($ip);

                if($result == 2)
                {
                    $perfomchecks_result = false;
                }
            }
        }
        // Akismet
        // Further informations: http://akismet.com/
        if($this->params->get('akismet'))
        {
            require_once(dirname(__FILE__).DS.'easycalccheckplus'.DS.'akismet.php');
            $akismet_key = $this->params->get('akismet_key');

            if($akismet_key)
            {
                $akismet_url = JURI::getInstance()->toString();

                $name = '';
                $email = '';
                $url = '';
                $comment = '';

                if($request['option'] == 'com_contact')
                {
                    $name = $request['jform']['contact_name'];
                    $email = $request['jform']['contact_email'];
                    $comment = $request['jform']['contact_message'];
                }
                elseif($request['option'] == 'com_users')
                {
                    $name = $request['jform']['name'];
                    $email = $request['jform']['email1'];

                    if(isset($request['jform']['email']))
                    {
                        $email = $request['jform']['email'];
                    }
                }
                elseif($request['option'] == 'com_comprofiler')
                {
                    $name = $request['name'];
                    $email = $request['email'];

                    if(isset($request['checkusername']))
                    {
                        $name = $request['checkusername'];
                    }

                    if(isset($request['checkemail']))
                    {
                        $email = $request['checkemail'];
                    }
                }
                elseif($request['option'] == 'com_easybookreloaded')
                {
                    $name = $request['gbname'];
                    $email = $request['gbmail'];
                    $comment = $request['gbtext'];

                    if(isset($request['gbpage']))
                    {
                        $url = $request['gbpage'];
                    }
                }
                elseif($request['option'] == 'com_phocaguestbook')
                {
                    $name = $request['pgusername'];
                    $email = $request['email'];
                    $comment = $request['pgbcontent'];
                }
                elseif($request['option'] == 'com_dfcontact')
                {
                    $name = $request['name'];
                    $email = $request['email'];
                    $comment = $request['message'];
                }
                elseif($request['option'] == 'com_flexicontact')
                {
                    $name = $request['from_name'];
                    $email = $request['from_email'];
                    $comment = $request['area_data'];
                }
                elseif($request['option'] == 'com_alfcontact')
                {
                    $name = $request['name'];
                    $email = $request['email'];
                    $comment = $request['message'];
                }

                $akismet = new Akismet($akismet_url, $akismet_key);
                $akismet->setCommentAuthor($name);
                $akismet->setCommentAuthorEmail($email);
                $akismet->setCommentAuthorURL($url);
                $akismet->setCommentContent($comment);

                if($akismet->isCommentSpam())
                {
                    $perfomchecks_result = false;
                }
            }
        }
        // ReCaptcha
        // Further informations: http://www.google.com/recaptcha
        if($this->params->get('recaptcha') AND $this->params->get('recaptcha_publickey') AND $this->params->get('recaptcha_privatekey'))
        {
            require_once(dirname(__FILE__).DS.'easycalccheckplus'.DS.'recaptchalib.php');
            $privatekey = $this->params->get('recaptcha_privatekey');

            $resp = recaptcha_check_answer($privatekey, $this->_session->get('ip', null, 'easycalccheck'), $request['recaptcha_challenge_field'], $request['recaptcha_response_field']);

            if(!$resp->is_valid)
            {
                $perfomchecks_result = false;
            }
        }
        // Botscout - Check the IP Address
        // Further informations: http://botscout.com/
        if($this->params->get('botscout') AND $this->params->get('botscout_key'))
        {
            $url = 'http://botscout.com/test/?ip='.$this->_session->get('ip', null, 'easycalccheck').'&key='.$this->params->get('botscout_key');

            // Function test - Comment out to test - Important: Enter a active Spam-IP
            // $ip = '87.103.128.199';
            // $url = 'http://botscout.com/test/?ip='.$ip.'&key='.$this->params->get('botscout_key');

            $response = false;
            $is_spam = false;

            if(function_exists('curl_init'))
            {
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                curl_setopt($ch, CURLOPT_POST, 0);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                $response = curl_exec($ch);
                curl_close($ch);
            }

            if($response)
            {
                $is_spam = substr($response, 0, 1);
            }
            else
            {
                $response = @ fopen($url, 'r');

                if($response)
                {
                    while(!feof($response))
                    {
                        $line = fgets($response, 1024);

                        $is_spam = substr($line, 0, 1);
                    }
                    fclose($response);
                }
            }

            if($is_spam == 'Y' AND $response == true)
            {
                // Spam-IP - failed
                $perfomchecks_result = false;
            }
        }
        // Mollom
        // Further informations: http://mollom.com/
        if($this->params->get('mollom') AND $this->params->get('mollom_publickey') AND $this->params->get('mollom_privatekey'))
        {
            require_once(dirname(__FILE__).DS.'easycalccheckplus'.DS.'mollom.php');

            Mollom::setPublicKey($this->params->get('mollom_publickey'));
            Mollom::setPrivateKey($this->params->get('mollom_privatekey'));

            $servers = Mollom::getServerList();

            $name = '';
            $email = '';
            $url = '';
            $comment = '';

            if($request['option'] == 'com_contact')
            {
                $name = $request['jform']['contact_name'];
                $email = $request['jform']['contact_email'];
                $comment = $request['jform']['contact_message'];
            }
            elseif($request['option'] == 'com_users')
            {
                $name = $request['jform']['name'];
                $email = $request['jform']['email1'];

                if(isset($request['jform']['email']))
                {
                    $email = $request['jform']['email'];
                }
            }
            elseif($request['option'] == 'com_comprofiler')
            {
                $name = $request['name'];
                $email = $request['email'];

                if(isset($request['checkusername']))
                {
                    $name = $request['checkusername'];
                }

                if(isset($request['checkemail']))
                {
                    $email = $request['checkemail'];
                }
            }
            elseif($request['option'] == 'com_easybookreloaded')
            {
                $name = $request['gbname'];
                $email = $request['gbmail'];
                $comment = $request['gbtext'];

                if(isset($request['gbpage']))
                {
                    $url = $request['gbpage'];
                }
            }
            elseif($request['option'] == 'com_phocaguestbook')
            {
                $name = $request['pgusername'];
                $email = $request['email'];
                $comment = $request['pgbcontent'];
            }
            elseif($request['option'] == 'com_dfcontact')
            {
                $name = $request['name'];
                $email = $request['email'];
                $comment = $request['message'];
            }
            elseif($request['option'] == 'com_flexicontact')
            {
                $name = $request['from_name'];
                $email = $request['from_email'];
                $comment = $request['area_data'];
            }
            elseif($request['option'] == 'com_alfcontact')
            {
                $name = $request['name'];
                $email = $request['email'];
                $comment = $request['message'];
            }

            $feedback = Mollom::checkContent(null, null, $comment, $name, $url, $email);

            if($feedback['spam'] == 'spam')
            {
                $perfomchecks_result = false;
            }
        }

        $this->_session->clear('ip', 'easycalccheck');

        if($perfomchecks_result == true)
        {
            $this->_session->clear('saved_data', 'easycalccheck');
        }

        return $perfomchecks_result;
    }

    // Check if ECC+ has to be loaded
    private function loadEcc($option, $task, $view, $func, $layout)
    {
        $user = JFactory::getUser();
        $app = JFactory::getApplication();

        if($app->isAdmin() OR ($this->params->get('onlyguests') AND !$user->guest))
        {
            $this->_load_ecc = false;
            $this->_load_ecc_check = false;
        }
        else
        {
            // Find out if ECC+ has to be loaded depending on the called component
            switch($option)
            {
                case 'com_contact':

                    // Array(name, form, regex for hidden field, regex for output);
                    $this->_extension_info = array('com_contact', '<form[^>]+id="contact-form".+</form>', '<label id="jform_contact.+>', '<button class="button validate" type="submit">');

                    if($this->params->get('contact') AND $view == 'contact')
                    {
                        $this->_load_ecc = true;
                    }

                    if($this->params->get('contact') AND $task == 'contact.submit')
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                case 'com_users':

                    if($layout != 'confirm' AND $layout != 'complete')
                    {
                        if($view == 'registration')
                        {
                            $this->_extension_info = array('com_users', '<form[^>]+id="member-registration".+</form>', '<label id="jform.+>', '<button type="submit" class="validate">');
                        }
                        elseif($view == 'reset' OR $view == 'remind')
                        {
                            $this->_extension_info = array('com_users', '<form[^>]+id="user-registration".+</form>', '<label id="jform_email-lbl"', '<button type="submit">');
                        }

                        if($this->params->get('user_reg') AND ($view == 'registration' OR $view == 'reset' OR $view == 'remind'))
                        {
                            $this->_load_ecc = true;
                        }

                        if($this->params->get('user_reg') AND ($task == 'registration.register' OR $task == 'reset.request' OR $task == 'remind.remind'))
                        {
                            $this->_load_ecc_check = true;
                        }
                    }

                    break;

                // Easybook Reloaded - tested with version 4.0
                case 'com_easybookreloaded':

                    $this->_extension_info = array('com_easybookreloaded', '<form[^>]+name=\'gbookForm\'.+</form>', '<input type=.+>', '<p id="easysubmit">');

                    if($this->params->get('easybookreloaded') AND ($task == 'add'))
                    {
                        $this->_load_ecc = true;
                    }

                    if($this->params->get('easybookreloaded') AND ($task == 'save'))
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // Phoca Guestbook - tested with version 2.0.1
                case 'com_phocaguestbook':

                    $this->_extension_info = array('com_phocaguestbook', '<form[^>]+id="pgbSaveForm".+</form>', '<input type=.+>', '<input type="submit" name="save" value=".+" />');

                    if($this->params->get('phocaguestbook') AND $view == 'guestbook' AND $task != 'submit')
                    {
                        $this->_load_ecc = true;
                    }

                    if($this->params->get('phocaguestbook') AND $task == 'submit')
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // Community Builder - tested with version 1.7
                case 'com_comprofiler':

                    if($task == 'registers')
                    {
                        $this->_extension_info = array('com_comprofiler', '<form[^>]+id="cbcheckedadminForm".+</form>', '<label for=".+>', '<input type="submit" value=".+" class="button" />');
                    }
                    elseif($task == 'lostpassword')
                    {
                        $this->_extension_info = array('com_comprofiler', '<form[^>]+id="adminForm".+</form>', '<label for=".+>', '<input type="submit" class="button" id="cbsendnewuspass" value=".+" />');
                    }

                    if($this->params->get('communitybuilder') AND ($task == 'registers' OR $task == 'lostpassword'))
                    {
                        $this->_load_ecc = true;
                    }
                    elseif($this->params->get('communitybuilder') AND ($task == 'saveregisters' OR $task == 'sendNewPass'))
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // DFContact - tested with version 1.6.3
                case 'com_dfcontact':

                    $this->_extension_info = array('com_dfcontact', '<form[^>]+id="dfContactForm".+</form>', '<label for="dfContactField.+>', '<input type="submit" value=".+" class="button" />');

                    if($this->params->get('dfcontact') AND $view == 'dfcontact' AND empty($_REQUEST["submit"]))
                    {
                        $this->_load_ecc = true;

                    }
                    elseif($this->params->get('dfcontact') AND $view == 'dfcontact' AND !empty($_REQUEST["submit"]))
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // FoxContact - tested with version 2.0.5
                case 'com_foxcontact':

                    $this->_extension_info = array('com_foxcontact', '<form[^>]+id="FoxForm".+</form>', '<input class=.+>', '<input class="foxbutton" type="submit" style=".+" name=".+" value=".+"/>');

                    $Itemid = JRequest::getCmd('Itemid');

                    if($this->params->get('foxcontact') AND $view == 'foxcontact' AND !isset($_REQUEST['cid_'.$Itemid]))
                    {
                        $this->_load_ecc = true;

                    }
                    elseif($this->params->get('foxcontact') AND $view == 'foxcontact' AND isset($_REQUEST['cid_'.$Itemid]))
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // FlexiContact - tested with version 5.06
                case 'com_flexicontact':

                    $this->_extension_info = array('com_flexicontact', '<form[^>]+name="fc_form".+</form>', '<input type=.+>', '<input type="submit" name="send_button".+/>');

                    if($this->params->get('flexicontact') AND $view == 'contact' AND empty($task))
                    {
                        $this->_load_ecc = true;

                    }
                    elseif($this->params->get('flexicontact') AND $view == 'contact' AND $task == 'send')
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // Kunena Forum - tested with version 1.7.0
                case 'com_kunena':

                    $this->_extension_info = array('com_kunena', '<form[^>]+id="postform".+</form>', '<input type=.+>', '<input type="submit" name="ksubmit" class="kbutton".+/>');

                    if($this->params->get('kunena') AND $func == 'post' AND empty($_REQUEST["ksubmit"]))
                    {
                        $this->_load_ecc = true;

                    }
                    elseif($this->params->get('kunena') AND $func == 'post' AND !empty($_REQUEST["ksubmit"]))
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                // ALFContact - tested with version 2.0.1
                case 'com_alfcontact':

                    $this->_extension_info = array('com_alfcontact', '<form[^>]+id="contact-form".+</form>', '<label for=".+>', '<button class="button">');

                    if($this->params->get('alfcontact') AND $view == 'alfcontact' AND empty($task))
                    {
                        $this->_load_ecc = true;

                    }
                    elseif($this->params->get('alfcontact') AND $task == 'sendemail')
                    {
                        $this->_load_ecc_check = true;
                    }

                    break;

                default:

                    return;
                    break;
            }
        }
    }

    // Save entered data in the session
    private function saveData()
    {
        $request = JRequest::get('default', JREQUEST_ALLOWRAW);
        $data_array = array();

        foreach($request as $key => $value)
        {
            if($key != 'option' AND $key != 'view' AND $key != 'id' AND $key != 'Itemid' AND $key != 'task' AND $key != 'controller' AND $key != 'func')
            {
                if(is_array($value))
                {
                    foreach($value as $key2 => $value2)
                    {
                        // Need second request for user profile plugin
                        if(is_array($value2))
                        {
                            foreach($value2 as $key3 => $value3)
                            {
                                $key4 = $key.'['.$key2.']['.$key3.']';
                                $data_array[$key4] = $value3;
                            }
                        }
                        else
                        {
                            $key3 = $key.'['.$key2.']';
                            $data_array[$key3] = $value2;
                        }
                    }
                }
                else
                {
                    $data_array[$key] = $value;
                }
            }
        }

        $this->_session->set('saved_data', $data_array, 'easycalccheck');
    }

    private function fill_form(&$body)
    {
        $autofill = $this->_session->get('saved_data', null, 'easycalccheck');

        if(!empty($autofill))
        {
            $pattern_form = '@'.$this->_extension_info[1].'@isU';
            preg_match($pattern_form, $body, $match_extension);

            $pattern_input = '@<input[^>].*/?>@isU';
            preg_match_all($pattern_input, $match_extension[0], $matches_input);

            foreach($matches_input[0] as $input_value)
            {
                foreach($autofill as $key => $autofill_value)
                {
                    if($autofill_value != '')
                    {
                        $value = '@name=("|\')'.preg_quote($key).'("|\')@isU';
                        if(preg_match($value, $input_value))
                        {
                            $value = '@value=("|\').*("|\')@isU';
                            if(preg_match($value, $input_value, $match))
                            {
                                $pattern_value = '/'.preg_quote($match[0]).'/isU';
                                $input_value_replaced = preg_replace($pattern_value, 'value="'.$autofill_value.'"', $input_value);

                                $body = str_replace($input_value, $input_value_replaced, $body);
                                unset($autofill[$key]);
                                break;
                            }
                        }
                    }
                }
            }

            $pattern_textarea = '@<textarea[^>].*>(.*</textarea>)@isU';
            preg_match_all($pattern_textarea, $match_extension[0], $matches_textarea);

            $count = 0;

            foreach($matches_textarea[0] as $textarea_value)
            {
                foreach($autofill as $key => $autofill_value)
                {
                    $value = '@name=("|\')'.preg_quote($key).'("|\')@';
                    if(preg_match($value, $textarea_value))
                    {
                        $pattern_value = '@'.preg_quote($matches_textarea[1][$count]).'@isU';
                        $textarea_value_replaced = preg_replace($pattern_value, $autofill_value.'</textarea>', $textarea_value);

                        $body = str_replace($textarea_value, $textarea_value_replaced, $body);
                        unset($autofill[$key]);
                        break;
                    }
                }

                $count++;
            }

            $this->_session->clear('saved_data', 'easycalccheck');
        }
    }

    // Call checks for supported extensions
    private function callChecks($option, $task)
    {
        $check_failed = false;
        $Itemid = JRequest::getCmd('Itemid');

        if($option == 'com_users' AND ($task == 'reset.request' OR $task == 'remind.remind'))
        {
            if(!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif($option == 'com_easybookreloaded' AND $task == 'save')
        {
            if(!$this->performChecks())
            {
                $this->_session->set('easybookreloaded', 1, 'easycalccheck');
                $check_failed = true;
            }
        }
        elseif($option == 'com_phocaguestbook' AND $task == 'submit')
        {
            if(!$this->performChecks())
            {
                $this->_session->set('phocaguestbook', 1, 'easycalccheck');
                $check_failed = true;
            }
        }
        elseif($option == 'com_comprofiler' AND $task == 'sendNewPass')
        {
            if(!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif ($option == 'com_dfcontact' AND !empty($_REQUEST["submit"]))
        {
            if (!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif ($option == 'com_foxcontact' AND isset($_REQUEST['cid_'.$Itemid]))
        {
            if (!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif ($option == 'com_flexicontact' AND $task == 'send')
        {
            if (!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif ($option == 'com_kunena' AND !empty($_REQUEST["ksubmit"]))
        {
            if (!$this->performChecks())
            {
                $check_failed = true;
            }
        }
        elseif ($option == 'com_alfcontact' AND $task == 'sendemail')
        {
            if (!$this->performChecks())
            {
                $check_failed = true;
            }
        }

        if($check_failed == true)
        {
            $url = $this->buildFailedUrl();
            $this->redirect($url);
        }
    }

    // Build URL if check failed
    private function buildFailedUrl()
    {
        $url = $this->_redirect_url;
        $string_failed = 'eccp_err=check_failed';

        $pattern_failed = '@eccp_err=check_failed@isU';

        if(preg_match($pattern_failed, $url))
        {
            return $url;
        }

        $pattern = '@\?@';

        if(preg_match($pattern, $url))
        {
            $url = $url.'&'.$string_failed;
        }
        else
        {
            $url = $url.'?'.$string_failed;
        }

        return $url;
    }

    // Decode encoded fields
    private function decodeFields($option, $task)
    {
        if($this->params->get('contact') AND ($option == 'com_contact') AND ($task == 'contact.submit'))
        {
            $name = $this->_session->get('jform[contact_name]', null, 'easycalccheck');
            $email = $this->_session->get('jform[contact_email]', null, 'easycalccheck');
            $subject = $this->_session->get('jform[contact_subject]', null, 'easycalccheck');
            $text = $this->_session->get('jform[contact_message]', null, 'easycalccheck');

            $jform_array = JRequest::getVar('jform');

            $jform = array();
            $jform['jform']['contact_name'] = JRequest::getString($name);
            $jform['jform']['contact_email'] = JRequest::getString($email);
            $jform['jform']['contact_subject'] = JRequest::getString($subject);
            $jform['jform']['contact_message'] = JRequest::getString($text);

            // Check e-mail copy
            if(isset($jform_array['contact_email_copy']))
            {
                $jform['jform']['contact_email_copy'] = '';
            }

            JRequest::set($jform, 'post', true);

            $this->_session->clear('jform[contact_name]', 'easycalccheck');
            $this->_session->clear('jform[contact_email]', 'easycalccheck');
            $this->_session->clear('jform[contact_subject]', 'easycalccheck');
            $this->_session->clear('jform[contact_message]', 'easycalccheck');
        }
        elseif($this->params->get('user_reg') AND ($option == 'com_users') AND ($task == 'registration.register'))
        {
            $name = $this->_session->get('jform[name]', null, 'easycalccheck');
            $username = $this->_session->get('jform[username]', null, 'easycalccheck');
            $password1 = $this->_session->get('jform[password1]', null, 'easycalccheck');
            $password2 = $this->_session->get('jform[password2]', null, 'easycalccheck');
            $email1 = $this->_session->get('jform[email1]', null, 'easycalccheck');
            $email2 = $this->_session->get('jform[email2]', null, 'easycalccheck');

            $jform = JRequest::get();

            $jform['jform']['name'] = JRequest::getString($name);
            $jform['jform']['username'] = JRequest::getString($username);
            $jform['jform']['password1'] = JRequest::getString($password1);
            $jform['jform']['password2'] = JRequest::getString($password2);
            $jform['jform']['email1'] = JRequest::getString($email1);
            $jform['jform']['email2'] = JRequest::getString($email2);

            JRequest::set($jform, 'post', true);

            $this->_session->clear('jform[name]', 'easycalccheck');
            $this->_session->clear('jform[username]', 'easycalccheck');
            $this->_session->clear('jform[password1]', 'easycalccheck');
            $this->_session->clear('jform[password2]', 'easycalccheck');
            $this->_session->clear('jform[email1]', 'easycalccheck');
            $this->_session->clear('jform[email2]', 'easycalccheck');
        }
    }

    // Redirect if spamcheck wasn't passed
    private function redirect($returnURI)
    {
        // PHP Redirection
        header('Location: '.$returnURI);

        // JS Redirection
        ?>
        <script type="text/javascript">window.location = '<?php echo $returnURI; ?>'</script>
        <?php
        // White page - if redirection doesn't work
        echo JText::_('PLG_ECC_YOUHAVENOTRESOLVEDOURSPAMCHECK');
        jexit();
    }

    // Create random string
    private function random()
    {
        $pw = '';

        // first character has to be a letter
        $characters = range('a', 'z');
        $pw .= $characters[mt_rand(0, 25)];

        // other characters arbitrarily
        $numbers = range(0, 9);
        $characters = array_merge($characters, $numbers);

        $pw_length = mt_rand(4, 12);

        for($i = 0; $i < $pw_length; $i++)
        {
            $pw .= $characters[mt_rand(0, 35)];
        }

        return $pw;
    }

    // Convert numbers into strings
    private function converttostring($x)
    {
        // Probability 2/3 for conversion
        $random = mt_rand(1, 3);

        if($random != 1)
        {
            if($x > 20)
            {
                return $x;
            }
            else
            {
                // Names of the numbers are read from language file
                $names = array(JText::_('PLG_ECC_NULL'), JText::_('PLG_ECC_ONE'), JText::_('PLG_ECC_TWO'), JText::_('PLG_ECC_THREE'), JText::_('PLG_ECC_FOUR'), JText::_('PLG_ECC_FIVE'), JText::_('PLG_ECC_SIX'), JText::_('PLG_ECC_SEVEN'), JText::_('PLG_ECC_EIGHT'), JText::_('PLG_ECC_NINE'), JText::_('PLG_ECC_TEN'), JText::_('PLG_ECC_ELEVEN'), JText::_('PLG_ECC_TWELVE'), JText::_('PLG_ECC_THIRTEEN'), JText::_('PLG_ECC_FOURTEEN'), JText::_('PLG_ECC_FIFTEEN'), JText::_('PLG_ECC_SIXTEEN'), JText::_('PLG_ECC_SEVENTEEN'), JText::_('PLG_ECC_EIGHTEEN'), JText::_('PLG_ECC_NINETEEN'), JText::_('PLG_ECC_TWENTY'));
                return $names[$x];
            }
        }
        else
        {
            return $x;
        }
    }
}
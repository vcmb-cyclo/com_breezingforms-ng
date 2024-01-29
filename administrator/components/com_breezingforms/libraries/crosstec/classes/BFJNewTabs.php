<?php
defined('_JEXEC') or die('Direct Access to this location is not allowed.');
/**
* BreezingForms - A Joomla Forms Application
* @version 1.9
* @package BreezingForms
* @copyright (C) 2008-2020 by Markus Bopp
* @license Released under the terms of the GNU General Public License
**/

// shouldn't be required no longer in Joomla 3.0 Stable
require_once(JPATH_SITE.DS.'administrator'.DS.'components'.DS.'com_breezingforms'.DS.'libraries'.DS.'crosstec'.DS.'classes'.DS.'BFBehaviorTabs.php');

    class BFTabs  {
            
            function __construct( $useCookies, $xhtml = null) {
            }

            static function startTab( $tabText, $paneid ) {
                
                    // bring back in joomla 3.0 stable
                    //echo JHtml::_('tabs.panel', $tabText, $paneid);
                    echo BFBehaviorTabs::panel($tabText, $paneid);
            }

            static function endTab() {
                    echo '';
            }

            static function startPane( $tabText ){
                    $options = array(
                        'startOffset' => 0,  // 0 starts on the first tab, 1 starts the second, etc...
                        'useCookie' => true, // this must not be a string. Don't use quotes.
                    );
                
                    //echo JHtml::_('tabs.start', 'bftab', $options);
                    echo BFBehaviorTabs::start('bftab', $options);
            }

            static function endPane(){
                //echo JHtml::_('tabs.end');
                echo BFBehaviorTabs::end();
            }
    }

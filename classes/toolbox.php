<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    theme_ned_boost
 * @subpackage NED Boost
 * @copyright  NED {@link http://ned.ca}
 * @author     NED {@link http://ned.ca}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @developer  G J Barnard - {@link http://about.me/gjbarnard} and
 *                           {@link http://moodle.org/user/profile.php?id=442195}
 */

namespace theme_ned_boost;

defined('MOODLE_INTERNAL') || die();

class toolbox {

    protected static $instance;
    protected $boostparent;
    protected $customiseindividualblocks = null;
    // Constants.
    public static $fontawesomekey = 'fa';
    public static $headerbackgroundcolourkey = 'hbc';
    public static $headertextcolourkey = 'htc';
    public static $bodybackgroundcolourkey = 'bbc';
    public static $nocolourdelimiter = '-';

    private function __construct() {
        
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
            self::$instance->boostparent = \theme_config::load('boost');
        }
        return self::$instance;
    }

    public function get_pre_scss($theme) {
        $mainbackgroundcolour = '#eceeef';
        if (!empty($theme->settings->mainbackgroundcolour)) {
            $mainbackgroundcolour = $theme->settings->mainbackgroundcolour;
        }
        $scss = '$body-bg: '.$mainbackgroundcolour.';';

        $navdrawerbackgroundcolour = '#dce0e2';
        if (!empty($theme->settings->navdrawerbackgroundcolour)) {
            $navdrawerbackgroundcolour = $theme->settings->navdrawerbackgroundcolour;
        }
        $scss .= '$drawer-bg: '.$navdrawerbackgroundcolour.';';

        $scss .= theme_boost_get_pre_scss($this->boostparent);

        return $scss;
    }

    public function get_main_scss_content($theme) {
        global $CFG;
        require_once($CFG->dirroot.'/theme/boost/lib.php');

        $scss = theme_boost_get_main_scss_content($this->boostparent);

        if (file_exists("{$CFG->dirroot}/theme/ned_boost/scss/blocks.scss")) {
            $scss .= file_get_contents($CFG->dirroot.'/theme/ned_boost/scss/blocks.scss');
        } else if (!empty($CFG->themedir) && file_exists("{$CFG->themedir}/ned_boost/scss/blocks.scss")) {
            $scss .= file_get_contents($CFG->themedir.'/ned_boost/scss/blocks.scss');
        }

        $scss .= $this->set_frontpagedashboard_blocks($theme);
        $scss .= $this->set_course_blocks($theme);
        $scss .= $this->set_block_header($theme);

        return $scss;
    }

    public function get_extra_scss($theme) {
        return theme_boost_get_extra_scss($this->boostparent);
    }

    protected function set_frontpagedashboard_blocks($theme) {
        $scss = '';
        $singlewidth = 280;
        $doublewidth = 560;
        if (!empty($theme->settings->frontpagedashboardlevelblockwidth)) {
            $scss .= '.pagelayout-mydashboard [data-region="blocks-column"],';
            $scss .= '.pagelayout-frontpage [data-region="blocks-column"] {';
            $scss .= 'width: '.$theme->settings->frontpagedashboardlevelblockwidth.'px;';
            $scss .= '}';
            $singlewidth = $theme->settings->frontpagedashboardlevelblockwidth + 30;
            $doublewidth = ($theme->settings->frontpagedashboardlevelblockwidth * 2) + 60;
            if ($theme->settings->frontpagedashboardlevelblockpositions != 2) { // Not right.
                $scss .= '.pagelayout-mydashboard [data-region="blocks-column"].side-pre,';
                $scss .= '.pagelayout-frontpage [data-region="blocks-column"].side-pre {';
                $scss .= 'float: left;';
                $scss .= '}';
                $scss .= '.pagelayout-mydashboard #region-main.has-blocks,';
                $scss .= '.pagelayout-frontpage #region-main.has-blocks {';
                $scss .= 'margin-left: 30px;';
                $scss .= '}';
            }
        }
        $scss .= '.pagelayout-mydashboard #region-main-settings-menu.has-blocks,';
        $scss .= '.pagelayout-mydashboard #region-main.has-blocks,';
        $scss .= '.pagelayout-frontpage #region-main-settings-menu.has-blocks,';
        $scss .= '.pagelayout-frontpage #region-main.has-blocks {';
        $scss .= 'width: calc(100% - '.$singlewidth.'px);';
        $scss .= '}';
        $scss .= '.pagelayout-mydashboard #region-main-settings-menu.has-blocks.both-blocks,';
        $scss .= '.pagelayout-mydashboard #region-main.has-blocks.both-blocks,';
        $scss .= '.pagelayout-frontpage #region-main-settings-menu.has-blocks.both-blocks,';
        $scss .= '.pagelayout-frontpage #region-main.has-blocks.both-blocks {';
        $scss .= 'width: calc(100% - '.$doublewidth.'px);';
        $scss .= '}';

        return $scss;
    }

    protected function set_course_blocks($theme) {
        $scss = '';
        $singlewidth = 280;
        $doublewidth = 560;
        if (!empty($theme->settings->courselevelblockwidth)) {
            $scss .= '.pagelayout-course [data-region="blocks-column"],';
            $scss .= '.pagelayout-incourse [data-region="blocks-column"] {';
            $scss .= 'width: '.$theme->settings->courselevelblockwidth.'px;';
            $scss .= '}';
            $singlewidth = $theme->settings->courselevelblockwidth + 30;
            $doublewidth = ($theme->settings->courselevelblockwidth * 2) + 60;
            if ($theme->settings->courselevelblockpositions != 2) { // Not right.
                $scss .= '.pagelayout-course [data-region="blocks-column"].side-pre,';
                $scss .= '.pagelayout-incourse [data-region="blocks-column"].side-pre {';
                $scss .= 'float: left;';
                $scss .= '}';
                $scss .= '.pagelayout-course #region-main.has-blocks,';
                $scss .= '.pagelayout-incourse #region-main.has-blocks {';
                $scss .= 'margin-left: 30px;';
                $scss .= '}';
            }
        }
        $scss .= '.pagelayout-course #region-main-settings-menu.has-blocks,';
        $scss .= '.pagelayout-course #region-main.has-blocks,';
        $scss .= '.pagelayout-incourse #region-main-settings-menu.has-blocks,';
        $scss .= '.pagelayout-incourse #region-main.has-blocks {';
        $scss .= 'width: calc(100% - '.$singlewidth.'px);';
        $scss .= '}';
        $scss .= '.pagelayout-course #region-main-settings-menu.has-blocks.both-blocks,';
        $scss .= '.pagelayout-course #region-main.has-blocks.both-blocks,';
        $scss .= '.pagelayout-incourse #region-main-settings-menu.has-blocks.both-blocks,';
        $scss .= '.pagelayout-incourse #region-main.has-blocks.both-blocks {';
        $scss .= 'width: calc(100% - '.$doublewidth.'px);';
        $scss .= '}';

        return $scss;
    }

    protected function set_block_header($theme) {
        $scss = '';

        $blockheaderbackgroundcolour = '#dce0e2';
        $blockheadertextcolour = '#333333';
        if (!empty($theme->settings->blockheaderbackgroundcolour)) {
            $blockheaderbackgroundcolour = '#dce0e2';
        }
        if (!empty($theme->settings->blockheadertextcolour)) {
            $blockheadertextcolour = '#333333';
        }

        $scss .= '.block .card-block {';
        $scss .= 'padding: 0;';
        $scss .= '}';

        $scss .= '.block .card-block .content {';
        $scss .= 'padding: $card-spacer-x;';
        $scss .= '}';

        $scss .= '.block .card-block .block-header {';
        $scss .= 'background-color: '.$blockheaderbackgroundcolour.';';
        $scss .= 'color: '.$blockheadertextcolour.';';
        $scss .= '}';

        // Individual blocks.
        $customiseindividualblocks = $this->get_customiseindividualblocks($theme);
        foreach ($customiseindividualblocks as $blockname => $blocksettings) {
            if ((!empty($blocksettings[self::$headerbackgroundcolourkey])) &&
                    ($blocksettings[self::$headerbackgroundcolourkey][0] != self::$nocolourdelimiter)) { // Header background colour.
                $scss .= '.block.block_'.$blockname.' .card-block .block-header {';
                $scss .= 'background-color: '.$blocksettings[self::$headerbackgroundcolourkey].';';
                $scss .= '}';
            }
            if ((!empty($blocksettings[self::$headertextcolourkey])) &&
                    ($blocksettings[self::$headertextcolourkey][0] != self::$nocolourdelimiter)) { // Header text colour.
                $scss .= '.block.block_'.$blockname.' .card-block .block-header,';
                $scss .= '.block.block_'.$blockname.' .block-controls a.dropdown-toggle,';
                $scss .= '.block.block_'.$blockname.' .block-controls a.dropdown-toggle .icon {';
                $scss .= 'color: '.$blocksettings[self::$headertextcolourkey].';';
                $scss .= '}';
            }
            if ((!empty($blocksettings[self::$bodybackgroundcolourkey])) &&
                    ($blocksettings[self::$bodybackgroundcolourkey][0] != self::$nocolourdelimiter)) { // Body background colour.
                $scss .= '.block.block_'.$blockname.' .card-block .content {';
                $scss .= 'background-color: '.$blocksettings[self::$bodybackgroundcolourkey].';';
                $scss .= '}';
            }
        }

        return $scss;
    }

    public function get_customiseindividualblocks($theme) {
        if (is_null($this->customiseindividualblocks)) {
            if (!empty($theme->settings->customiseindividualblocks)) {
                $this->customiseindividualblocks = json_decode($theme->settings->customiseindividualblocks, true);
            } else {
                // Not set or empty so no blocks to customise.
                $this->customiseindividualblocks = array();
            }
        }

        return $this->customiseindividualblocks;
    }

    /**
     * Validates the colour that was entered by the user.
     * Borrowed from 'admin_setting_configcolourpicker' in '/lib/adminlib.php'.
     *
     * I'm not completely happy with this solution as would rather embed in the colour
     * picker code in the form, however I find this area rather fraut and I hear that
     * Dan Poltawski (via MDL-42270) will be re-writing the forms lib so hopefully more
     * developer friendly.
     *
     * Note: Colour names removed, but might consider putting them back in if asked, but
     *       at the moment that would require quite a few changes and coping with existing
     *       settings.  Either convert the names to hex or allow them as valid values and
     *       fix the colour picker code and the CSS code in 'format.php' for the setting.
     *
     * Colour name to hex on: http://www.w3schools.com/cssref/css_colornames.asp.
     *
     * @param string $data the colour string to validate.
     * @return true|false.
     */
    public function validate_colour($data) {
        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $data)) {
            return true;
        } else {
            return false;
        }
    }

    public function getfontawesomemarkup($theicon, $classes = array(), $attributes = array(), $content = '') {
        $classes[] = $theicon;
        $attributes['aria-hidden'] = 'true';
        $attributes['class'] = implode(' ', $classes);
        return \html_writer::tag('span', $content, $attributes);
    }
}
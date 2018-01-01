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

    private function __construct() {
    }

    public static function get_instance() {
        if (!is_object(self::$instance)) {
            self::$instance = new self();
            self::$instance->boostparent = \theme_config::load('boost');
        }
        return self::$instance;
    }

    public function get_main_scss_content($theme) {
        global $CFG;
        require_once($CFG->dirroot.'/theme/boost/lib.php');

        $scss = theme_boost_get_main_scss_content($this->boostparent);

        $scss .= $this->set_sitedashboard_block_width($theme);
        $scss .= $this->set_course_block_width($theme);

        return $scss;
    }

    protected function set_sitedashboard_block_width($theme) {
        $scss = '';
        $mainwidth = 280;
        if (!empty($theme->settings->sitedashboardlevelblockwidth)) {
            $scss .= '.pagelayout-mydashboard [data-region="blocks-column"],';
            $scss .= '.pagelayout-frontpage [data-region="blocks-column"] {';
            $scss .= 'width: '.$theme->settings->sitedashboardlevelblockwidth.'px;';
            $scss .= '}';
            if ($theme->settings->sitedashboardlevelblockpositions == 1) { // Both.
                $mainwidth = ($theme->settings->sitedashboardlevelblockwidth * 2) + 60;
            } else {
                $mainwidth = $theme->settings->sitedashboardlevelblockwidth + 30;
            }
            if ($theme->settings->sitedashboardlevelblockpositions != 2) { // Not right.
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
        $scss .= 'width: calc(100% - '.$mainwidth.'px);';
        $scss .= '}';

        return $scss;
    }

    protected function set_course_block_width($theme) {
        $scss = '';
        $mainwidth = 280;
        if (!empty($theme->settings->courselevelblockwidth)) {
            $scss .= '.pagelayout-course [data-region="blocks-column"],';
            $scss .= '.pagelayout-incourse [data-region="blocks-column"] {';
            $scss .= 'width: '.$theme->settings->courselevelblockwidth.'px;';
            $scss .= '}';
            if ($theme->settings->courselevelblockpositions == 1) { // Both.
                $mainwidth = ($theme->settings->courselevelblockwidth * 2) + 60;
            } else {
                $mainwidth = $theme->settings->courselevelblockwidth + 30;
            }
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
        $scss .= 'width: calc(100% - '.$mainwidth.'px);';
        $scss .= '}';

        return $scss;
    }
}

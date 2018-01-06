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

namespace theme_ned_boost\output;

defined('MOODLE_INTERNAL') || die;

class core_renderer extends \theme_boost\output\core_renderer {

    protected function get_dynamicbase() {
        global $SITE;
        $regionmainsettingsmenu = $this->region_main_settings_menu();
        $templatecontext = [
            'sitename' => format_string($SITE->shortname, true, ['context' => \context_course::instance(\SITEID), "escape" => false]),
            'output' => $this,
            'regionmainsettingsmenu' => $regionmainsettingsmenu,
            'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu)
        ];
        $templatecontext['flatnavigation'] = $this->page->flatnav;

        return $templatecontext;
    }

    protected function get_navdraweropen(&$templatecontext, $shownavdrawer) {
        if ($shownavdrawer) {
            user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);

            if (isloggedin()) {
                $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
            } else {
                $navdraweropen = false;
            }
        } else {
            $navdraweropen = false;
        }

        $extraclasses = [];
        if ($navdraweropen && $shownavdrawer) {
            $extraclasses[] = 'drawer-open-left';
        }
        $templatecontext['navdraweropen'] = $navdraweropen;
        $templatecontext['shownavdrawer'] = $shownavdrawer;

        return $extraclasses;
    }

    protected function determine_dynamic_block_positions(&$templatecontext, $position) {
        $blockslefthtml = $this->blocks('side-pre');
        $hasleftblocks = strpos($blockslefthtml, 'data-block=') !== false;
        $blocksrighthtml = $this->blocks('side-post');
        $hasrightblocks = strpos($blocksrighthtml, 'data-block=') !== false;
        $hasblocks = ($hasleftblocks || $hasrightblocks);

        switch ($position) { // 1 is 'Both' - default for this layout.
            case 2: // Right.
                $blocksrighthtml = $blockslefthtml.$blocksrighthtml;
                $blockslefthtml = '';
                $hasrightblocks = $hasblocks;
                $hasleftblocks = false;
            break;
            case 3: // Left.
                $blockslefthtml = $blockslefthtml.$blocksrighthtml;
                $blocksrighthtml = '';
                $hasleftblocks = $hasblocks;
                $hasrightblocks = false;
            break;
        }
        $bothblocks = ($hasleftblocks && $hasrightblocks);

        $templatecontext['hasblocks'] = $hasblocks;
        $templatecontext['bothblocks'] = $bothblocks;
        $templatecontext['sidepreblocks'] = $blockslefthtml;
        $templatecontext['hasleftblocks'] = $hasleftblocks;
        $templatecontext['sidepostblocks'] = $blocksrighthtml;
        $templatecontext['hasrightblocks'] = $hasrightblocks;
    }

    public function get_course_context() {
        global $CFG;

        if (!empty($this->page->theme->settings->coursehamburgerbutton)) {
            switch($this->page->theme->settings->coursehamburgerbutton) {
                case 1: // Show.
                    $shownavdrawer = true;
                    break;
                case 2: // Hide.
                    $shownavdrawer = false;
                    break;
                default: // Is 3 being 'capability'.
                    $shownavdrawer = has_capability('theme/ned_boost:shownavdrawer', \context_course::instance($this->page->course->id));
                    break;
            }
        } else {
            $shownavdrawer = has_capability('theme/ned_boost:shownavdrawer', \context_course::instance($this->page->course->id));
        }

        require_once($CFG->libdir . '/behat/lib.php');

        $templatecontext = $this->get_dynamicbase();
        $extraclasses = $this->get_navdraweropen($templatecontext, $shownavdrawer);
        $bodyattributes = $this->body_attributes($extraclasses);
        $templatecontext['bodyattributes'] = $bodyattributes;

        $position = (!empty($this->page->theme->settings->courselevelblockpositions)) ? $this->page->theme->settings->courselevelblockpositions : 2; // Right.
        $this->determine_dynamic_block_positions($templatecontext, $position);

        return $templatecontext;
    }

    public function get_frontdashboard_context() {
        global $CFG;
        require_once($CFG->libdir . '/behat/lib.php');

        $templatecontext = $this->get_dynamicbase();
        $extraclasses = $this->get_navdraweropen($templatecontext, true);
        $bodyattributes = $this->body_attributes($extraclasses);
        $templatecontext['bodyattributes'] = $bodyattributes;

        $position = (!empty($this->page->theme->settings->frontpagedashboardlevelblockpositions)) ? $this->page->theme->settings->frontpagedashboardlevelblockpositions : 3; // Left.
        $this->determine_dynamic_block_positions($templatecontext, $position);

        return $templatecontext;
    }

    public function get_layout2_context() {
        global $CFG;
        require_once($CFG->libdir . '/behat/lib.php');

        $templatecontext = $this->get_dynamicbase();
        $extraclasses = $this->get_navdraweropen($templatecontext, true);
        $bodyattributes = $this->body_attributes($extraclasses);
        $templatecontext['bodyattributes'] = $bodyattributes;

        $blockshtml = $this->blocks('side-pre');
        $hasblocks = strpos($blockshtml, 'data-block=') !== false;
        $templatecontext['sidepreblocks'] = $blockshtml;
        $templatecontext['hasblocks'] = $hasblocks;

        return $templatecontext;
    }
}

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

use block_contents;
use context_system;
use moodle_url;
use stdClass;

class core_renderer extends \theme_boost\output\core_renderer {

    protected $compactlogourl = null;
    protected $logourl = null;

    /**
     * Constructor
     *
     * @param moodle_page $page the page we are doing output for.
     * @param string $target one of rendering target constants
     */
    public function __construct(\moodle_page $page, $target) {
        parent::__construct($page, $target);

        global $USER;
        if (!empty($USER->institution)) {
            $numberofuserinstitutions = get_config('theme_ned_boost', 'userinstitutioncount');
            for ($institutionnumber = 1; $institutionnumber <= $numberofuserinstitutions; $institutionnumber++) {
                $userinstitutionsetting = 'userinstitution'.$institutionnumber;
                if ((!empty($this->page->theme->settings->$userinstitutionsetting)) &&
                        ($USER->institution == $this->page->theme->settings->$userinstitutionsetting)) {
                    $institutioncompactlogo = 'institutioncompactlogo'.$institutionnumber;
                    $institutionlogo = 'institutionlogo'.$institutionnumber;

                    if (!empty($this->page->theme->settings->$institutioncompactlogo)) {
                        // Hide the requested size in the file path.
                        $filepath = '0x70/';

                        // Use $CFG->themerev to prevent browser caching when the file changes.
                        $this->compactlogourl = moodle_url::make_pluginfile_url(
                            context_system::instance()->id,
                            'theme_ned_boost',
                            $institutioncompactlogo,
                            $filepath,
                            \theme_get_revision(),
                            $this->page->theme->settings->$institutioncompactlogo
                        );
                    }

                    if (!empty($this->page->theme->settings->$institutionlogo)) {
                        // Hide the requested size in the file path.
                        $filepath = '0x150/';

                        // Use $CFG->themerev to prevent browser caching when the file changes.
                        $this->logourl = moodle_url::make_pluginfile_url(
                            context_system::instance()->id,
                            'theme_ned_boost',
                            $institutionlogo,
                            $filepath,
                            \theme_get_revision(),
                            $this->page->theme->settings->$institutionlogo
                        );
                    }

                    break;
                }
            }
        }
    }

    /**
     * Get the compact logo URL.
     *
     * @return string
     */
    public function get_compact_logo_url($maxwidth = 100, $maxheight = 100) {
        if ($this->compactlogourl == null) {
            $this->compactlogourl = parent::get_compact_logo_url($maxwidth, $maxheight);
        }

        return $this->compactlogourl;
    }

    /**
     * Return the site's logo URL, if any.
     *
     * @param int $maxwidth The maximum width, or null when the maximum width does not matter.
     * @param int $maxheight The maximum height, or null when the maximum height does not matter.
     * @return moodle_url|false
     */
    public function get_logo_url($maxwidth = null, $maxheight = 200) {
        if ($this->logourl == null) {
            $this->logourl = parent::get_logo_url($maxwidth, $maxheight);
        }

        return $this->logourl;
    }

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
            switch ($this->page->theme->settings->coursehamburgerbutton) {
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

        require_once($CFG->libdir.'/behat/lib.php');

        $templatecontext = $this->get_dynamicbase();
        $extraclasses = $this->get_navdraweropen($templatecontext, $shownavdrawer);
        $bodyattributes = $this->body_attributes($extraclasses);
        $templatecontext['bodyattributes'] = $bodyattributes;

        $position = (!empty($this->page->theme->settings->courselevelblockpositions)) ? $this->page->theme->settings->courselevelblockpositions : 2; // Right.
        $this->determine_dynamic_block_positions($templatecontext, $position);

        if ($CFG->branch >= 34) {
            $templatecontext['hasactivity_navigation'] = true;
        }

        return $templatecontext;
    }

    public function get_frontdashboard_context() {
        global $CFG;
        require_once($CFG->libdir.'/behat/lib.php');

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
        require_once($CFG->libdir.'/behat/lib.php');

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

    /**
     * Prints a nice side block with an optional header.
     *
     * @param block_contents $bc HTML for the content
     * @param string $region the region the block is appearing in.
     * @return string the HTML to be output.
     */
    public function block(block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        }

        $title = $bc->title;
        $showheader = true;

        if (empty($title)) {
            if (!$this->page->user_is_editing()) {
                $showheader = false;
            }
        } else {
            $toolbox = \theme_ned_boost\toolbox::get_instance();
            $customiseindividualblocks = $toolbox->get_customiseindividualblocks($this->page->theme);
            if (!empty($customiseindividualblocks)) {
                foreach ($customiseindividualblocks as $blockname => $blocksettings) {
                    if ($blockname == $bc->attributes['data-block']) {
                        if (!empty($blocksettings[\theme_ned_boost\toolbox::$fontawesomekey])) {
                            $title = $toolbox->getfontawesomemarkup($blocksettings[\theme_ned_boost\toolbox::$fontawesomekey]).$title;
                        }
                        break;
                    }
                }
            }
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid;
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->type = $bc->attributes['data-block'];
        $context->title = $title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);
        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }
        $context->showheader = $showheader;

        return $this->render_from_template('core/block', $context);
    }

    /**
     * Returns standard navigation between activities in a course.
     *
     * M3.4 onwards but should be harmless in less than as long as not called.
     *
     * @return string the navigation HTML.
     */
    public function activity_navigation() {
        // First we should check if we want to add navigation.
        $context = $this->page->context;
        if (($this->page->pagelayout !== 'incourse' && $this->page->pagelayout !== 'frametop')
            || $context->contextlevel != CONTEXT_MODULE) {
            return '';
        }

        // If the activity is in stealth mode, show no links.
        if ($this->page->cm->is_stealth()) {
            return '';
        }

        // Get a list of all the activities in the course.
        $course = $this->page->cm->get_course();
        $modules = get_fast_modinfo($course->id)->get_cms();

        // Put the modules into an array in order by the position they are shown in the course.
        $mods = [];
        $activitylist = [];
        foreach ($modules as $module) {
            // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
            if (!$module->uservisible || $module->is_stealth() || empty($module->url)) {
                continue;
            }
            $mods[$module->id] = $module;

            // No need to add the current module to the list for the activity dropdown menu.
            if ($module->id == $this->page->cm->id) {
                continue;
            }
            // Module name.
            $modname = $module->get_formatted_name();
            // Display the hidden text if necessary.
            if (!$module->visible) {
                $modname .= ' ' . get_string('hiddenwithbrackets');
            }

            if ((!empty($this->page->theme->settings->jumptomenu)) && ($this->page->theme->settings->jumptomenu == 2)) {
                // Module URL.
                $linkurl = new moodle_url($module->url, array('forceview' => 1));
                // Add module URL (as key) and name (as value) to the activity list array.
                $activitylist[$linkurl->out(false)] = $modname;
            }
        }

        $nummods = count($mods);

        // If there is only one mod then do nothing.
        if ($nummods == 1) {
            return '';
        }

        // Get an array of just the course module ids used to get the cmid value based on their position in the course.
        $modids = array_keys($mods);

        // Get the position in the array of the course module we are viewing.
        $position = array_search($this->page->cm->id, $modids);

        $prevmod = null;
        $nextmod = null;

        if ((!empty($this->page->theme->settings->forwardbacklinks)) && ($this->page->theme->settings->forwardbacklinks == 2)) {
            // Check if we have a previous mod to show.
            if ($position > 0) {
                $prevmod = $mods[$modids[$position - 1]];
            }

            // Check if we have a next mod to show.
            if ($position < ($nummods - 1)) {
                $nextmod = $mods[$modids[$position + 1]];
            }
        }

        $activitynav = new \core_course\output\activity_navigation($prevmod, $nextmod, $activitylist);
        $renderer = $this->page->get_renderer('core', 'course');
        return $renderer->render($activitynav);
    }
}
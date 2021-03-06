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
use html_writer;
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
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        $acourse = (($this->page->pagelayout == 'course') || ($this->page->pagelayout == 'incourse'));
        $pageheaderclass = 'row';
        $headerinfo = null;
        if ($acourse) {
            $pageheaderclass .= ' acourse';

            $nctoolbox = \local_ned_controller\toolbox::get_instance();
            $categoryicons = $nctoolbox->get_categoryicons();
            if (!empty($categoryicons)) {
                $category = \coursecat::get($this->get_current_category());
                if ((!empty($category)) && (array_key_exists($category->name, $categoryicons))) {
                    $toolbox = \theme_ned_boost\toolbox::get_instance();
                    $headerinfo = array('heading' => $toolbox->getfontawesomemarkup(
                        $categoryicons[$category->name][\local_ned_controller\toolbox::$fontawesomekey]).' '.$this->page->heading
                    );
                }
            }
        }
        $html = html_writer::start_tag('header', array('id' => 'page-header', 'class' => $pageheaderclass));
        $html .= html_writer::start_div('col-xs-12 p-a-1');
        $html .= html_writer::start_div('card');
        $html .= html_writer::start_div('card-block');

        if (!$acourse) {
            $html .= html_writer::start_div('pull-xs-left');
        } else {
            $html .= html_writer::start_div('nedcoursename');
        }
        $html .= $this->context_header($headerinfo);
        $html .= html_writer::end_div();
        $html .= html_writer::start_div('pull-xs-right acoursemenus');
        if (($this->page->pagelayout == 'dashboard') || ($this->page->pagelayout == 'frontpage')) {
            $html .= html_writer::div(html_writer::img($this->image_url('pbr', 'theme_ned_boost'), get_string('pbrpix', 'theme_ned_boost')), 'pbrpix');
        }
        $contextheadersettingsmenu = $this->context_header_settings_menu();
        if (!empty($contextheadersettingsmenu)) {
            $html .= html_writer::div($contextheadersettingsmenu, 'context-header-settings-menu');
        }
        if ($acourse) {
            $html .= html_writer::div($this->editing_button(), 'editing-button pull-right');

            $quicklinks = array();

            $manualenrolmentquicklink = $nctoolbox->get_manualenrollment_quicklink($this->page->context, $this->page->course->id);
            if ($manualenrolmentquicklink) {
                $icon = $this->pix_icon('t/enrolusers', get_string('quicklinksmanualenrollment', 'local_ned_controller'));
                $quicklinks[] = html_writer::link($manualenrolmentquicklink, $icon);
            }

            $courseparticipantsquicklink = $nctoolbox->get_courseparticipants_quicklink($this->page->context, $this->page->course->id);
            if ($courseparticipantsquicklink) {
                $icon = $this->pix_icon('t/groups', get_string('quicklinkscourseparticpants', 'local_ned_controller'));
                $quicklinks[] = html_writer::link($courseparticipantsquicklink, $icon);
            }

            if (!empty($quicklinks)) {
                $html .= html_writer::div(html_writer::span('', 'ned-divider'), 'pull-right');
                foreach ($quicklinks as $quicklink) {
                    $html .= html_writer::div($quicklink, 'quicklink');
                }
            }
        }
        $html .= html_writer::end_div();

        $pageheadingbutton = $this->page_heading_button();
        if ((empty($this->page->layout_options['nonavbar'])) &&
             (!(($this->page->pagelayout == 'course') || ($this->page->pagelayout == 'incourse')))) {
            $html .= html_writer::start_div('clearfix w-100 pull-xs-left', array('id' => 'page-navbar'));
            $html .= html_writer::tag('div', $this->navbar(), array('class' => 'breadcrumb-nav'));
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button pull-xs-right');
            $html .= html_writer::end_div();
        } else if ($pageheadingbutton) {
            $html .= html_writer::div($pageheadingbutton, 'breadcrumb-button nonavbar pull-xs-right');
        }
        $html .= html_writer::tag('div', $this->course_header(), array('id' => 'course-header'));
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_div();
        $html .= html_writer::end_tag('header');
        return $html;
    }

    protected function get_current_category() {
        $catid = 0;

        if (is_array($this->page->categories)) {
            $catids = array_keys($this->page->categories);
            $catid = reset($catids);
        } else if (!empty($this->page->course->category)) {
            $catid = $this->page->course->category;
        }

        return $catid;
    }

    /**
     * Whether we should display the logo in the navbar.
     *
     * We will when there are no main logos, and we have compact logo.
     *
     * @return bool
     */
    public function should_display_navbar_logo() {
        if (($this->page->pagelayout == 'course') || ($this->page->pagelayout == 'incourse')) {
            return false;
        }
        $logo = $this->get_compact_logo_url();
        return !empty($logo) && !$this->should_display_main_logo();
    }

    /**
     * Override to inject the logo.
     *
     * @param array $headerinfo The header info.
     * @param int $headinglevel What level the 'h' tag will be.
     * @return string HTML for the header bar.
     */
    public function context_header($headerinfo = null, $headinglevel = 1) {
        global $SITE;

        if ($this->should_display_main_logo($headinglevel)) {
            $sitename = format_string($SITE->fullname, true, array('context' => \context_course::instance(SITEID)));
            return html_writer::div(html_writer::empty_tag('img', [
                'src' => $this->get_logo_url(null, 150), 'alt' => $sitename, 'class' => 'img-responsive']), 'logo');
        }

        return \core_renderer::context_header($headerinfo, $headinglevel); // Bypass Boost's method.
    }

    public function editing_button() {
        $html = '';
        if ($this->page->user_allowed_editing()) {
            $pagetype = $this->page->pagetype;
            if (strpos($pagetype, 'admin-setting') !== false) {
                $pagetype = 'admin-setting'; // Deal with all setting page types.
            } else if ((strpos($pagetype, 'mod-') !== false) &&
                ((strpos($pagetype, 'edit') !== false) ||
                 (strpos($pagetype, 'view') !== false) ||
                 (strpos($pagetype, '-mod') !== false))) {
                $pagetype = 'mod-edit-view'; // Deal with all mod edit / view / -mod page types.
            } else if (strpos($pagetype, 'mod-data-field') !== false) {
                $pagetype = 'mod-data-field'; // Deal with all mod data field page types.
            } else if (strpos($pagetype, 'mod-lesson') !== false) {
                $pagetype = 'mod-lesson'; // Deal with all mod lesson page types.
            }
            switch ($pagetype) {
                case 'site-index':
                case 'calendar-view':  // Slightly faulty as even the navigation link goes back to the frontpage.  TODO: MDL.
                    $url = new moodle_url('/course/view.php');
                    $url->param('id', 1);
                    if ($this->page->user_is_editing()) {
                        $url->param('edit', 'off');
                    } else {
                        $url->param('edit', 'on');
                    }
                break;
                case 'admin-index':
                case 'admin-setting':
                    $url = $this->page->url;
                    if ($this->page->user_is_editing()) {
                        $url->param('adminedit', 0);
                    } else {
                        $url->param('adminedit', 1);
                    }
                break;
                case 'course-index':
                case 'course-management':
                case 'course-search':
                case 'mod-resource-mod':
                case 'tag-search':
                    $url = new moodle_url('/tag/search.php');
                    if ($this->page->user_is_editing()) {
                        $url->param('edit', 'off');
                    } else {
                        $url->param('edit', 'on');
                    }
                break;
                case 'mod-data-field':
                case 'mod-edit-view':
                case 'mod-forum-discuss':
                case 'mod-forum-index':
                case 'mod-forum-search':
                case 'mod-forum-subscribers':
                case 'mod-lesson':
                case 'mod-quiz-index':
                case 'mod-scorm-player':
                    $url = new moodle_url('/course/view.php');
                    $url->param('id', $this->page->course->id);
                    $url->param('return', $this->page->url->out_as_local_url(false));
                    if ($this->page->user_is_editing()) {
                        $url->param('edit', 'off');
                    } else {
                        $url->param('edit', 'on');
                    }
                break;
                case 'my-index':
                case 'user-profile':
                    // TODO: Not sure how to get 'id' param and if it is really needed.
                    $url = $this->page->url;
                    // Umm! Both /user/profile.php and /user/profilesys.php have the same page type but different parameters!
                    if ($this->page->user_is_editing()) {
                        $url->param('adminedit', 0);
                        $url->param('edit', 0);
                    } else {
                        $url->param('adminedit', 1);
                        $url->param('edit', 1);
                    }
                break;
                default:
                    $url = $this->page->url;
                    if ($this->page->user_is_editing()) {
                        $url->param('edit', 'off');
                    } else {
                        $url->param('edit', 'on');
                    }
                break;
            }

            $url->param('sesskey', sesskey());
            if ($this->page->user_is_editing()) {
                $editstring = get_string('turneditingoff');
                $colourclass = 'edit-on';
            } else {
                $editstring = get_string('turneditingon');
                $colourclass = 'edit-off';
            }
            $toolbox = \theme_ned_boost\toolbox::get_instance();
            $edit = $toolbox->getfontawesomemarkup('fa fa-pencil-square-o', array('fa-fw'));
            $html = html_writer::link($url, $edit, array('title' => $editstring, 'class' => $colourclass));
        }

        return $html;
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
        $templatecontext['homeicon'] = true;

        if (empty($this->page->layout_options['nonavbar'])) {
            $navbaritems = $this->page->navbar->get_items();
            $navbaritems = array_slice($navbaritems, 2);
            $navbarcontext = ['get_items' => $navbaritems];
            $templatecontext['headernavbar'] = html_writer::div($this->render_from_template('core/navbar', $navbarcontext), 'coursenavbar');
        }

        $position = (!empty($this->page->theme->settings->courselevelblockpositions)) ? $this->page->theme->settings->courselevelblockpositions : 2; // Right.
        $this->determine_dynamic_block_positions($templatecontext, $position);

        if ($CFG->branch >= 34) {
            $templatecontext['hasactivity_navigation'] = true;
        }

        return $templatecontext;
    }

    public function get_frontdashboard_context() {
        global $CFG;

        if (!empty($this->page->theme->settings->frontpagedashboardhamburgerbutton)) {
            switch ($this->page->theme->settings->frontpagedashboardhamburgerbutton) {
                case 1: // Show.
                    $shownavdrawer = true;
                    break;
                case 2: // Hide.
                    $shownavdrawer = false;
                    break;
                default: // Is 3 being 'capability'.
                    $shownavdrawer = has_capability('theme/ned_boost:shownavdrawer', \context_course::instance($this->page->course->id)); // This should be the site id.
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

    /**
     * Renders an action menu component.
     *
     * ARIA references:
     *   - http://www.w3.org/WAI/GL/wiki/Using_ARIA_menus
     *   - http://stackoverflow.com/questions/12279113/recommended-wai-aria-implementation-for-navigation-bar-menu
     *
     * @param theme_ned_boost\ned_action_menu $menu
     * @return string HTML
     */
    public function render_ned_action_menu(\theme_ned_boost\ned_action_menu $menu) {
        $context = $menu->export_for_template($this);
        return $this->render_from_template('theme_ned_boost/ned_action_menu', $context);
    }
}
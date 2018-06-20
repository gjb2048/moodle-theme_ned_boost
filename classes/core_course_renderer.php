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

defined('MOODLE_INTERNAL') || die;

class theme_ned_boost_core_course_renderer extends core_course_renderer {
    private static $nctoolbox = null;
    private static $nedboostcoursenameactionmenujs = false;
    private static $themesettings = null;
    private $editingoff;

    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        if (empty(self::$nctoolbox)) {
            self::$nctoolbox = \local_ned_controller\toolbox::get_instance();
        }
        if (empty(self::$themesettings)) {
            self::$themesettings = theme_config::load('ned_boost')->settings;
        }
        $this->editingoff = !$page->user_is_editing();
    }

    /**
     * Renders html to display a course search form
     *
     * @param string $value default value to populate the search field
     * @param string $format display format - 'plain' (default), 'short' or 'navbar'
     * @return string
     */
    function course_search_form($value = '', $format = 'plain') {
        static $count = 0;
        $formid = 'coursesearch';
        if ((++$count) > 1) {
            $formid .= $count;
        }

        switch ($format) {
            case 'navbar' :
                $formid = 'coursesearchnavbar';
                $inputid = 'navsearchbox';
                $inputsize = 20;
                break;
            case 'short' :
                $inputid = 'shortsearchbox';
                $inputsize = 12;
                break;
            default :
                $inputid = 'coursesearchbox';
                $inputsize = 30;
        }

        $strsearchcourses = get_string("searchcourses");
        $searchurl = new moodle_url('/course/search.php');

        $output = html_writer::start_tag('form', array('id' => $formid, 'action' => $searchurl, 'method' => 'get'));
        $output .= html_writer::start_tag('fieldset', array('class' => 'coursesearchbox invisiblefieldset nedcoursesearchbox pull-right'));
        $output .= html_writer::empty_tag('input', array('type' => 'text', 'id' => $inputid,
            'size' => $inputsize, 'name' => 'search', 'value' => s($value)));
        $output .= html_writer::tag('label', $strsearchcourses, array('for' => $inputid));
        $output .= html_writer::empty_tag('input', array('type' => 'submit',
            'value' => get_string('go'), 'class' => 'btn'));
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');

        return $output;
    }

    /**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name_title(cm_info $mod, $displayoptions = array()) {
        $output = '';
        $url = $mod->url;
        if (!$mod->is_visible_on_course_page() || !$url) {
            // Nothing to be displayed to the user.
            return $output;
        }

        // Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        /* Avoid unnecessary duplication: if e.g. a forum name already
           includes the word forum (or Forum, etc) then it is unhelpful
           to include that in the accessible description that is added. */
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        list($linkclasses, $textclasses) = $this->course_section_cm_classes($mod);

        /* Get on-click attribute value if specified and decode the onclick - it
           has already been encoded for display. */
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);

        // Start of NED Boost specific changes.
        if ($this->editingoff) {
            if (($mod->modname == 'url') && ((!empty(self::$themesettings->urlresourcelink)) && (self::$themesettings->urlresourcelink == 2))) {
                global $DB;

                $modurl = $DB->get_record('url', array('id' => $mod->instance), '*', MUST_EXIST);
                $url = $modurl->externalurl;
            }
            if (($mod->modname == 'questionnaire') && ((!empty(self::$themesettings->questionnaireactivitylink)) && (self::$themesettings->questionnaireactivitylink == 2))) {
                global $CFG, $DB, $USER;
                require_once($CFG->dirroot.'/mod/questionnaire/locallib.php');
                require_once($CFG->dirroot.'/mod/questionnaire/questionnaire.class.php');

                // TODO: Could this be more efficient?
                list($cm, $course, $questionnaire) = questionnaire_get_standard_page_items($mod->id, null);
                $questionnaire = new questionnaire(0, $questionnaire, $course, $cm);

                if (($questionnaire->user_can_take($USER->id)) && ($questionnaire->questions)) {
                    $newurl = new moodle_url('/mod/questionnaire/complete.php', array('id' => $questionnaire->cm->id));
                    if ($questionnaire->user_has_saved_response($USER->id)) {
                        $newurl->param('resume', 1);
                        $instancename .= ' - '.get_string('resumesurvey', 'questionnaire');
                    }
                    $url = $newurl;
                }
            }
        }
        // End of NED Boost specific changes.

        // Display link itself.

        // Start of NED Boost specific changes.
        // We should search only for reference to FontAwesome icons.  From the FontAwesome filter: https://moodle.org/plugins/pluginversions.php?plugin=filter_fontawesome.
        $fasearch = "(\[(fa-.*?)\])is";
        $instancename = preg_replace_callback($fasearch, array($this, 'fa_callback'), $instancename);
        // End of NED Boost specific changes.

        $activitylink = html_writer::empty_tag('img', array('src' => $mod->get_icon_url(),
            'class' => 'iconlarge activityicon', 'alt' => ' ', 'role' => 'presentation')) .
            html_writer::tag('span', $instancename . $altname, array('class' => 'instancename'));
        if ($mod->uservisible) {
            $output .= html_writer::link($url, $activitylink, array('class' => $linkclasses, 'onclick' => $onclick));
        } else {
            /* We may be displaying this just in order to show information
               about visibility, without the actual link ($mod->is_visible_on_course_page()). */
            $output .= html_writer::tag('div', $activitylink, array('class' => $textclasses));
        }
        return $output;
    }

    /**
     * Displays one course in the list of courses.
     *
     * This is an internal function, to display an information about just one course
     * please use {@link core_course_renderer::course_info_box()}
     *
     * @param coursecat_helper $chelper various display options
     * @param course_in_list|stdClass $course
     * @param string $additionalclasses additional classes to add to the main <div> tag (usually
     *    depend on the course position in list - first/last/even/odd)
     * @return string
     */
    protected function coursecat_coursebox(coursecat_helper $chelper, $course, $additionalclasses = '') {
        global $CFG, $OUTPUT;
        if (!isset($this->strings->summary)) {
            $this->strings->summary = get_string('summary');
        }
        if ($chelper->get_show_courses() <= self::COURSECAT_SHOW_COURSES_COUNT) {
            return '';
        }
        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content = '';
        $classes = trim('coursebox clearfix '. $additionalclasses);
        if ($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_EXPANDED) {
            $nametag = 'h3';
        } else {
            $classes .= ' collapsed';
            $nametag = 'div';
        }

        // .coursebox
        $content .= html_writer::start_tag('div', array(
            'class' => $classes,
            'data-courseid' => $course->id,
            'data-type' => self::COURSECAT_TYPE_COURSE,
        ));

        $content .= html_writer::start_tag('div', array('class' => 'info'));

        // Course name.
        $coursename = $chelper->get_course_formatted_name($course);
        $coursenamelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
            $coursename, array('class' => $course->visible ? '' : 'dimmed'));
        $content .= html_writer::tag($nametag, $coursenamelink, array('class' => 'coursename'));

        // Begin NED Boost specific changes.
        $menu = new \theme_ned_boost\ned_action_menu();
        $menu->attributessecondary['class'] .= ' nedcoursename';

        $coursecontext = context_course::instance($course->id);
        $editsettingsquicklink = self::$nctoolbox->get_editsettings_quicklink($coursecontext, $course->id, 'coursename');
        if ($editsettingsquicklink) {
            $menuitemstring = get_string('editsettings');
            $menu->add_secondary_action(new action_link($editsettingsquicklink, $menuitemstring, null, null, new pix_icon('t/edit', $menuitemstring)));
        }

        $courseparticipantsquicklink = self::$nctoolbox->get_courseparticipants_quicklink($coursecontext, $course->id, 'coursename');
        if ($courseparticipantsquicklink) {
            $menuitemstring = get_string('quicklinkscourseparticpants', 'local_ned_controller');
            $menu->add_secondary_action(new action_link($courseparticipantsquicklink, $menuitemstring, null, null, new pix_icon('t/groups', $menuitemstring)));
        }

        $manualenrolmentquicklink = self::$nctoolbox->get_manualenrollment_quicklink($coursecontext, $course->id, 'coursename');
        if ($manualenrolmentquicklink) {
            $menuitemstring = get_string('quicklinksmanualenrollments', 'local_ned_controller');
            $menu->add_secondary_action(new action_link($manualenrolmentquicklink, $menuitemstring, null, null, new pix_icon('t/enrolusers', $menuitemstring)));
        }

        $nedprogressreportquicklink = self::$nctoolbox->get_nedprogressreport_quicklink($coursecontext, $course->id, 'coursename');
        if ($nedprogressreportquicklink) {
            $menuitemstring = get_string('quicklinksnedprogressreport', 'local_ned_controller');
            $menu->add_secondary_action(new action_link($nedprogressreportquicklink, $menuitemstring, null, null, new pix_icon('i/grades', $menuitemstring)));
        }

        $nedmarkingmanagerquicklink = self::$nctoolbox->get_nedmarkingmanager_quicklink($coursecontext, $course->id, 'coursename');
        if ($nedmarkingmanagerquicklink) {
            $menuitemstring = get_string('quicklinksnedmarkingmanager', 'local_ned_controller');
            $menu->add_secondary_action(new action_link($nedmarkingmanagerquicklink, $menuitemstring, null, null, new pix_icon('i/competencies', $menuitemstring)));
        }

        $gradebookquicklink = self::$nctoolbox->get_gradebook_quicklink($coursecontext, $course->id, 'coursename');
        if ($gradebookquicklink) {
            $menuitemstring = get_string('quicklinksgradebook', 'local_ned_controller');
            $menu->add_secondary_action(new action_link($gradebookquicklink, $menuitemstring, null, null, new pix_icon('i/report', $menuitemstring)));
        }

        $menu->set_menu_trigger(' ');
        $content .= $OUTPUT->render($menu);
        if (empty(self::$nedboostcoursenameactionmenujs)) {
            global $PAGE;
            $PAGE->requires->js_call_amd('theme_ned_boost/ned_boost_course_name_action_menu', 'init');
            self::$nedboostcoursenameactionmenujs = true;
        }
        // End NED Boost specific changes.

        // If we display course in collapsed form but the course has summary or course contacts, display the link to the info page.
        $content .= html_writer::start_tag('div', array('class' => 'moreinfo'));
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            if ($course->has_summary() || $course->has_course_contacts() || $course->has_course_overviewfiles()) {
                $url = new moodle_url('/course/info.php', array('id' => $course->id));
                $image = $this->output->pix_icon('i/info', $this->strings->summary);
                $content .= html_writer::link($url, $image, array('title' => $this->strings->summary));
                // Make sure JS file to expand course content is included.
                $this->coursecat_include_js();
            }
        }
        $content .= html_writer::end_tag('div'); // .moreinfo

        // Print enrolmenticons.
        if ($icons = enrol_get_course_info_icons($course)) {
            $content .= html_writer::start_tag('div', array('class' => 'enrolmenticons'));
            foreach ($icons as $pixicon) {
                $content .= $this->render($pixicon);
            }
            $content .= html_writer::end_tag('div'); // .enrolmenticons
        }

        $content .= html_writer::end_tag('div'); // .info

        $content .= html_writer::start_tag('div', array('class' => 'content'));
        $content .= $this->coursecat_coursebox_content($chelper, $course);
        $content .= html_writer::end_tag('div'); // .content

        $content .= html_writer::end_tag('div'); // .coursebox
        return $content;
    }

    /**
     * Returns HTML to display course content (summary, course contacts and optionally category name)
     *
     * This method is called from coursecat_coursebox() and may be re-used in AJAX
     *
     * @param coursecat_helper $chelper various display options
     * @param stdClass|course_in_list $course
     * @return string
     */
    protected function coursecat_coursebox_content(coursecat_helper $chelper, $course) {
        global $CFG;
        if ($chelper->get_show_courses() < self::COURSECAT_SHOW_COURSES_EXPANDED) {
            return '';
        }
        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content = '';

        // Display course summary.
        if ($course->has_summary()) {
            $content .= html_writer::start_tag('div', array('class' => 'summary'));
            $content .= $chelper->get_course_formatted_summary($course,
                    array('overflowdiv' => true, 'noclean' => true, 'para' => false));
            $content .= html_writer::end_tag('div'); // .summary
        }

        // Display course overview files.
        $contentimages = $contentfiles = '';
        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) { // Begin NED Boost specific changes.
                $courseimagelink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)),
                    html_writer::empty_tag('img', array('src' => $url)), array('class' => $course->visible ? '' : 'dimmed'));
                $contentimages .= html_writer::tag('div',
                        $courseimagelink,
                        array('class' => 'courseimage'));
            } else { // End NED Boost specific changes.
                $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
                $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                        html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
                $contentfiles .= html_writer::tag('span',
                        html_writer::link($url, $filename),
                        array('class' => 'coursefile fp-filename-icon'));
            }
        }
        $content .= $contentimages. $contentfiles;

        // Display course contacts. See course_in_list::get_course_contacts().
        if ($course->has_course_contacts()) {
            $content .= html_writer::start_tag('ul', array('class' => 'teachers'));
            foreach ($course->get_course_contacts() as $userid => $coursecontact) {
                $name = $coursecontact['rolename'].': '.
                        html_writer::link(new moodle_url('/user/view.php',
                                array('id' => $userid, 'course' => SITEID)),
                            $coursecontact['username']);
                $content .= html_writer::tag('li', $name);
            }
            $content .= html_writer::end_tag('ul'); // .teachers
        }

        // Display course category if necessary (for example in search results).
        if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT) {
            require_once($CFG->libdir. '/coursecatlib.php');
            if ($cat = coursecat::get($course->category, IGNORE_MISSING)) {
                $content .= html_writer::start_tag('div', array('class' => 'coursecat'));
                $content .= get_string('category').': '.
                        html_writer::link(new moodle_url('/course/index.php', array('categoryid' => $cat->id)),
                                $cat->get_formatted_name(), array('class' => $cat->visible ? '' : 'dimmed'));
                $content .= html_writer::end_tag('div'); // .coursecat
            }
        }

        return $content;
    }

    private function fa_callback(array $matches) {
        return '<i class="fa '.$matches[1].'"></i>';
    }

    /**
     * Displays availability info for a course section or course module
     *
     * @param string $text
     * @param string $additionalclasses
     * @return string
     */
    public function availability_info($text, $additionalclasses = '') {
        $showavailabilityinfo = has_capability('theme/ned_boost:showavailabilityinfo', \context_course::instance($this->page->course->id));

        $output = '';
        if ($showavailabilityinfo) {
            $output = parent::availability_info($text, $additionalclasses);
        }

        return $output;
    }
}
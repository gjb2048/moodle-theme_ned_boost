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

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);
require_once($CFG->libdir . '/behat/lib.php');

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}
$extraclasses = [];

$shownavdrawer = has_capability('theme/ned_boost:shownavdrawer', context_course::instance($OUTPUT->page->course->id));
if ($navdraweropen && $shownavdrawer) {
    $extraclasses[] = 'drawer-open-left';
}

$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockslefthtml = $OUTPUT->blocks('side-pre');
$hasleftblocks = strpos($blockslefthtml, 'data-block=') !== false;
$blocksrighthtml = $OUTPUT->blocks('side-post');
$hasrightblocks = strpos($blocksrighthtml, 'data-block=') !== false;
$hasblocks = ($hasleftblocks || $hasrightblocks);

switch ($OUTPUT->get_block_postions()) { // 1 is 'Both' - default for this layout.
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
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();

$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'output' => $OUTPUT,
    'hasblocks' => $hasblocks,
    'bothblocks' => $bothblocks,
    'sidepreblocks' => $blockslefthtml,
    'hasleftblocks' => $hasleftblocks,
    'sidepostblocks' => $blocksrighthtml,
    'hasrightblocks' => $hasrightblocks,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'shownavdrawer' => $shownavdrawer
];

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_ned_boost/layout', $templatecontext);


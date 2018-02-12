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

if ($ADMIN->fulltree) {
    $settings = new theme_boost_admin_settingspage_tabs('themesettingned_boost', get_string('configtitle', 'theme_ned_boost'));
    // Format tab.
    $page = new admin_settingpage('theme_ned_boost_format', get_string('formatsettings', 'theme_ned_boost'));

    // Frontpage / dashboard level.
    $page->add(new admin_setting_heading('theme_ned_boost_frontpagedashboardlevelheading',
        get_string('frontpagedashboardlevel', 'theme_ned_boost'), ''));

    // Block width.
    $name = 'theme_ned_boost/frontpagedashboardlevelblockwidth';
    $title = get_string('frontpagedashboardblockwidth', 'theme_ned_boost');
    $description = get_string('blockwidthdesc', 'theme_ned_boost');
    $default = 350;
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_INT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Block positions.
    $name = 'theme_ned_boost/frontpagedashboardlevelblockpositions';
    $title = get_string('frontpagedashboardblockpositions', 'theme_ned_boost');
    $description = '';
    $choices = array(
        1 => new lang_string('both', 'theme_ned_boost'),
        2 => new lang_string('right', 'theme_ned_boost'),
        3 => new lang_string('left', 'theme_ned_boost')
    );
    $default = 2;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Course level.
    $page->add(new admin_setting_heading('theme_ned_boost_courselevelheading',
        get_string('courselevel', 'theme_ned_boost'), ''));

    // Course hamburger button.
    $name = 'theme_ned_boost/coursehamburgerbutton';
    $title = get_string('coursehamburgerbutton', 'theme_ned_boost');
    $description = '';
    $choices = array(
        1 => new lang_string('show'),
        2 => new lang_string('hide'),
        3 => new lang_string('userolepermission', 'theme_ned_boost')
    );
    $default = 1;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $page->add($setting);

    // Block width.
    $name = 'theme_ned_boost/courselevelblockwidth';
    $title = get_string('courseblockwidth', 'theme_ned_boost');
    $description = get_string('blockwidthdesc', 'theme_ned_boost');
    $default = 350;
    $setting = new admin_setting_configtext($name, $title, $description, $default, PARAM_INT);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Block positions.
    $name = 'theme_ned_boost/courselevelblockpositions';
    $title = get_string('courseblockpositions', 'theme_ned_boost');
    $description = '';
    $choices = array(
        1 => new lang_string('both', 'theme_ned_boost'),
        2 => new lang_string('right', 'theme_ned_boost'),
        3 => new lang_string('left', 'theme_ned_boost')
    );
    $default = 3;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $choices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);

    // If-then tab.
    $page = new admin_settingpage('theme_ned_boost_ifthen', get_string('ifthen', 'theme_ned_boost'));

    // Frontpage / dashboard level.
    $page->add(new admin_setting_heading('theme_ned_boost_headerlogoheading',
        get_string('headerlogo', 'theme_ned_boost'), ''));

    // Institution count.
    $name = 'theme_ned_boost/userinstitutioncount';
    $title = get_string('userinstitutioncount', 'theme_ned_boost');
    $description = get_string('userinstitutioncountdesc', 'theme_ned_boost');
    $uichoices = array();
    for ($uicount = 1; $uicount <= 10; $uicount++) {
        $uichoices[$uicount] = $uicount;
    }
    $default = 4;
    $setting = new admin_setting_configselect($name, $title, $description, $default, $uichoices);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $numberofuserinstitutions = get_config('theme_ned_boost', 'userinstitutioncount');
    for ($institutionnumber = 1; $institutionnumber <= $numberofuserinstitutions; $institutionnumber++) {
        // User institution specific header logo.
        // User institution setting.
        $name = 'theme_ned_boost/userinstitution'.$institutionnumber;
        $title = get_string('ifinstitution', 'theme_ned_boost');
        $description = '';
        $default = '';
        $setting = new admin_setting_configtext($name, $title, $description, $default);
        $page->add($setting);

        // User institution logo file setting.
        $name = 'theme_ned_boost/institutionlogo'.$institutionnumber;
        $title = get_string('thenshow', 'theme_ned_boost').' '.get_string('logo', 'theme_ned_boost');
        $description = '';
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'institutionlogo'.$institutionnumber);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);

        // User institution compact logo file setting.
        $name = 'theme_ned_boost/institutioncompactlogo'.$institutionnumber;
        $title = get_string('compactlogo', 'theme_ned_boost');
        $description = '';
        $setting = new admin_setting_configstoredfile($name, $title, $description, 'institutioncompactlogo'.$institutionnumber);
        $setting->set_updatedcallback('theme_reset_all_caches');
        $page->add($setting);
    }
    $settings->add($page);

    // Colours.
    $page = new admin_settingpage('theme_ned_boost_colours', get_string('colours', 'theme_ned_boost'));

    // Main background colour setting.
    $name = 'theme_ned_boost/mainbackgroundcolour';
    $title = get_string('mainbackgroundcolour', 'theme_ned_boost');
    $description = '';
    $default = '#eceeef';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Nav-drawer background colour setting.
    $name = 'theme_ned_boost/navdrawerbackgroundcolour';
    $title = get_string('navdrawerbackgroundcolour', 'theme_ned_boost');
    $description = '';
    $default = '#dce0e2';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Block header background colour setting.
    $name = 'theme_ned_boost/blockheaderbackgroundcolour';
    $title = get_string('blockheaderbackgroundcolour', 'theme_ned_boost');
    $description = '';
    $default = '#dce0e2';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    // Block header text colour setting.
    $name = 'theme_ned_boost/blockheadertextcolour';
    $title = get_string('blockheadertextcolour', 'theme_ned_boost');
    $description = '';
    $default = '#333333';
    $previewconfig = null;
    $setting = new admin_setting_configcolourpicker($name, $title, $description, $default, $previewconfig);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    global $CFG;
    if (file_exists("{$CFG->dirroot}/theme/ned_boost/ned_boost_admin_setting_customiseindividualblocks.php")) {
        require_once($CFG->dirroot . '/theme/ned_boost/ned_boost_admin_setting_customiseindividualblocks.php');
    } else if (!empty($CFG->themedir) && file_exists("{$CFG->themedir}/ned_boost/ned_boost_admin_setting_customiseindividualblocks.php")) {
        require_once($CFG->themedir . '/ned_boost/ned_boost_admin_setting_customiseindividualblocks.php');
    }

    // Customise individual blocks setting.
    $name = 'theme_ned_boost/customiseindividualblocks';
    $title = get_string('customiseindividualblocks', 'theme_ned_boost');
    $description = '';
    $default = 'poll, fa fa-comment, #dc8d55, #ffffff;'.PHP_EOL.'login, fa fa-user, #e04f42, #ffffff, #ffffff';
    $setting = new ned_boost_admin_setting_customiseindividualblocks($name, $title, $description, $default);
    $setting->set_updatedcallback('theme_reset_all_caches');
    $page->add($setting);

    $settings->add($page);
}

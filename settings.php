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
}

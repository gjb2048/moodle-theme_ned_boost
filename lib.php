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

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_ned_boost_get_extra_scss($theme) {
    $toolbox = \theme_ned_boost\toolbox::get_instance();

    return $toolbox->get_extra_scss($theme);
}

/**
 * Inject SCSS content.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_ned_boost_get_main_scss_content($theme) {
    $toolbox = \theme_ned_boost\toolbox::get_instance();

    return $toolbox->get_main_scss_content($theme);
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_ned_boost_get_pre_scss($theme) {
    $toolbox = \theme_ned_boost\toolbox::get_instance();

    return $toolbox->get_pre_scss($theme);
}

/**
 * Serves any files associated with the theme settings.
 *
 * @param stdClass $course.
 * @param stdClass $cm.
 * @param context $context.
 * @param string $filearea.
 * @param array $args.
 * @param bool $forcedownload.
 * @param array $options.
 * @return bool.
 */
function theme_ned_boost_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    static $theme;
    if (empty($theme)) {
        $theme = theme_config::load('ned_boost');
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        // By default, theme files must be cache-able by both browsers and proxies.  From 'More' theme.
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if (preg_match("/^(institutionlogo|institutioncompactlogo)[1-9][0-9]*$/", $filearea)) {
            // Path hides the size, see: core_admin_pluginfile.
            global $CFG;

            $size = array_shift($args); // The path hides the size.
            $itemid = clean_param(array_shift($args), PARAM_INT);
            $filename = clean_param(array_shift($args), PARAM_FILE);
            $themerev = theme_get_revision();
            if ($themerev < 0) {
                // Normalise to 0 as -1 doesn't place well with paths.
                 $themerev = 0;
            }

            // Extract the requested width and height.
            $maxwidth = 0;
            $maxheight = 0;
            if (preg_match('/^\d+x\d+$/', $size)) {
                list($maxwidth, $maxheight) = explode('x', $size);
                $maxwidth = clean_param($maxwidth, PARAM_INT);
                $maxheight = clean_param($maxheight, PARAM_INT);
            }

            $lifetime = 0;
            if ($itemid > 0 && $themerev == $itemid) {
                // The itemid is $CFG->themerev, when 0 or less no caching. Also no caching when they don't match.
                $lifetime = DAYSECS * 60;
            }

            // Check if we've got a cached file to return. When lifetime is 0 then we don't want to cached one.
            $candidate = $CFG->localcachedir . "/theme_ned_boost/$themerev/$filearea/{$maxwidth}x{$maxheight}/$filename";
            if (file_exists($candidate) && $lifetime > 0) {
                send_file($candidate, $filename, $lifetime, 0, false, false, '', false, $options);
            }

            // Find the original file.
            $fs = get_file_storage();
            $filepath = "/{$context->id}/theme_ned_boost/{$filearea}/0/{$filename}";
            if (!$file = $fs->get_file_by_hash(sha1($filepath))) {
                send_file_not_found();
            }

            // No need for resizing, but if the file should be cached we save it so we can serve it fast next time.
            if (empty($maxwidth) && empty($maxheight)) {
                if ($lifetime) {
                    file_safe_save_content($file->get_content(), $candidate);
                }
                send_stored_file($file, $lifetime, 0, false, $options);
            }

            // Proceed with the resizing.
            $filedata = $file->resize_image($maxwidth, $maxheight);
            if (!$filedata) {
                send_file_not_found();
            }

            // If we don't want to cached the file, serve now and quit.
            if (!$lifetime) {
                send_content_uncached($filedata, $filename);
            }

            // Save, serve and quit.
            file_safe_save_content($filedata, $candidate);
            send_file($candidate, $filename, $lifetime, 0, false, false, '', false, $options);
        } else {
            send_file_not_found();
        }
    } else {
        send_file_not_found();
    }
}

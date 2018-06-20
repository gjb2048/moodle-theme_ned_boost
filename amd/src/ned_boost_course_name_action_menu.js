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
 * NED Boost theme.
 *
 * @package    theme
 * @subpackage ned_boost
 * @copyright  &copy; 2018-onwards G J Barnard.
 * @author     G J Barnard - {@link http://moodle.org/user/profile.php?id=442195}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/* jshint ignore:start */
define(['jquery', 'core/log'], function ($, log) {

    "use strict"; // jshint ;_;

    log.debug('Theme NED Boost Course Name Action Menu jQuery AMD');

    return {
        init: function () {
            log.debug('Theme NED Boost Course Name Action Menu AMD init initialised');

            $(document).ready(function () {
                if ($('.nedcoursename').length) {
                    $('.nedcoursename a').click(function (e) {
                        e.preventDefault();
                        var href = $(this).attr('href');
                        var mywindow = window.open(href, "",
                            "scrollbars=yes, toolbar=yes, width=800, height=600");
                        mywindow.focus();
                    });
                }
            });
        }
    };
});
/* jshint ignore:end */

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

class ned_boost_admin_setting_customiseindividualblocks extends admin_setting_configtextarea {

    // Because parent has $rows and $cols as 'private' then we need to store duplicates as cannot access!
    protected $therows;
    protected $thecols;
    // Internal JSON representation of the string.
    protected $json;

    /**
     * @param string $name
     * @param string $visiblename
     * @param string $description
     * @param mixed $defaultsetting string or array
     * @param string $cols The number of columns to make the editor
     * @param string $rows The number of rows to make the editor
     */
    public function __construct($name, $visiblename, $description, $defaultsetting, $cols = '60', $rows = '8') {
        $this->therows = $rows;
        $this->thecols = $cols;
        // We decide the type.
        parent::__construct($name, $visiblename, $description, $defaultsetting, PARAM_RAW, $cols, $rows);
    }

    /**
     * Returns an XHTML string for the editor.
     *
     * @param string $data
     * @param string $query
     * @return string XHTML string for the editor.
     */
    public function output_html($data, $query = '') {
        global $OUTPUT;

        $default = $this->get_defaultsetting();
        $defaultinfo = $default;
        if (!is_null($default) and $default !== '') {
            $defaultinfo = "\n".$default;
        }

        if (empty($data)) {
            $decodeddata = '';
        } else {
            /* If the string starts with a '{' then it could be JSON otherwise there was an error and the decoded setting has
              been returned. */
            if ($data[0] == '{') {
                // Convert the stored JSON into the original form.
                $decodeddata = $this->decode($data);
            } else {
                $decodeddata = $data;
            }
        }

        $context = (object) [
                    'cols' => $this->thecols,
                    'rows' => $this->therows,
                    'id' => $this->get_id(),
                    'name' => $this->get_full_name(),
                    'value' => $decodeddata,
                    'forceltr' => $this->get_force_ltr(),
        ];
        $element = $OUTPUT->render_from_template('core_admin/setting_configtextarea', $context);

        return format_admin_setting($this, $this->visiblename, $element, $this->description, true, '', $defaultinfo, $query);
    }

    /**
     * Write the data to storage.
     * @param string data.
     * @return string empty string if ok or populated string if error found.
     */
    public function write_setting($data) {
        $validated = $this->validate($data);
        if ($validated !== true) {
            return $validated;
        }
        return ($this->config_write($this->name, $this->json) ? '' : get_string('errorsetting', 'admin'));
    }

    /**
     * Validate data before storage.
     * @param string data.
     * @return mixed true if ok string if error found.
     */
    public function validate($data) {
        $validated = parent::validate($data); // Pass parent validation first.

        if ($validated === true) {
            // Parent validation ok, if we have an empty string then that is valid.
            if (empty($data)) {
                $this->json = '';
            } else {
                $validated = $this->encode($data);
            }
        }

        return $validated;
    }

    /**
     * Convert the string into JSON format for storage and use.
     * @param string string.
     * @return mixed true if ok string if error found.
     */
    protected function encode($string) {
        $structure = array();
        $result = '';
        $toolbox = \theme_ned_boost\toolbox::get_instance();

        // Convert string to array.
        $lines = explode(';', $string);
        foreach ($lines as $line) {
            $theline = explode(',', $line);
            // Line must have at least two elements, the block and the Font Awesome icon.
            if (count($theline) > 1) {
                $theline[0] = ltrim($theline[0]); // Remove newlines on the block name.
                $structure[$theline[0]] = array();
                $structure[$theline[0]][\theme_ned_boost\toolbox::$fontawesomekey] = trim($theline[1]);
                if (!empty($theline[2])) {
                    $theline[2] = trim($theline[2]);
                    if ($theline[2][0] == \theme_ned_boost\toolbox::$nocolourdelimiter) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$headerbackgroundcolourkey] = $theline[2][0];
                    } else if ($toolbox->validate_colour($theline[2])) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$headerbackgroundcolourkey] = $theline[2];
                    } else {
                        $result .= get_string('customiseindividualblockscolourfail', 'theme_ned_boost', array(
                            'blockname' => $theline[0],
                            'setting' => get_string('headerbackgroundcolour', 'theme_ned_boost'),
                            'value' => $theline[2]
                        ));
                    }
                }
                if (!empty($theline[3])) {
                    $theline[3] = trim($theline[3]);
                    if ($theline[3][0] == \theme_ned_boost\toolbox::$nocolourdelimiter) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$headertextcolourkey] = $theline[3][0];
                    } else if ($toolbox->validate_colour($theline[3])) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$headertextcolourkey] = $theline[3];
                    } else {
                        $result .= get_string('customiseindividualblockscolourfail', 'theme_ned_boost', array(
                            'blockname' => $theline[0],
                            'setting' => get_string('headertextcolour', 'theme_ned_boost'),
                            'value' => $theline[3]
                        ));
                    }
                }
                if (!empty($theline[4])) {
                    $theline[4] = trim($theline[4]);
                    if ($theline[4][0] == \theme_ned_boost\toolbox::$nocolourdelimiter) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$bodybackgroundcolourkey] = $theline[4][0];
                    } else if ($toolbox->validate_colour($theline[4])) {
                        $structure[$theline[0]][\theme_ned_boost\toolbox::$bodybackgroundcolourkey] = $theline[4];
                    } else {
                        $result .= get_string('customiseindividualblockscolourfail', 'theme_ned_boost', array(
                            'blockname' => $theline[0],
                            'setting' => get_string('bodybackgroundcolour', 'theme_ned_boost'),
                            'value' => $theline[4]
                        ));
                    }
                }
            }
        }

        if (empty($result)) {
            $this->json = json_encode($structure);

            if ($this->json === false) {
                $result = get_string('customiseindividualblocksjsonfail', 'theme_ned_boost');
            } else {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Convert the string into JSON format for storage and use.
     * @param string string.
     * @return string JSON or false if cannot convert.
     */
    protected function decode($json) {
        $structure = json_decode($json, true);

        if (($structure !== false) && (!empty($structure))) {
            $lines = array();

            foreach ($structure as $block => $blocksettings) {
                $lines[] = $block.', '.implode(', ', $blocksettings);
            }

            return implode(';'.PHP_EOL, $lines);
        } else {
            return false;
        }
    }
}
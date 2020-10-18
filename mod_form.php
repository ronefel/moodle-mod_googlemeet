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
 * The main mod_googlemeet configuration form.
 *
 * @package     mod_googlemeet
 * @copyright   2020 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/moodleform_mod.php');

/**
 * Module instance settings form.
 *
 * @package    mod_googlemeet
 * @copyright  2020 Rone Santos <ronefel@hotmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_googlemeet_mod_form extends moodleform_mod {
    /** @var array options to be used with date_time_selector fields in the quiz. */
    public static $datefieldoptions = array('optional' => true);

    /**
     * Defines forms elements
     */
    public function definition() {
        global $CFG, $PAGE, $COURSE;

        // Prevent JS caching
        // $CFG->cachejs = false;

        $config = get_config('googlemeet');

        // Get the current data for the form and destructuring of object
        // extract(get_object_vars($this->get_current()));

        $mform = $this->_form;

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('hidden', 'timezone');
        $mform->setType('timezone', PARAM_RAW);
        $mform->setDefault('timezone', $this->takeAccents(usertimezone()));

        $mform->addElement('hidden', 'timezoneoffset');
        $mform->setType('timezoneoffset', PARAM_RAW);
        $mform->setDefault('timezoneoffset', $this->getUserTimeZoneOffset());

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('googlemeetname', 'googlemeet'), array('size' => '64'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('text', 'url', get_string('url', 'googlemeet'), array('size' => '34'));
        $mform->setType('url', PARAM_URL);
        $mform->addRule('url', null, 'required', null, 'client');
        $mform->addHelpButton('url', 'url', 'googlemeet');

        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        // Open and close dates of event in Calendar.
        $mform->addElement('header', 'eventduration', get_string('eventduration', 'googlemeet'));
        $mform->addElement(
            'date_time_selector',
            'timeopen',
            get_string('googlemeetopen', 'googlemeet'),
            self::$datefieldoptions
        );
        $mform->addHelpButton('timeopen', 'googlemeetopenclose', 'googlemeet');
        $mform->addElement(
            'date_time_selector',
            'timeclose',
            get_string('googlemeetclose', 'googlemeet'),
            self::$datefieldoptions
        );
        $mform->disabledIf('timeclose', 'timeopen[enabled]');

        if ($config->clientid && $config->apikey) {
            $mform->addElement('header', 'generateurl', get_string('generateurlautomatically', 'googlemeet'));

            $generateurlgroup = [
                $mform->createElement('html', '<pre id="googlemeetcontent">' .
                    get_string('instructions', 'googlemeet') .
                    '<details id="googlemeetlog"><summary><b>Logs</b></summary>
                            <section><p id="googlemeetcontentlog"></p></section>
                        </details>
                    </pre>'),

                $mform->createElement('button', 'generateLink', get_string('googlemeetgeneratelink', 'googlemeet')),
            ];

            $mform->addGroup($generateurlgroup, 'generateurlgroup', '', ' ', false);
            
            $PAGE->requires->js(new moodle_url('https://apis.google.com/js/api.js'));
            $PAGE->requires->js_call_amd('mod_googlemeet/client', 'init', [
                $config->clientid,
                $config->apikey,
                $config->scopes,
            ]);
        }


        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        global $CFG;
        $errors = array();

        $pattern = "/^https:\/\/meet.google.com\/[-a-zA-Z0-9@:%._\+~#=]{3}-[-a-zA-Z0-9@:%._\+~#=]{4}-[-a-zA-Z0-9@:%._\+~#=]{3}$/";
        if (!preg_match($pattern, $data['url']) && $data['update'] === 0) {
            $errors['urlGroup'] = get_string('url_failed', 'googlemeet');
        }

        return $errors;
    }

    public function takeAccents($string) {
        return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
    }

    public function getUserTimeZoneOffset() {
        $tz = new DateTimeZone($this->takeAccents(usertimezone()));
        $dateTimeUtc = new DateTime("now", new DateTimeZone("UTC"));
        $seconds = timezone_offset_get($tz, $dateTimeUtc);

        $hours = '';
        if ($seconds < 0) {
            $hours = "-" . gmdate("Hi", -$seconds);
        } else {
            $hours = "+" . gmdate("Hi", $seconds);
        }

        return $hours;
    }
}

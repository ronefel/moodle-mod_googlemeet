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

use mod_googlemeet\client;

require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once($CFG->dirroot . '/mod/googlemeet/locallib.php');

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
        global $CFG, $OUTPUT;

        $config = get_config('googlemeet');
        $mform = $this->_form;
        $client = new client();

        $logout = optional_param('logout', 0, PARAM_BOOL);
        if ($logout) {
            $client->logout();
        }

        if (empty($this->current->instance)) {
            $clientislogged = optional_param('client_islogged', false, PARAM_BOOL);

            // Was logged in before submitting the form and the google session expired after submitting the form.
            if ($clientislogged && !$client->check_login()) {
                $mform->addElement('html', html_writer::div(get_string('sessionexpired', 'googlemeet') .
                    $client->print_login_popup(), 'mdl-align alert alert-danger googlemeet_loginbutton'
                ));

                // Whether the customer is enabled and if not logged in to the Google account.
            } else if ($client->enabled && !$client->check_login()) {
                $mform->addElement('html', html_writer::div(get_string('logintoyourgoogleaccount', 'googlemeet') .
                    $client->print_login_popup(), 'mdl-align alert alert-info googlemeet_loginbutton'
                ));
            }

            // If is logged in, shows Google account information.
            if ($client->check_login()) {
                $mform->addElement('html', $client->print_user_info('calendar'));
                $mform->addElement('hidden', 'client_islogged', true);
            }

        } else {
            $mform->addElement('hidden', 'client_islogged', false);
        }
        $mform->setType('client_islogged', PARAM_BOOL);

        // Adding the "general" fieldset, where all the common settings are shown.
        $mform->addElement('header', 'general', get_string('general', 'form'));

        // Adding the standard "name" field.
        $mform->addElement('text', 'name', get_string('roomname', 'googlemeet'), array('size' => '50'));

        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEANHTML);
        }

        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements();
        $element = $mform->getElement('introeditor');
        $attributes = $element->getAttributes();
        $attributes['rows'] = 5;
        $element->setAttributes($attributes);

        $hours = [];
        $minutes = [];
        for ($i = 0; $i <= 23; $i++) {
            $hours[$i] = sprintf("%02d", $i);
        }
        for ($i = 0; $i < 60; $i++) {
            $minutes[$i] = sprintf("%02d", $i);
        }

        $eventtime = [
            $mform->createElement('date_selector', 'eventdate', ''),
            $mform->createElement('html', '<div style="width: 100%;"></div>'),
            $mform->createElement('html', '<div class="items-center">' . get_string('from', 'googlemeet') . '</div>'),
            $mform->createElement('select', 'starthour', get_string('hour', 'form'), $hours, false, true),
            $mform->createElement('select', 'startminute', get_string('minute', 'form'), $minutes, false, true),
            $mform->createElement('html', '<div class="items-center">' . get_string('to', 'googlemeet') . '</div>'),
            $mform->createElement('select', 'endhour', get_string('hour', 'form'), $hours, false, true),
            $mform->createElement('select', 'endminute', get_string('minute', 'form'), $minutes, false, true),
            $mform->createElement('html',
                '<div id="id_googlemeet_eventtime_error" class="form-control-feedback invalid-feedback"></div>'
            ),
        ];
        $mform->addGroup($eventtime, 'eventtime', get_string('eventdate', 'googlemeet'), [''], false);

        // For multiple dates.
        $mform->addElement('header', 'headeraddmultipleeventdates', get_string('recurrenceeventdate', 'googlemeet'));
        if (!empty($config->multieventdateexpanded) || !empty($this->current->addmultiply)) {
            $mform->setExpanded('headeraddmultipleeventdates');
        }

        $mform->addElement('checkbox', 'addmultiply', '', get_string('repeatasfollows', 'googlemeet'));
        $mform->addHelpButton('addmultiply', 'recurrenceeventdate', 'googlemeet');

        $days = [
            $mform->createElement('checkbox', 'days[Mon]', '', get_string('monday', 'calendar')),
            $mform->createElement('checkbox', 'days[Tue]', '', get_string('tuesday', 'calendar')),
            $mform->createElement('checkbox', 'days[Wed]', '', get_string('wednesday', 'calendar')),
            $mform->createElement('checkbox', 'days[Thu]', '', get_string('thursday', 'calendar')),
            $mform->createElement('checkbox', 'days[Fri]', '', get_string('friday', 'calendar')),
            $mform->createElement('checkbox', 'days[Sat]', '', get_string('saturday', 'calendar')),
        ];

        if ($CFG->calendar_startwday === '0') { // Week start from sunday.
            array_unshift($days, $mform->createElement('checkbox', 'days[Sun]', '', get_string('sunday', 'calendar')));
        } else {
            array_push($days, $mform->createElement('checkbox', 'days[Sun]', '', get_string('sunday', 'calendar')));
        }

        array_push($days,
            $mform->createElement('html',
                '<div id="id_googlemeet_days_error" class="form-control-feedback invalid-feedback"></div>'
            )
        );

        $mform->addGroup($days, 'days', get_string('repeaton', 'googlemeet'), ['&nbsp;&nbsp;&nbsp;'], false);
        $mform->disabledIf('days', 'addmultiply', 'notchecked');

        $period = array(
            1 => 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20,
            21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36
        );
        $periodgroup = [
            $mform->createElement('select', 'period', '', $period, false, true),
            $mform->createElement('html', '<div class="items-center">' . get_string('week', 'googlemeet') . '</div>'),
            $mform->createElement('html',
                '<div id="id_googlemeet_periodgroup_error" class="form-control-feedback invalid-feedback"></div>'
            ),
        ];
        $mform->addGroup($periodgroup, 'periodgroup', get_string('repeatevery', 'googlemeet'), [''], false);
        $mform->disabledIf('periodgroup', 'addmultiply', 'notchecked');

        $eventenddategroup = [
            $mform->createElement('date_selector', 'eventenddate', ''),
            $mform->createElement('html',
                '<div id="id_googlemeet_eventenddategroup_error" class="form-control-feedback invalid-feedback"></div>'
            ),
        ];
        $mform->addGroup($eventenddategroup, 'eventenddategroup', get_string('repeatuntil', 'googlemeet'), [''], false);
        $mform->disabledIf('eventenddategroup', 'addmultiply', 'notchecked');

        $mform->addElement('header', 'headerroomurl', get_string('roomurl', 'googlemeet'));
        if (!empty($config->roomurlexpanded)) {
            $mform->setExpanded('headerroomurl');
        }

        if (!empty($this->current->instance) && $client->enabled) {
            $mform->addElement('static', 'url_caution', '',
                $OUTPUT->notification(get_string('roomurl_caution', 'googlemeet'), 'warning')
            );
        }

        if ($client->check_login() && empty($this->current->instance)) {
            $mform->addElement('static', 'url_desc', '', $OUTPUT->notification(get_string('roomurl_desc', 'googlemeet'), 'info'));
            $mform->addElement('text', 'url', get_string('roomurl', 'googlemeet'), ['size' => '50', 'readonly' => true]);
            $mform->setType('url', PARAM_RAW);

            $mform->addElement('text', 'creatoremail', get_string('creatoremail', 'googlemeet'),
                ['size' => '50', 'readonly' => true]
            );
            $mform->setType('creatoremail', PARAM_RAW);
        } else {
            $mform->addElement('text', 'url', get_string('roomurl', 'googlemeet'), ['size' => '50']);
            $mform->setType('url', PARAM_URL);
            $mform->addHelpButton('url', 'url', 'googlemeet');

            $mform->addElement('text', 'creatoremail', get_string('creatoremail', 'googlemeet'), ['size' => '50']);
            $mform->setType('creatoremail', PARAM_RAW);
            $mform->addHelpButton('creatoremail', 'creatoremail', 'googlemeet');
        }

        $mform->addElement('header', 'headernotification', get_string('notification', 'googlemeet'));
        if (!empty($config->notificationexpanded)) {
            $mform->setExpanded('headernotification');
        }

        $mform->addElement('checkbox', 'notify', '', get_string('notify', 'googlemeet'));
        $mform->setDefault('notify', $config->notify);
        $mform->addHelpButton('notify', 'notify', 'googlemeet');

        $minutes = [];
        for ($i = 0; $i <= 120; $i = $i + 5) {
            $minutes[$i] = $i;
        }
        $minutesbefore = $mform->addElement('select',
            'minutesbefore', get_string('minutesbefore', 'googlemeet'), $minutes, false, true
        );
        $minutesbefore->setSelected($config->minutesbefore);
        $mform->addHelpButton('minutesbefore', 'minutesbefore', 'googlemeet');

        // Add standard elements.
        $this->standard_coursemodule_elements();

        // Add standard buttons.
        $this->add_action_buttons();

    }

    /**
     * Decode json format from the database
     *
     * @param array $defaultvalues Form defaults
     * @return void
     */
    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $defaultvalues['days'] = json_decode($defaultvalues['days'], true);
        }
    }

    /**
     * Enforce validation rules here
     *
     * @param array $data array of ("fieldname"=>value) of submitted data
     * @param array $files array of uploaded files "element_name"=>tmp_file_path
     * @return array
     **/
    public function validation($data, $files) {
        global $COURSE;

        $errors = parent::validation($data, $files);

        $starttime = $data['starthour'] * HOURSECS + $data['startminute'] * MINSECS;
        $endtime = $data['endhour'] * HOURSECS + $data['endminute'] * MINSECS;

        if ($endtime < $starttime) {
            $errors['eventtime'] = get_string('invalideventendtime', 'googlemeet');
        }

        if (!empty($data['addmultiply']) &&
            $data['eventdate'] !== 0 &&
            $data['eventenddate'] !== 0 &&
            $data['eventenddate'] < $data['eventdate']
        ) {
            $errors['eventenddategroup'] = get_string('invalideventenddate', 'googlemeet');
        }

        $addmulti = isset($data['addmultiply']) ? (int)$data['addmultiply'] : 0;
        $days = isset($data['days']);

        if ($addmulti && !$days) {
            $errors['days'] = get_string('checkweekdays', 'googlemeet');
        } else if ($addmulti && !$this->checkweekdays($data['eventdate'], $data['eventenddate'], $data['days'])) {
            $errors['days'] = get_string('checkweekdays', 'googlemeet');
        }

        if ($addmulti && ceil(($data['eventenddate'] - $data['eventdate']) / YEARSECS) > 1) {
            $errors['eventenddate'] = get_string('timeahead', 'googlemeet');
        }

        $startdate = $data['eventdate'] + $starttime;
        if ($startdate < $COURSE->startdate) {
            $errors['eventtime'] = get_string('earlierto', 'googlemeet',
                userdate($COURSE->startdate, get_string('strftimedmyhm', 'googlemeet'))
            );
        }

        $client = new client();
        $clientislogged = optional_param('client_islogged', false, PARAM_BOOL);

        if (empty($this->current->instance)) {
            // Validates the url field only if not logged into Google account.
            if (!$client->check_login() && !$clientislogged) {
                $errors = $this->validate_url($data['url'], $errors);
                if (!validate_email($data['creatoremail'])) {
                    $errors['creatoremail'] = get_string('creatoremail_error', 'googlemeet');
                }
            }

            // Forces an error if the Google session expired after submitting the form.
            if (!$client->check_login() && $clientislogged) {
                $errors['client_islogged'] = '';
            }
        } else {
            // Validates url field if updating instance.
            $errors = $this->validate_url($data['url'], $errors);
            if (!validate_email($data['creatoremail'])) {
                $errors['creatoremail'] = get_string('creatoremail_error', 'googlemeet');
            }
        }

        return $errors;
    }

    /**
     * Check weekdays function.
     * @param int $eventdate
     * @param int $eventenddate
     * @param array $days
     * @return bool
     */
    private function checkweekdays($eventdate, $eventenddate, $days) {
        $found = false;

        if (!$days) {
            return false;
        }

        $daysofweek = [
            0 => "Sun",
            1 => "Mon",
            2 => "Tue",
            3 => "Wed",
            4 => "Thu",
            5 => "Fri",
            6 => "Sat"
        ];

        $start = new DateTime(date("Y-m-d", $eventdate));
        $interval = new DateInterval('P1D');
        $end = new DateTime(date("Y-m-d", $eventenddate));
        $end->add(new DateInterval('P1D'));

        $period = new DatePeriod($start, $interval, $end);
        foreach ($period as $date) {
            if (!$found) {
                foreach ($days as $day => $value) {
                    $key = array_search($day, $daysofweek);
                    if ($date->format("w") == $key) {
                        $found = true;
                        break;
                    }
                }
            }
        }

        return $found;
    }

    /**
     * Validate the provided url
     * @param string $url Url to validate.
     * @param array $errors Form errors.
     *
     * @return array Form errors.
     */
    private function validate_url(string $url, array $errors) {
        if (googlemeet_clear_url($url) == null) {
            $errors['generateurlgroup'] = get_string('url_failed', 'googlemeet');
            $errors['url'] = get_string('url_failed', 'googlemeet');
        }
        return $errors;
    }
}

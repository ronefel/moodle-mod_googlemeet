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

namespace mod_googlemeet;

use calendar_event;
use moodle_exception;
use stdClass;

/**
 * Utility class for all instance (module) routines helper.
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /** @var string The googlemeet meeting_start event */
    public const GOOGLEMEET_EVENT_START = 'googlemeet_event';

    /**
     * Wrapper function to perform an API call and also catch and handle potential exceptions.
     *
     * @param rest $service The rest API object
     * @param string $api The name of the API call
     * @param array $params The parameters required by the API call
     * @param string $rawpost Optional param to include in the body of a post.
     *
     * @return \stdClass The response object
     * @throws moodle_exception
     */
    public static function request($service, $api, $params, $rawpost = false): ?\stdClass {
        try {
            $response = $service->call($api, $params, $rawpost);
        } catch (\Exception $e) {
            if ($e->getCode() == 403 && strpos($e->getMessage(), 'Access Not Configured') !== false) {
                // This is raised when the Drive API service or the Calendar API service
                // has not been enabled on Google APIs control panel.
                throw new moodle_exception('servicenotenabled', 'mod_googlemeet');
            }
            throw $e;
        }

        return $response;
    }

    /**
     * Generates an event in calendar after a googlemeet insert/update.
     *
     * @param stdClass $googlemeet moodleform
     * @param stdClass $event The event
     *
     * @return void
     **/
    public static function create_calendar_event(stdClass $googlemeet, stdClass $event) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/calendar/lib.php');

        // Add event to the calendar as openingtime is set.
        $calendarevent = (object) [
            'eventtype' => self::GOOGLEMEET_EVENT_START,
            'type' => CALENDAR_EVENT_TYPE_ACTION,
            'name' => get_string('calendareventname', 'googlemeet', $googlemeet->name),
            'description' => format_module_intro('googlemeet', $googlemeet, $googlemeet->coursemodule, false),
            'format' => FORMAT_HTML,
            'courseid' => $googlemeet->course,
            'groupid' => 0,
            'userid' => 0,
            'modulename' => 'googlemeet',
            'instance' => $googlemeet->id,
            'timestart' => $event->eventdate,
            'timeduration' => $event->duration,
            'timesort' => $event->eventdate,
            'visible' => instance_is_visible('googlemeet', $googlemeet),
            'priority' => null,
        ];

        calendar_event::create($calendarevent);
    }
}

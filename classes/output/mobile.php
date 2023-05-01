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
 * Mobile output class for googlemeet
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace mod_googlemeet\output;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/googlemeet/locallib.php');

/**
 * Mobile output class for googlemeet
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mobile {

    /**
     * Returns the googlemeet course view for the mobile app.
     *
     * @param mixed $args
     * @return array HTML, javascript and other data.
     */
    public static function mobile_course_view($args): array {
        global $OUTPUT, $DB;

        $args = (object) $args;
        $versionname = $args->appversioncode >= 3950 ? 'latest' : 'ionic3';

        $cm = get_coursemodule_from_id('googlemeet', $args->cmid);

        // Capabilities check.
        require_login($args->courseid, false, $cm, true, true);

        $context = \context_module::instance($cm->id);

        require_capability('mod/googlemeet:view', $context);

        $googlemeet = $DB->get_record('googlemeet', array('id' => $cm->instance), '*', MUST_EXIST);
        $course = $DB->get_record('course', array('id' => $args->courseid), '*', MUST_EXIST);

        $recordings = googlemeet_list_recordings(['googlemeetid' => $googlemeet->id, 'visible' => true]);
        $hasrecordings = !empty($recordings);

        $data = [
            'intro' => $googlemeet->intro,
            'url' => $googlemeet->url,
            'cmid' => $cm->id,
            'upcomingevent' => googlemeet_get_upcoming_events($googlemeet->id),
            'recording' => ['hasrecordings' => $hasrecordings, 'recordings' => $recordings]
        ];

        // Completion and trigger events.
        googlemeet_view($googlemeet, $course, $cm, $context);

        return [
            'templates' => [
                [
                    'id' => 'main',
                    'html' => $OUTPUT->render_from_template("mod_googlemeet/mobile_view_page_$versionname", $data),
                ],
            ],
            'javascript' => 'this.showUpcomingEvents = '. !$hasrecordings,
            'otherdata' => '',
            'files' => '',
        ];
    }
}

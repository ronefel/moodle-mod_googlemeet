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
 * Private googlemeet module utility functions
 *
 * @package     mod_googlemeet
 * @copyright   2020 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->dirroot/mod/googlemeet/lib.php");

/**
 * Print googlemeet header.
 * @param object $googlemeet
 * @param object $cm
 * @param object $course
 * @return void
 */
function googlemeet_print_header($googlemeet, $cm, $course) {
    global $PAGE, $OUTPUT;

    $PAGE->set_title($course->shortname . ': ' . $googlemeet->name);
    $PAGE->set_heading($course->fullname);
    $PAGE->set_activity_record($googlemeet);
    echo $OUTPUT->header();
}

/**
 * Print googlemeet heading.
 * @param object $googlemeet
 * @param object $cm
 * @param object $course
 * @param bool $notused This variable is no longer used.
 * @return void
 */
function googlemeet_print_heading($googlemeet, $cm, $course, $notused = false) {
    global $OUTPUT;
    echo $OUTPUT->heading(format_string($googlemeet->name), 2);
}

/**
 * Print googlemeet introduction.
 * @param object $googlemeet
 * @param object $cm
 * @param object $course
 * @param bool $ignoresettings print even if not specified in modedit
 * @return void
 */
function googlemeet_print_intro($googlemeet, $cm, $course, $ignoresettings = false) {
    global $OUTPUT;

    $options = empty($googlemeet->displayoptions) ? array() : unserialize($googlemeet->displayoptions);
    if ($ignoresettings or !empty($options['printintro'])) {
        if (trim(strip_tags($googlemeet->intro))) {
            echo $OUTPUT->box_start('mod_introbox', 'googlemeetintro');
            echo format_module_intro('googlemeet', $googlemeet, $cm->id);
            echo $OUTPUT->box_end();
        }
    }
}

/**
 * Print googlemeet info and link.
 * @param object $googlemeet
 * @param object $cm
 * @param object $course
 * @return does not return
 */
function googlemeet_print_workaround($googlemeet, $cm, $course) {
    global $OUTPUT;

    googlemeet_print_header($googlemeet, $cm, $course);
    googlemeet_print_heading($googlemeet, $cm, $course, true);
    googlemeet_print_intro($googlemeet, $cm, $course, true);

    echo '<div class="googlemeetworkaround">';
    print_string('clicktoopen', 'googlemeet', "<a href=\"$googlemeet->url\" onclick=\"this.target='_blank';\">$googlemeet->url</a>");
    echo '</div>';

    echo $OUTPUT->footer();
    die;
}

/**
 * This excludes all Google Meet events.
 * @param int $googlemeetid
 * @return void
 */

function googlemeet_delete_events($googlemeetid) {
    global $DB;
    if ($events = $DB->get_records('event', array('modulename' => 'googlemeet', 'instance' => $googlemeetid))) {
        foreach ($events as $event) {
            $event = calendar_event::load($event);
            $event->delete();
        }
    }
}

/**
 * This creates new events given as timeopen and timeclose by $googlemeet.
 * @param object $googlemeet
 * @return void
 */
function googlemeet_set_events($googlemeet) {
    // Adding the googlemeet to the eventtable.

    googlemeet_delete_events($googlemeet->id);

    $event = new stdClass;
    $event->description = $googlemeet->intro;
    $event->courseid = $googlemeet->course;
    $event->groupid = 0;
    $event->userid = 0;
    $event->modulename = 'googlemeet';
    $event->instance = $googlemeet->id;
    $event->eventtype = 'open';
    $event->timestart = $googlemeet->timeopen;
    $event->visible = instance_is_visible('googlemeet', $googlemeet);
    $event->timeduration = ($googlemeet->timeclose - $googlemeet->timeopen);

    if ($googlemeet->timeopen && $googlemeet->timeclose) {
        // Single event for the whole googlemeet.
        $event->name = $googlemeet->name;
        calendar_event::create($event);
    }
}

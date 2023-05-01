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
 * Mobile app definition for googlemeet.
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;
global $CFG;
$addons = [
    "mod_googlemeet" => [ // Plugin identifier.
        "handlers" => [ // Different places where the plugin will display content.
            'coursegooglemeet' => [ // Handler unique name (alphanumeric).
                'displaydata' => [
                    'title' => 'pluginname',
                    'icon' => $CFG->wwwroot . '/mod/googlemeet/pix/icon.png',
                    'class' => '',
                ],
                'delegate' => 'CoreCourseModuleDelegate', // Delegate (where to display the link to the plugin).
                'method' => 'mobile_course_view' // Main function in \mod_googlemeet\output\mobile.
            ]
        ],
        'lang' => [ // Language strings that are used in all the handlers.
            ['pluginname', 'googlemeet'],
            ['entertheroom', 'googlemeet'],
            ['upcomingevents', 'googlemeet'],
            ['today', 'googlemeet'],
            ['from', 'googlemeet'],
            ['to', 'googlemeet'],
            ['at', 'googlemeet'],
            ['recordings', 'googlemeet']
        ]
    ]
];

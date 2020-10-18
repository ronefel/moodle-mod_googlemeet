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
 * Plugin strings are defined here.
 *
 * @package     mod_googlemeet
 * @category    string
 * @copyright   2020 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Google Meet';
$string['modulename'] = 'Google Meet';
$string['modulenameplural'] = 'Google Meet instances';
$string['modulename_help'] = 'The Google Meet module allows a teacher to create a meeting room as a course resource.';

$string['googlemeetname'] = 'Room name';

$string['generateurlautomatically'] = 'Generate Room URL automatically';

$string['url'] = 'Room url';
$string['url_help'] = 'You can manually enter the URL of the Google Meet room in the following format: https://meet.google.com/aaa-aaaa-aaa <br/> or you can generate the URL automatically in the section below "<strong>'.$string['generateurlautomatically'].'</strong>".';
$string['url_failed'] = 'Enter a valid Google Meet url with the following format: https://meet.google.com/aaa-aaaa-aaa';

$string['introeditor'] = 'Description';
$string['introeditor_help'] = 'This description will also be saved in the calendar event if the URL is automatically generated in the section below "<strong>'.$string['generateurlautomatically'].'</strong>".';

$string['instructions'] = '<details>
<summary><b>Instructions</b></summary>
<section>
<ol>
<li>It is mandatory to enter the name of the room in the "Room name" field above.</li>
<li>The "Description" field will also be used as a description in the Agenda event (optional).</li>
<li>The date fields below are optional, they serve to inform the start and end date of the event in the Agenda.</li>
<li>Clicking on the "Generate room url" button will open a window where you must select your institutional account or log in if you are not logged in. If you select an account that is not institutional it will show an error and it will not be possible to continue.</li>
<li>On the next screen, you should give permission to "View and edit events on all your Calendars".</li>
<li>With the account selected and the permission granted, an event in the Calendar will automatically be created with a link from the Google Meet conference room. The "Room url" field above will be automatically populated with this link.</li>
<li>Click on the "Save and return to course" button.</li>
</ol>
</section>
</details>';
$string['googlemeetgeneratelink'] = 'Generate room url';
$string['eventduration'] = 'Event duration';
$string['googlemeetopen'] = 'Event start date';
$string['googlemeetclose'] = 'End date of the event';
$string['googlemeetopenclose'] = 'Start date and end date of the event';
$string['googlemeetopenclose_help'] = 'If disabled, it will generate the event in the calendar for today.';

$string['warning'] = 'Warning';
$string['warningtext'] = 'The GoogleMeet Resource cannot be edited. Delete this and create a new one.';

$string['pluginadministration'] = 'GoogleMeet module administration';

$string['clicktoopen'] = 'Click {$a} link to open resource.';

$string['clientid'] = 'Client ID';
$string['configclientid'] = 'Client ID from the Developer Console';
$string['apikey'] = 'API key';
$string['configapikey'] = 'API key from the Developer Console';
$string['scopes'] = 'Scopes';
$string['configscopes'] = 'Authorization scopes required by the API; multiple scopes can be included, separated by spaces.';

$string['requiredfield'] = 'Enter a value.';
$string['creatingcalendarevent'] = 'Creating event in the calendar...';
$string['eventsuccessfullycreated'] = 'Account successfully created in account';
$string['creatingconferenceroom'] = 'Creating conference room...';
$string['conferencesuccessfullycreated'] = 'Conference room created successfully';
$string['invalidstoredurl'] = 'Cannot display this resource, Google Meet URL is invalid.';

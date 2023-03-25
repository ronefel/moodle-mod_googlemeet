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

defined('MOODLE_INTERNAL') || die();

use html_writer;
use popup_action;
use single_button;
use moodle_url;
use dml_missing_record_exception;
use stdClass;

/**
 * Oauth Client for mod_googlemeet.
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class client {

    /**
     * OAuth 2 client
     * @var \core\oauth2\client
     */
    private $client = null;

    /**
     * OAuth 2 Issuer
     * @var \core\oauth2\issuer
     */
    private $issuer = null;

    /**
     * Additional scopes required for drive.
     */
    const SCOPES = 'https://www.googleapis.com/auth/drive https://www.googleapis.com/auth/calendar.events';

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {

        try {
            $this->issuer = \core\oauth2\api::get_issuer(get_config('googlemeet', 'issuerid'));
        } catch (dml_missing_record_exception $e) {
            die('disabled');
        }

        if ($this->issuer && !$this->issuer->get('enabled')) {
            die('disabled');
        }
    }

    /**
     * Get a cached user authenticated oauth client.
     *
     * @return \core\oauth2\client
     */
    protected function get_user_oauth_client() {
        if ($this->client) {
            return $this->client;
        }        

        $returnurl = new moodle_url('/mod/googlemeet/callback.php');
        $returnurl->param('callback', 'yes');
        $returnurl->param('sesskey', sesskey());

        $this->client = \core\oauth2\api::get_user_oauth_client($this->issuer, $returnurl, self::SCOPES, true);

        return $this->client;
    }

    /**
     * Print the login in a popup.
     *
     * @param array|null $attr Custom attributes to be applied to popup div.
     * 
     * @return string HTML code
     */
    public function print_login_popup($attr = null) {
        global $OUTPUT;

        $client = $this->get_user_oauth_client();
        $url = new moodle_url($client->get_login_url());
        $state = $url->get_param('state') . '&reloadparent=true';
        $url->param('state', $state);

        $button = new single_button($url, 'Log in to your Google account', 'post', true);  // stringar
        $button->add_action(new popup_action('click', $url, 'Login'));
        $button->class = 'mdl-align';
        $button = $OUTPUT->render($button);

        return html_writer::div($button, '', $attr);

    }

    /**
     * Print user info.
     *
     * @param string|null $scope 'calendar' or 'drive' Defines which link will be used.
     * 
     * @return string HTML code
     */
    public function print_user_info($scope = null) {
        global $OUTPUT, $PAGE;

        $userauth = $this->get_user_oauth_client();
        $userinfo = $userauth->get_userinfo();

        $username = $userinfo['username'];
        $name = $userinfo['firstname'].' '.$userinfo['lastname'];
        $userpicture = base64_encode($userinfo['picture']);

        $userurl = '#';
        if($scope == 'calendar') {
            $userurl = new moodle_url('https://calendar.google.com/');
        }
        if($scope == 'drive') {
            $userurl = new moodle_url('https://drive.google.com/');
        }

        $logouturl = new moodle_url($PAGE->url);
        $logouturl->param('logout', true);
        
        $img = html_writer::img('data:image/jpeg;base64,'.$userpicture, '');
        $out = html_writer::start_div('',['id'=>'googlemeet_auth-info']);
        $out .= html_writer::link($userurl, $img, ['id'=>'googlemeet_picture-user', 'target'=>'_blank', 'title'=>'Manage']); // stringar
        $out .= html_writer::start_div('',['id'=>'googlemeet_user-name']);
        $out .= html_writer::span('Conta do Google logada', ''); // stringar
        $out .= html_writer::span($name);
        $out .= html_writer::span($username);
        $out .= html_writer::end_div();
        $out .= html_writer::link($logouturl,
            $OUTPUT->pix_icon('logout', '', 'googlemeet', ['class'=>'m-0']),
            ['class'=>'btn btn-secondary btn-sm', 'title'=>'Logout']  // stringar
        );

        $out .= html_writer::end_div();

        return $out;
    }    

    /**
     * Checks whether the user is authenticate or not.
     *
     * @return bool true when logged in.
     */
    public function check_login() {
        $client = $this->get_user_oauth_client();
        return $client->is_logged_in();
    }

    /**
     * Logout.
     *
     * @return void
     */
    public function logout() {
        global $PAGE;

        if($this->check_login()) {
            $url = new moodle_url($PAGE->url);
            $client = $this->get_user_oauth_client();
            $client->log_out();
            $js = <<<EOD
                <html>
                <head>
                    <script type="text/javascript">
                        window.location = '{$url}'.replaceAll('&amp;','&')
                    </script>
                </head>
                <body></body>
                </html>
            EOD;
            die($js);
        }
    }

    /**
     * Store the access token.
     * 
     * @return void
     */
    public function callback() {
        $client = $this->get_user_oauth_client();
        // This will upgrade to an access token if we have an authorization code and save the access token in the session.
        $client->is_logged_in();
    }

    /**
     * Create a meeting event in Google Calendar
     *
     * @param object $googlemeet An object from the form.
     * 
     * @return object Google Calendar event
     */
    public function create_meeting_event($googlemeet) {
        global $USER;

        $calendarid = 'primary';
        $starthour = str_pad($googlemeet->starthour , 2 , '0' , STR_PAD_LEFT);
        $startminute = str_pad($googlemeet->startminute , 2 , '0' , STR_PAD_LEFT);
        $endhour = str_pad($googlemeet->endhour , 2 , '0' , STR_PAD_LEFT);
        $endminute = str_pad($googlemeet->endminute , 2 , '0' , STR_PAD_LEFT);

        $starttime = $starthour . ':' . $startminute . ':00';
        $endtime = $endhour . ':' . $endminute . ':00';

        $startdatetime = date('Y-m-d', $googlemeet->eventdate) . 'T' . $starttime;
        $enddatetime = date('Y-m-d', $googlemeet->eventdate) . 'T' . $endtime;

        $timezone = get_user_timezone($USER->timezone);

        $daysofweek = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        $recurrence = '';

        if (isset($googlemeet->addmultiply)) {
            $interval = 'INTERVAL=' . $googlemeet->period;
            $until = 'UNTIL=' . date('Ymd', $googlemeet->eventenddate) . 'T235959Z';
            $byday = 'BYDAY=';

            $daysofweek = new stdClass;
            $daysofweek->Sun = 'SU';
            $daysofweek->Mon = 'MO';
            $daysofweek->Tue = 'TU';
            $daysofweek->Wed = 'WE';
            $daysofweek->Thu = 'TH';
            $daysofweek->Fri = 'FR';
            $daysofweek->Sat = 'SA';

            foreach ((array) $googlemeet->days as $day => $val) {
                $byday .= $daysofweek->$day . ',';
            }

            $recurrence = ['RRULE:FREQ=WEEKLY;' . $interval . ';' . $until . ';' . $byday];
        }

        $eventrawpost = [
            'summary' => $googlemeet->name,
            'start' => [
                'dateTime' => $startdatetime,
                'timeZone' => $timezone
            ],
            'end' => [
                'dateTime' => $enddatetime,
                'timeZone' => $timezone
            ],
            'recurrence' => $recurrence
        ];

        $service = new rest($this->get_user_oauth_client());

        $eventparams = [
            'calendarid' => $calendarid
        ];

        $eventresponse = helper::request($service, 'insertevent', $eventparams, json_encode($eventrawpost));
        
        $conferenceparams = [
            'calendarid' => $calendarid,
            'eventid' => $eventresponse->id,
            'conferenceDataVersion' => 1
        ];

        $conferencerawpost = [
            'conferenceData' => [
                'createRequest' => [
                    'requestId' => $eventresponse->id
                ]
            ]
        ];

        $conferenceresponse = helper::request($service, 'createconference', $conferenceparams, json_encode($conferencerawpost));

        return $conferenceresponse;

    }

    /**
     * Get recordings from Google Drive and sync with database.
     *
     * @return void
     */
    public function syncrecordings($googlemeet) {
        global $PAGE;

        if($this->check_login()) {
            $service = new rest($this->get_user_oauth_client());

            $folderparams = [
                'q' => 'name="Meet Recordings" and trashed = false and mimeType = "application/vnd.google-apps.folder" and "me" in owners',
                'pageSize' => 1000,
                'fields' => 'nextPageToken, files(id,owners)'
            ];
    
            $folderresponse = helper::request($service, 'list', $folderparams);

            $files = $folderresponse->files;
            $parents = '';
            for($i=0; $i < count($files); $i++) {
                $parents .= 'parents="'.$files[$i]->id.'"';
                if ($i + 1 < count($files)) {
                    $parents .= ' or ';
                  }
            }

            $meetingCode = substr($googlemeet->url, 24, 12);
            $name = $googlemeet->name;
            $recordingparams = [
                'q' => '(parents="'.$parents.'") and trashed = false and mimeType = "video/mp4" and "me" in owners and (name contains "'.$meetingCode.'" or name contains "'.$name.'")',
                'pageSize' => 1000,
                'fields' => 'files(id,name,permissionIds,createdTime,videoMediaMetadata,webViewLink)'
            ];
    
            $recordingresponse = helper::request($service, 'list', $recordingparams);


            $url = new moodle_url($PAGE->url);
            $js = <<<EOD
                <html>
                <head>
                    <script type="text/javascript">
                        window.location = '{$url}'.replaceAll('&amp;','&')
                    </script>
                </head>
                <body></body>
                </html>
            EOD;
            die($js);
        }
    }

}
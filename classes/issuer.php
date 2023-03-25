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

use DateTime;
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
class issuer {

    // /**
    //  * OAuth 2 client
    //  * @var \core\oauth2\client
    //  */
    // private $client = null;

    /**
     * OAuth 2 Issuer
     * @var \core\oauth2\issuer
     */
    private $issuer = null;

    /**
     * Force disable googlemeet instance
     *  @var bool
     */
    private $disabled = false;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct() {

        try {
            $this->issuer = \core\oauth2\api::get_issuer(get_config('googlemeet', 'issuerid'));
        } catch (dml_missing_record_exception $e) {
            $this->disabled = true;
        }

        if ($this->issuer && !$this->issuer->get('enabled')) {
            $this->disabled = true;
        }
    }

    /**
     * Checks whether the system is authenticated or not.
     *
     * @return bool true when logged in.
     */
    public function check_login() {
        if(\core\oauth2\api::get_system_oauth_client($this->issuer) === false){
            return false;
        }
        return true;
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

        $service = new rest(\core\oauth2\api::get_system_oauth_client($this->issuer));

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
            $service = new rest(\core\oauth2\api::get_system_oauth_client($this->issuer));

            $folderparams = [
                'q' => 'name="Meet Recordings" and trashed = false and mimeType = "application/vnd.google-apps.folder"',
                'pageSize' => 1000,
                'fields' => 'files(id,owners)'
            ];
    
            $folderresponse = helper::request($service, 'list', $folderparams, false);

            $folders = $folderresponse->files;
            $parents = '';
            for($i=0; $i < count($folders); $i++) {
                $parents .= 'parents="'.$folders[$i]->id.'"';
                if ($i + 1 < count($folders)) {
                    $parents .= ' or ';
                  }
            }

            $meetingCode = substr($googlemeet->url, 24, 12);
            $name = $googlemeet->name;
            $recordingparams = [
                'q' => '('.$parents.') and trashed = false and mimeType = "video/mp4" and (name contains "'.$meetingCode.'" or name contains "'.$name.'")',
                'pageSize' => 1000,
                'fields' => 'files(id,name,permissionIds,createdTime,videoMediaMetadata,webViewLink,owners)'
            ];
    
            $recordingresponse = helper::request($service, 'list', $recordingparams, false);

            $recordings = $recordingresponse->files;

            if ($recordings && count($recordings) > 0) {
                for ($i = 0; $i < count($recordings); $i++) {
                    $recording = $recordings[$i];

                    // Add "Anyone with the link" permission on video file
                    if (!array_search('anyoneWithLink', $recording->permissionIds)) {
                        $permissionparams = [
                            'fileid' => $recording->id,
                            'fields' => 'id'
                        ];
                        $permissionrawpost = [
                            "role" => "reader",
                            "type" => "anyone"
                        ];
                        helper::request($service, 'create_permission', $permissionparams, json_encode($permissionrawpost));
                    }

                    //Format it into a human-readable time.
                    $duration = date("H:i:s", floor((int)$recording->videoMediaMetadata->durationMillis / 1000));

                    $createdTime = new DateTime($recording->createdTime);

                    $recordings[$i]->recordingId = $recording->id;
                    $recordings[$i]->duration = $duration;
                    $recordings[$i]->createdTime = $createdTime->getTimestamp();
                    $recordings[$i]->creatoremail = $recording->owners[0]->emailAddress;

                    unset($recordings[$i]->id);
                    unset($recordings[$i]->permissionIds);
                    unset($recordings[$i]->videoMediaMetadata);
                    unset($recordings[$i]->owners);
                }

                sync_recordings($googlemeet->id, $recordings);
            }

                // sync_recordings($googlemeet->id, );

            //     Ajax.call([{
            //         methodname: 'mod_googlemeet_sync_recordings',
            //         args: {
            //         googlemeetid: googlemeet.id,
            //         creatoremail: ownerEmail,
            //         files: files,
            //         coursemoduleid: courseModuleId
            //         }
            //     }])[0].then(function(response) {
            //         renderTemplate(response);
            //         hasRecording = true;
            //         return;
            //     }).fail(Notification.exception).fail(function() {
            //         showLoading(false);
            //     });

            //     } else {
            //     $notfoundmsg = notfoundrecordingname + ' "' + meetingCode + '" ';
            //     if (googlemeet.originalname) {
            //         notfoundmsg += stror + ' "' + googlemeet.originalname + '"';
            //     }
            //     appendPre(notfoundmsg);
            //     showLoading(false);

            //     if (hasRecording) {
            //         showLoading(true);
            //         Ajax.call([{
            //         methodname: 'mod_googlemeet_delete_all_recordings',
            //         args: {
            //             googlemeetid: googlemeet.id,
            //             coursemoduleid: courseModuleId
            //         }
            //         }])[0].then(function(response) {
            //         renderTemplate(response);
            //         hasRecording = false;
            //         showLoading(false);
            //         return;
            //         }).fail(Notification.exception).fail(function() {
            //         showLoading(false);
            //         });
            //     }


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
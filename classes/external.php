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
 * Google Meet external API
 *
 * @package     mod_googlemeet
 * @category    external
 * @copyright   2020 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->dirroot/mod/googlemeet/lib.php");

class mod_googlemeet_external extends external_api {
 
    public static function sync_recordings_parameters() {
        return new external_function_parameters(
            [
                'googlemeetId' => new external_value(PARAM_INT, ''),
                'creatorEmail' => new external_value(PARAM_EMAIL, ''),
                'files' => new external_multiple_structure(
                    new external_single_structure(
                        [
                            'recordingId' => new external_value(PARAM_TEXT, 'Google Drive file ID'),
                            'name' => new external_value(PARAM_TEXT, 'Recording name'),
                            'createdTime' => new external_value(PARAM_INT, 'Creation date timestamp'),
                            'duration' => new external_value(PARAM_TEXT, 'Recording time'),
                            'webViewLink' => new external_value(PARAM_URL, 'Link to preview'),
                        ]
                    )
                ),
                'coursemoduleId' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * Returns synchronized recordings
     * @return stdClass recordings
     */
    public static function sync_recordings($googlemeetId, $creatorEmail, $files, $coursemoduleId) {
        global $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(
            self::sync_recordings_parameters(),
            [
                'googlemeetId' => $googlemeetId,
                'creatorEmail' => $creatorEmail,
                'files' => $files,
                'coursemoduleId' => $coursemoduleId
            ]
        );

        $context = context_module::instance($coursemoduleId);
        require_capability('mod/googlemeet:syncgoogledrive', $context);

        $googlemeetrecordings = $DB->get_records('googlemeet_recordings', ['googlemeetid' => $googlemeetId]);

        $recordingids = array_column($googlemeetrecordings, 'recordingid');
        $fileids = array_column($files, 'recordingId');

        $updaterecordings = [];
        $insertrecordings = [];
        $deleterecordings = [];

        foreach ($files as $file) {
            if (in_array($file['recordingId'], $recordingids, true)) {
                array_push($updaterecordings, $file);
            } else {
                array_push($insertrecordings, $file);
            }
        }

        foreach ($googlemeetrecordings as $googlemeetrecording){
            if(!in_array($googlemeetrecording->recordingid, $fileids)){
                $deleterecordings['id'] = $googlemeetrecording->id;
            }
        }

        if($deleterecordings){
            $DB->delete_records('googlemeet_recordings', $deleterecordings);
        }

        if ($updaterecordings) {
            foreach ($updaterecordings as $updaterecording) {
                $recording = $DB->get_record('googlemeet_recordings', [
                    'googlemeetid' => $googlemeetId,
                    'recordingid' => $updaterecording['recordingId']
                ]);

                $recording->createdtime     = $updaterecording['createdTime'];
                $recording->duration        = $updaterecording['duration'];
                $recording->webviewlink     = $updaterecording['webViewLink'];
                $recording->timemodified    = time();

                $DB->update_record('googlemeet_recordings', $recording);
            }

            $googlemeetRecord = $DB->get_record('googlemeet', ['id' => $googlemeetId]);
            $googlemeetRecord->lastsync = time();
            $DB->update_record('googlemeet', $googlemeetRecord);
        }

        if ($insertrecordings) {
            $recordings = [];

            foreach ($insertrecordings as $insertrecording) {
                $recording = new stdClass();
                $recording->googlemeetid      = $googlemeetId;
                $recording->recordingid     = $insertrecording['recordingId'];
                $recording->name            = $insertrecording['name'];
                $recording->createdtime     = $insertrecording['createdTime'];
                $recording->duration        = $insertrecording['duration'];
                $recording->webviewlink     = $insertrecording['webViewLink'];
                $recording->timemodified    = time();

                array_push($recordings, $recording);
            }

            $DB->insert_records('googlemeet_recordings', $recordings);

            $googlemeetRecord = $DB->get_record('googlemeet', ['id' => $googlemeetId]);
            $googlemeetRecord->lastsync = time();

            if(!$googlemeetRecord->creatoremail){
                $googlemeetRecord->creatoremail = $creatorEmail;
            }

            $DB->update_record('googlemeet', $googlemeetRecord);
        }

        return googlemeet_list_recordings(['googlemeetid' => $googlemeetId]);
    }

    public static function sync_recordings_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                [
                    'id' => new external_value(PARAM_INT, 'Recording instance ID'),
                    'name' => new external_value(PARAM_TEXT, 'Recording name'),
                    'createdtime' => new external_value(PARAM_INT, 'Creation date timestamp'),
                    'createdtimeformatted' => new external_value(PARAM_TEXT, 'Formatted creation date'),
                    'duration' => new external_value(PARAM_TEXT, 'Recording time'),
                    'webviewlink' => new external_value(PARAM_URL, 'Link to preview'),
                    'visible' => new external_value(PARAM_BOOL, 'If recording visible')
                ]
            )
        );
    }

    public static function recording_edit_name_parameters() {
        return new external_function_parameters(
            [
                'recordingId' => new external_value(PARAM_INT, ''),
                'name' => new external_value(PARAM_TEXT, ''),
                'coursemoduleId' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * Returns the value of the visible attribute of the recording
     * @return string visible
     */
    public static function recording_edit_name($recordingId, $name, $coursemoduleId) {
        global $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(
            self::recording_edit_name_parameters(),
            [
                'recordingId' => $recordingId,
                'name' => $name,
                'coursemoduleId' => $coursemoduleId
            ]
        );

        $context = context_module::instance($coursemoduleId);
        require_capability('mod/googlemeet:editrecording', $context);

        $recording = $DB->get_record('googlemeet_recordings', ['id' => $recordingId]);

        $recording->name = $name;
        $recording->timemodified = time();

        $DB->update_record('googlemeet_recordings', $recording);

        return (object)[
            'name' => $recording->name
        ];
    }

    public static function recording_edit_name_returns() {
        return new external_single_structure(
            [
                'name' => new external_value(PARAM_RAW, 'New recording name'),
            ]
        );
    }

    public static function showhide_recording_parameters() {
        return new external_function_parameters(
            [
                'recordingId' => new external_value(PARAM_INT, ''),
                'coursemoduleId' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * Returns the value of the visible attribute of the recording
     * @return string visible
     */
    public static function showhide_recording($recordingId, $coursemoduleId) {
        global $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(
            self::showhide_recording_parameters(),
            [
                'recordingId' => $recordingId,
                'coursemoduleId' => $coursemoduleId
            ]
        );

        $context = context_module::instance($coursemoduleId);
        require_capability('mod/googlemeet:editrecording', $context);

        $recording = $DB->get_record('googlemeet_recordings', ['id' => $recordingId]);

        if ($recording->visible) {
            $recording->visible = false;
        } else {
            $recording->visible = true;
        }

        $recording->timemodified = time();

        $DB->update_record('googlemeet_recordings', $recording);

        return (object)[
            'visible' => $recording->visible
        ];
    }

    public static function showhide_recording_returns() {
        return new external_single_structure(
            [
                'visible' => new external_value(PARAM_RAW, 'Visible or hidden recording'),
            ]
        );
    }

    public static function delete_all_recordings_parameters() {
        return new external_function_parameters(
            [
                'googlemeetId' => new external_value(PARAM_INT, ''),
                'coursemoduleId' => new external_value(PARAM_INT, ''),
            ]
        );
    }

    /**
     * Removes all recordings from Google Meet
     * @return array empty
     */
    public static function delete_all_recordings($googlemeetId, $coursemoduleId) {
        global $DB;

        //Parameter validation
        //REQUIRED
        $params = self::validate_parameters(
            self::delete_all_recordings_parameters(),
            [
                'googlemeetId' => $googlemeetId,
                'coursemoduleId' => $coursemoduleId
            ]
        );

        $context = context_module::instance($coursemoduleId);
        require_capability('mod/googlemeet:removerecording', $context);

        $DB->delete_records('googlemeet_recordings', ['googlemeetid' => $googlemeetId]);

        $googlemeetRecord = $DB->get_record('googlemeet', ['id' => $googlemeetId]);
        $googlemeetRecord->lastsync = time();
        $DB->update_record('googlemeet', $googlemeetRecord);

        return [];
    }

    public static function delete_all_recordings_returns() {
        return new external_single_structure([]);
    }
}

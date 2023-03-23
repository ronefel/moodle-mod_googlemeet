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


/**
 * Helper class for the google docs repository and google calendar events.
 *
 * @package     mod_googlemeet
 * @copyright   2023 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class helper {

    /**
     * Wrapper function to perform an API call and also catch and handle potential exceptions.
     *
     * @param rest $service The rest API object
     * @param string $api The name of the API call
     * @param array $params The parameters required by the API call
     * @return \stdClass The response object
     * @throws \repository_exception
     */
    public static function request(rest $service, string $api, array $params): ?\stdClass {
        try {
            // Retrieving files and folders.
            $response = $service->call($api, $params);
        } catch (\Exception $e) {
            if ($e->getCode() == 403 && strpos($e->getMessage(), 'Access Not Configured') !== false) {
                // This is raised when the Drive API service or the Calendar API service has not been enabled on Google APIs control panel.
                throw new \repository_exception('servicenotenabled', 'mod_googlemeet');
            }
            throw $e;
        }

        return $response;
    }
}

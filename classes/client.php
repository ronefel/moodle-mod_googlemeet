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
    public function get_user_oauth_client() {
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
     */
    public function print_login_popup($attr = null) {
        global $OUTPUT;

        $client = $this->get_user_oauth_client(false);
        $url = new moodle_url($client->get_login_url());
        $state = $url->get_param('state') . '&reloadparent=true';
        $url->param('state', $state);

        $button = new single_button($url, get_string('generateurlroom', 'googlemeet'), 'post', true);
        $button->add_action(new popup_action('click', $url, 'Login'));
        $button->class = 'mdl-align';
        $button = $OUTPUT->render($button);

        return html_writer::div($button, '', $attr);

    }

    /**
     * Store the access token.
     */
    public function callback() {
        $client = $this->get_user_oauth_client();
        // This will upgrade to an access token if we have an authorization code and save the access token in the session.
        $client->is_logged_in();
    }
}
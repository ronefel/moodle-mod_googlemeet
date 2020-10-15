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
 * Plugin administration pages are defined here.
 *
 * @package     mod_googlemeet
 * @category    admin
 * @copyright   2020 Rone Santos <ronefel@hotmail.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
   $settings->add(new admin_setting_configtext('googlemeet/apikey',
      get_string('apikey', 'googlemeet'),
      get_string('configapikey', 'googlemeet'),''));

   $settings->add(new admin_setting_configtext('googlemeet/clientid',
      get_string('clientid', 'googlemeet'),
      get_string('configclientid', 'googlemeet'),''));

   $settings->add(new admin_setting_configtext('googlemeet/scopes',
      get_string('scopes', 'googlemeet'),
      get_string('configscopes', 'googlemeet'),
      'https://www.googleapis.com/auth/calendar.events'));

   $settings->add(new admin_setting_description('help', '',
   '<details>
      <summary>How to generate API key and Client ID</summary>
      <section>
         <h3>Get a Google Account</h3>
         <p>First,&nbsp;<a href="https://www.google.com/accounts" rel="nofollow">sign up</a>&nbsp;for a Google Account if you do not already have one.</p>
         <h3>Create a Google project</h3>
         <p>Go to the&nbsp;<a href="https://console.developers.google.com/project" rel="nofollow">Google API Console</a>. Click&nbsp;<strong>Create project</strong>, enter a name, and click&nbsp;<strong>Create</strong>.</p>
         <h3>Enable Google APIs</h3>
         <p>Next, decide which Google APIs your application needs to use and enable them for your project. Use the&nbsp;<a href="https://developers.google.com/apis-explorer/" rel="nofollow">APIs Explorer</a>&nbsp;to explore Google APIs that the JavaScript client
            library can work with.</p>
         <p>To enable an API for your project, do the following:</p>
         <ol>
            <li><a href="https://console.developers.google.com/apis/library" rel="nofollow">Open the API Library</a>&nbsp;in the Google API Console. If prompted, select a project or create a new one. The API Library lists all available APIs, grouped by product family
               and popularity.</li>
            <li>If the API you want to enable isn\'t visible in the list, use search to find it.</li>
            <li>Select the API you want to enable, then click the&nbsp;<strong>Enable</strong>&nbsp;button.</li>
            <li>If prompted, enable billing.</li>
            <li>If prompted, accept the API\'s Terms of Service.</li>
         </ol>
         <h3>Get access keys for your application</h3>
         <p>Google defines two levels of API access:</p>
         <table class="table table-bordered table-condensed table-striped">
            <tbody>
               <tr>
                     <th>Level</th>
                     <th>Description</th>
                     <th>Requires:</th>
               </tr>
               <tr>
                     <td>Simple</td>
                     <td>API calls do not access any private user data</td>
                     <td>API key</td>
               </tr>
               <tr>
                     <td>Authorized</td>
                     <td>API calls can read and write private user data, or the application\'s own data</td>
                     <td>OAuth 2.0 credentials</td>
               </tr>
            </tbody>
         </table>
         <h4>To acquire an API key for simple access, do the following:</h4>
         <ol>
            <li>Open the&nbsp;<a href="https://console.developers.google.com/apis/credentials" rel="nofollow">Credentials page</a>&nbsp;in the "APIs &amp; Services" console.</li>
            <li>Click&nbsp;<strong>Create credentials &gt; API key</strong>&nbsp;and select the appropriate key type.</li>
         </ol>
         <p>To keep your API keys secure, follow the&nbsp;<a href="https://support.google.com/cloud/answer/6310037" rel="nofollow">best practices for securely using API keys</a>.</p>
         <h4>To acquire OAuth 2.0 credentials for authorized access, do the following:</h4>
         <ol>
            <li>Open the&nbsp;<a href="https://console.developers.google.com/apis/credentials" rel="nofollow">Credentials page</a>&nbsp;in the "APIs &amp; Services" console.</li>
            <li>Click&nbsp;<strong>Create credentials &gt; OAuth client ID</strong>&nbsp;and select the appropriate Application type.</li>
         </ol>
         <p>For information about using OAuth 2.0 credentials, see the&nbsp;<a href="https://developers.google.com/api-client-library/javascript/features/authentication" rel="nofollow">Authentication</a>&nbsp;page.</p>
      </section>
   </details>'));
}

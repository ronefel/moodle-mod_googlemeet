define([
  'core/ajax',
  'core/str',
  'core/notification',
  'core/templates',
  'mod_googlemeet/gapi'
], function(Ajax, Str, Notification, Templates, gapi) {
  return {
    init: function(
      clientId,
      apiKey,
      googlemeet,
      hasRecording,
      courseModuleId,
      hasCapability
    ) {

      // Array of API discovery doc URLs for APIs used by the quickstart
      var discoveryDocs = ["https://www.googleapis.com/discovery/v1/apis/drive/v3/rest"];

      // Authorization scopes required by the API
      var scope = 'https://www.googleapis.com/auth/drive';

      // Meeting code
      var meetingCode = googlemeet.url.substr(24, 12);

      // Google Drive Meet Recordings folder owner email.
      var ownerEmail;

      // // Load strings
      var notpossiblesync = '';
      var notfoundrecordingsfolder = '';
      var notfoundrecordingname = '';
      var stror = '';
      Str.get_strings([
        {key: 'notpossiblesync', component: 'mod_googlemeet'},
        {key: 'notfoundrecordingsfolder', component: 'mod_googlemeet'},
        {key: 'notfoundrecordingname', component: 'mod_googlemeet'},
        {key: 'or', component: 'mod_googlemeet'},
      ]).done(function(strs) {
        notpossiblesync = strs[0];
        notfoundrecordingsfolder = strs[1];
        notfoundrecordingname = strs[2];
        stror = strs[3];
      }).fail(Notification.exception);

      // Elements references
      var syncDriveButton = document.getElementById('id_syncdrivebutton');

      /**
       *  Initializes the API client library and sets up sign-in state
       *  listeners.
       */
      function initClient() {
        gapi.client.init({
          apiKey: apiKey,
          clientId: clientId,
          discoveryDocs: discoveryDocs,
          scope: scope
        }).then(function() {
          syncDriveButton.onclick = handleSyncDrive;
          syncDriveButton.disabled = false;
          return;
        }).catch(function(error) {
          syncDriveButton.disabled = true;
          appendPre(JSON.stringify(error, null, 2));
        });
      }

      /**
       * Initiates sync with Google Drive
       */
      function handleSyncDrive() {
        gapi.auth2.getAuthInstance().signIn({prompt: 'select_account'}).then(function() {
          getMeetFolder();
          return;
        }).catch();
      }

      /**
       * Shows a loading on the screen
       * @param {boolean} show
       */
      function showLoading(show) {
        var googlemeetSyncImg = document.getElementById('googlemeet_syncimg');

        if (show) {
          googlemeetSyncImg.style.display = "flex";
          syncDriveButton.disabled = true;
        } else {
          googlemeetSyncImg.style.display = "none";
          syncDriveButton.disabled = false;
        }
      }

      /**
       * Append a pre-element to the body that contains the message
       * provided as its text node. Used to display API call errors.
       *
       * @param {string} message Text to be placed in pre element.
       */
      function appendPre(message) {
        var pre = document.getElementById('googlemeetcontentlog');
        var textContent = document.createTextNode(message + '\n');
        pre.style.display = "block";
        pre.appendChild(textContent);
      }

      /**
       * Hide the pre tag
       */
      function hidePre() {
        var pre = document.getElementById('googlemeetcontentlog');
        pre.style.display = "none";
        pre.innerHTML = "";
      }

      /**
       * Generates the shareable link to anyone with the link
       * @param {string} fileId Google Drive recording ID
       */
      function setPermission(fileId) {
        gapi.client.drive.permissions.create({
          resource: {
            'type': 'anyone',
            'role': 'reader'
          },
          fileId: fileId,
          fields: 'id',
        }).then().catch();
      }

      /**
       * Build the query to search for the recording name in Google Drive.
       *
       * @returns {string}
       */
      function getNameQuery() {
        var query = "and (name contains '" + meetingCode + "'";
        query += " or name contains '" + googlemeet.originalname + "')";

        return query;
      }

      /**
       * Receive recording time duration in milliseconds and format to string.
       *
       * @param {string} s The time in milliseconds.
       * @returns {string} Formatted time. Example 1:01:20
       */
      function getTimeString(s) {
        var secNum = Math.floor(parseInt(s, 10) / 1000);
        var hours = Math.floor(secNum / 3600);
        var minutes = Math.floor((secNum - (hours * 3600)) / 60);
        var seconds = secNum - (hours * 3600) - (minutes * 60);

        if (seconds < 10) {
          seconds = "0" + seconds;
        }

        if (hours > 0) {
          if (minutes < 10) {
            minutes = "0" + minutes;
          }
          return hours + ':' + minutes + ':' + seconds;
        } else {
          return minutes + ':' + seconds;
        }
      }

      /**
       * Render the recording table with the recordings coming from Google Drive.
       * @param {array} recordings
       */
      function renderTemplate(recordings) {
        Templates.render('mod_googlemeet/recordingstable', {
          recordings: recordings,
          coursemoduleid: courseModuleId,
          hascapability: hasCapability
        }).then(function(html, js) {
          showLoading(false);

          Templates.replaceNodeContents('#googlemeet_recordings_table', html, js);

          document.getElementById('id_creatoremail').innerHTML = ownerEmail;
          document.getElementById('id_lastsync').innerHTML = new Date().toLocaleString().substr(0, 16);
          return;
        }).fail(Notification.exception).fail(function() {
          showLoading(false);
        });
      }

      /**
       * Get recordings from Google Drive
       * @param {string} meetFolderId 'Meet Recordings' folder ID
       */
      function getFiles(meetFolderId) {
        gapi.client.drive.files.list({
          'q': "parents='" + meetFolderId +
            "' and trashed=false and mimeType='video/mp4' " + getNameQuery(),
          'pageSize': 1000,
          'fields': "files(id,name,permissionIds,createdTime,videoMediaMetadata,webViewLink)"
        }).then(function(response) {
          var files = response.result.files;
          if (files && files.length > 0) {
            for (var i = 0; i < files.length; i++) {
              var file = files[i];
              if (!file.permissionIds.includes('anyoneWithLink')) {
                setPermission(file.id);
              }

              files[i].recordingId = file.id;
              files[i].duration = getTimeString(file.videoMediaMetadata.durationMillis);
              files[i].createdTime = Math.floor(new Date(file.createdTime).getTime() / 1000);

              delete (files[i].id);
              delete (files[i].permissionIds);
              delete (files[i].videoMediaMetadata);
            }

            Ajax.call([{
              methodname: 'mod_googlemeet_sync_recordings',
              args: {
                googlemeetid: googlemeet.id,
                creatoremail: ownerEmail,
                files: files,
                coursemoduleid: courseModuleId
              }
            }])[0].then(function(response) {
              renderTemplate(response);
              hasRecording = true;
              return;
            }).fail(Notification.exception).fail(function() {
              showLoading(false);
            });

          } else {

            if (hasRecording) {
              Ajax.call([{
                methodname: 'mod_googlemeet_delete_all_recordings',
                args: {
                  googlemeetid: googlemeet.id,
                  coursemoduleid: courseModuleId
                }
              }])[0].then(function(response) {
                renderTemplate(response);
                hasRecording = false;
                return;
              }).fail(Notification.exception).fail(function() {
                showLoading(false);
              });
            }
            var notfoundmsg = notfoundrecordingname + ' "' + meetingCode + '" ';
            if (googlemeet.originalname) {
              notfoundmsg += stror + ' "' + googlemeet.originalname + '"';
            }
            appendPre(notfoundmsg);

            showLoading(false);
          }
          return;
        }).catch(function(error) {
          showLoading(false);
          appendPre(JSON.stringify(error.result.error, null, 2));
        });
      }

      /**
       * Get 'Meet Recordings' folder from Google Drive
       */
      function getMeetFolder() {
        showLoading(true);
        hidePre();
        gapi.client.drive.files.list({
          'q': "name='Meet Recordings'",
          'pageSize': 100,
          'fields': "nextPageToken, files(id,owners)"
        }).then(function(response) {
          var files = response.result.files;

          if (files && files.length > 0) {
            if (!googlemeet.creatoremail || googlemeet.creatoremail === files[0].owners[0].emailAddress) {
              ownerEmail = files[0].owners[0].emailAddress;
              getFiles(files[0].id);
              return;
            }

            appendPre(notpossiblesync);
            showLoading(false);
          } else {
            appendPre(notfoundrecordingsfolder);
          }
          return;
        }).catch(function(error) {
          showLoading(false);
          appendPre(JSON.stringify(error.result.error, null, 2));
        });
      }

      /**
       *  On load, called to load the auth2 library and API client library.
       */
      gapi.load('client:auth2', initClient);
      // Function handleClientLoad() {
      // }

      // window.addEventListener("load", handleClientLoad(), false);
    }
  };
});

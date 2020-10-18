/* global gapi */
define(['core/notification', 'core/str'], function (notification, str) {
  return {
    init: function (clientId, apiKey, scope) {

      // Array of API discovery doc URLs for APIs used by the quickstart
      var discoveryDocs = ["https://www.googleapis.com/discovery/v1/apis/calendar/v3/rest"];

      // Load strings
      var requiredfield = '';
      var creatingcalendarevent = '';
      var eventsuccessfullycreated = '';
      var creatingconferenceroom = '';
      var conferencesuccessfullycreated = '';
      str.get_strings([
        {key: 'requiredfield', component: 'mod_googlemeet'},
        {key: 'creatingcalendarevent', component: 'mod_googlemeet'},
        {key: 'eventsuccessfullycreated', component: 'mod_googlemeet'},
        {key: 'creatingconferenceroom', component: 'mod_googlemeet'},
        {key: 'conferencesuccessfullycreated', component: 'mod_googlemeet'},
      ]).done(function(strs) {
        requiredfield = strs[0];
        creatingcalendarevent = strs[1];
        eventsuccessfullycreated = strs[2];
        creatingconferenceroom = strs[3];
        conferencesuccessfullycreated = strs[4];
      }).fail(notification.exception);

      // Elements references
      var generateLinkButton = document.getElementById('id_generateLink');
      var googlemeeturlInput = document.getElementById('id_url');
      var googlemeeturlInputError = document.getElementById('id_error_url');
      var form = document.querySelector('#region-main .mform');

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
        }).then(function () {

          generateLinkButton.onclick = handleAuthClick;
        }, function (error) {
          generateLinkButton.disabled = true;
          appendPre(JSON.stringify(error, null, 2));
        });
      }

      /**
       *  Sign in the user upon button click.
       */
      function handleAuthClick() {
        var formData = new FormData(form);
        var nameInput = document.getElementById('id_name');
        var nameError = document.getElementById('id_error_name');

        if (formData.get('name').trim().length === 0) {

          nameInput.classList.add('is-invalid');
          nameInput.focus();
          nameError.innerText = '- '+ requiredfield;

          return;
        }

        nameInput.classList.remove('is-invalid');
        nameError.innerText = '';

        gapi.auth2.getAuthInstance().signIn({prompt: 'select_account'}).then(function() {
          createEvent();
        });
      }

      /**
       * Append a pre element to the body containing the given message
       * as its text node. Used to display the results of the API call.
       *
       * @param {string} message Text to be placed in pre element.
       */
      function appendPre(message) {
        var log = document.getElementById('googlemeetlog');
        log.setAttribute('open', true);
        var pre = document.getElementById('googlemeetcontentlog');
        var textContent = document.createTextNode(message + '\n');
        pre.appendChild(textContent);
      }

      function disableInputs() {
        var inputsForReadOnly = [
          'id_url',
          'id_timeopen_enabled',
          'id_timeopen_day',
          'id_timeopen_month',
          'id_timeopen_year',
          'id_timeopen_hour',
          'id_timeopen_minute',
          'id_timeopen_calendar',
          'id_timeclose_enabled',
          'id_timeclose_day',
          'id_timeclose_month',
          'id_timeclose_year',
          'id_timeclose_hour',
          'id_timeclose_minute',
          'id_timeclose_calendar',
          'id_generateLink'
        ];

        inputsForReadOnly.forEach(function(inputId) {
          var input = document.getElementById(inputId);

          if (input.getAttribute('type') === 'checkbox') {
            input.parentElement.style.display = 'none';
            return;
          }

          input.setAttribute('readonly', true);
          input.style.pointerEvents = 'none';
          input.style.touchAction = 'none';
          input.setAttribute('tabindex', '-1');
          input.setAttribute('aria-disabled', true);
        });
      }

      function createEvent() {
        var formData = new FormData(form);

        var currentDate = new Date();
        var start = {};
        var end = {};

        if (formData.get('timeopen[enabled]')) {
          start = {
            dateTime: formData.get('timeopen[year]')+'-'+
                      formData.get('timeopen[month]')+'-'+
                      formData.get('timeopen[day]')+'T'+
                      formData.get('timeopen[hour]')+':'+
                      formData.get('timeopen[minute]')+':00'+
                      formData.get('timezoneoffset'),
            timezone: formData.get('timezone'),
          };
        } else {
          start = {
            date: currentDate.getFullYear()+'-'+
                  (currentDate.getMonth() + 1)+'-'+
                  currentDate.getDate(),
          };
        }

        if (formData.get('timeclose[enabled]')) {
          end = {
            dateTime: formData.get('timeclose[year]')+'-'+
                      formData.get('timeclose[month]')+'-'+
                      formData.get('timeclose[day]')+'T'+
                      formData.get('timeclose[hour]')+':'+
                      formData.get('timeclose[minute]')+':00'+
                      formData.get('timezoneoffset'),
            timezone: formData.get('timezone'),
          };
        } else {
          end = {
            date: currentDate.getFullYear()+'-'+
                  (currentDate.getMonth() + 1)+'-'+
                  currentDate.getDate(),
          };
        }

        appendPre(creatingcalendarevent);

        var event = {
          summary: formData.get('name'),
          description: formData.get('introeditor[text]'),
          start: start,
          end: end,
        };

        var request = gapi.client.calendar.events.insert({
          'calendarId': 'primary',
          'resource': event
        });

        request.execute(function (event) {
          appendPre('   '+ eventsuccessfullycreated +': '+ event.creator.email);
          appendPre(creatingconferenceroom);

          var eventPatch = {
            conferenceData: {
              createRequest: { requestId: event.id }
            }
          };

          gapi.client.calendar.events.patch({
            calendarId: "primary",
            eventId: event.id,
            resource: eventPatch,
            sendNotifications: false,
            conferenceDataVersion: 1
          }).execute(function (event) {
            appendPre('   '+ conferencesuccessfullycreated +': ' + event.hangoutLink);

            googlemeeturlInput.value = event.hangoutLink;
            googlemeeturlInput.focus();
            googlemeeturlInput.classList.remove("is-invalid");
            googlemeeturlInputError.innerText = '';

            disableInputs();
          });
        });
      }

      /**
       *  On load, called to load the auth2 library and API client library.
       */
      function handleClientLoad() {
        gapi.load('client:auth2', initClient);
      }

      window.addEventListener("load", handleClientLoad(), false);
    }
  };
});

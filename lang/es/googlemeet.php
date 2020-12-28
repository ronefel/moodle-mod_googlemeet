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

$string['apikey'] = 'Clave de API';
$string['checkweekdays'] = 'Seleccione los días de la semana dentro del rango de fechas seleccionado.';
$string['clientid'] = 'ID de cliente OAuth';
$string['clientid_desc'] = '<a href="https://github.com/ronefel/moodle-mod_googlemeet/wiki/How-to-create-the-API-key-and-OAuth-client-ID" target="_blank">¿Cómo crear la clave de API y el id de cliente OAuth?</a>';
$string['date'] = 'Fecha';
$string['duration'] = 'Duración';
$string['earlierto'] = 'La fecha del evento no puede ser menor a la fecha de inicio del curso ({$a}).';
$string['emailcontent'] = 'Contenido del email';
$string['emailcontent_default'] = '<p>Hola %userfirstname%,</p>
<p>Esta notificación es para recordarle que tendrá un evento de Google Meet en %coursename%</p>
<p><b>%googlemeetname%</b></p>
<p>Cuando: %eventdate% %duration% %timezone%</p>
<p>Link de acceso: %url%</p>';
$string['emailcontent_help'] = 'Cuando una notificación es enviada al estudiante, se toma el contenido del email de este campo, los siguientes wildcards pueden ser utilizados:
<ul>
<li>%userfirstname%</li>
<li>%userlastname%</li>
<li>%coursename%</li>
<li>%googlemeetname%</li>
<li>%eventdate%</li>
<li>%duration%</li>
<li>%timezone%</li>
<li>%url%</li>
<li>%cmid%</li>
</ul>';
$string['entertheroom'] = 'Ingrese la sala';
$string['eventdate'] = 'Fecha del evento';
$string['from'] = 'De';
$string['generateurlroom'] = 'Generar URL de la sala';
$string['googlemeet:addinstance'] = 'Agregar un nuevo Google Meet™ para Moodle';
$string['googlemeet:editrecording'] = 'Editar grabación';
$string['googlemeet:removerecording'] = 'Eliminar recordings';
$string['googlemeet:syncgoogledrive'] = 'Sincronizar con Google Drive';
$string['googlemeet:view'] = 'Ver el contenido de Google Meet™ para Moodle';
$string['hide'] = 'Ocultar';
$string['invalideventenddate'] = 'Esta fecha no puede ser menor que la "fecha del evento"';
$string['invalideventendtime'] = 'La hora final debe ser mayor a la hora inicial';
$string['invalidstoredurl'] = 'No puede mostrar este recurso, la URL de Google Meet es inválida.';
$string['jstableinfo'] = 'Mostrando {start} a {end} de {rows} grabaciones';
$string['jstableinfofiltered'] = 'Mostrando {start} a {end} de {rows} grabaciones (filtradas de {rowsTotal} grabaciones)';
$string['jstableloading'] = 'Cargando...';
$string['jstablenorows'] = 'No se encontraron grabaciones';
$string['jstableperpage'] = '{select} grabaciones por página';
$string['jstablesearch'] = 'Buscar...';
$string['lastsync'] = 'Última sincronización:';
$string['loading'] = 'Cargando';
$string['messageprovider:notification'] = 'Recordatorio de inicio del evento de Google Meet';
$string['minutesbefore'] = 'Minutos antes';
$string['minutesbefore_help'] = 'Número de minutos antes de iniciar un evento en los cuales debe ser enviada la notificación.';
$string['modulename'] = 'Google Meet™ para Moodle';
$string['modulename_help'] = 'El módulo Google Meet™ para Moodle permite al profesor crear salas o eventos de Google Meet como recursos de un curso y luego de la reunión permite la visualización de la grabación, guardada en Google Drive, a los estudiantes.
<p>©2020 Google LLC Todos los derechos reservados.<br/>
Google Meet y el logo de Google Meets son marcas registradas de Google LLC.</p>
';
$string['modulenameplural'] = 'Instancias de Google Meet™ para Moodle';
$string['multieventdateexpanded'] = 'Fecha de evento recurrente expandida';
$string['multieventdateexpanded_desc'] = 'Muestra la configuración "Fecha de evento recurrente" expandida por defecto cuando se crea una nueva sala.';
$string['name'] = 'Nombre';
$string['never'] = 'Nunca';
$string['notfoundrecordingname'] = 'No se encontró grabaciones con este nombre';
$string['notfoundrecordingsfolder'] = 'La carpeta "Meet Recordings" no fue encontrada en Google Drive.';
$string['notification'] = 'Notificación';
$string['notificationexpanded'] = 'Notificación expandida';
$string['notify'] = 'Enviar notificacion al estudiante';
$string['notify_help'] = 'Se se marca, se enviará una notificación a los estudiantes con la fecha de inicio del evento.';
$string['notifycationexpanded_desc'] = 'Muestra la configuración "Notification" expandida por defecto cuando se crea una nueva sala.';
$string['notifytask'] = 'Tarea de notificación para Google Meet™ para Moodle';
$string['notpossiblesync'] = 'No es posible sincronizar con la cuenta que ha creado la sala.';
$string['or'] = 'o';
$string['play'] = 'Reproducir';
$string['pluginadministration'] = 'Administración de Google Meet™ para Moodle';
$string['pluginname'] = 'Google Meet™ para Moodle';
$string['privacy:metadata:googlemeet_notify_done'] = 'Registra las notificaciones enviadas a los usuarios del inicio de los eventos. Esta información es temporal y es eliminada despues de la fecha de inicio del evento.';
$string['privacy:metadata:googlemeet_notify_done:eventid'] = 'Id del evento';
$string['privacy:metadata:googlemeet_notify_done:userid'] = 'Id del usuario';
$string['privacy:metadata:googlemeet_notify_done:timesent'] = 'Timestamp que indica cuando un usuario ha recibido la notificación';
$string['earlierto'] = 'La fecha del evento no puede ser inferior a la fecha de inicio del curso. ({$a}).';
$string['recording'] = 'Grabación';
$string['recordings'] = 'Grabaciones';
$string['recordingswiththename'] = 'Grabaciones con el nombre:';
$string['recurrenceeventdate'] = 'Fecha de evento recurrente';
$string['recurrenceeventdate_help'] = 'Esta funcionalidad permite crear multiples eventos recurrentes a partir de la fecha del evento..
<br>* <strong>Repetir en</strong>: Seleccione los días de la semana en los cuales se llevará a cabo las clases (por ejemplo, Lunes / Miercoles / Viernes).
<br>* <strong>Repetir cada</strong>: Permite una configuración por frecuencia semanal. Por ejemplo, si su clase se llevará a cabo cada semana, seleccione 1; si se llevará a cabo cada dos semana, seleccione 2; cada tres semanas, seleccione 3, etc.
<br>* <strong>Repeat hastsa</strong>: Seleccione el último día de la clase, es decir, el último día en el que se llevará el evento de manera recurrente.
';
$string['repeatasfollows'] = 'Repetir la fecha de evento anterior de la siguiente manera';
$string['repeatevery'] = 'Repetir cada';
$string['repeaton'] = 'Repetir en';
$string['repeatuntil'] = 'Repetir hasta';
$string['requirednamefield'] = 'Ingrese el nombre de la sala que se creará automáticamente.';
$string['roomcreator'] = 'Creador de la sala:';
$string['roomname'] = 'Nombre de la sala';
$string['roomurl'] = 'URL de la sala';
$string['roomurlexpanded'] = 'URL de la sala expandida';
$string['roomurlexpanded_desc'] = 'Mostrar la configuración "URL de la sala" expandida por defecto cuando se crear una sala.';
$string['show'] = 'Mostrar';
$string['strftimedm'] = '%a. %d %b.';
$string['strftimedmy'] = '%a. %d %b. %Y';
$string['strftimedmyhm'] = '%a. %d %b. %Y %H:%M';
$string['strftimehm'] = '%H:%M';
$string['syncwithgoogledrive'] = 'Sincronizar con Google Drive';
$string['thereisnorecordingtoshow'] = 'No hay grabaciones para mostrar.';
$string['timeahead'] = 'No es posible crear eventos recurrentes para la fecha indicada porque excede más de un año, ajuste la fecha de inicio y fin.';
$string['timedate'] = '%d/%m/%Y %H:%M';
$string['to'] = 'a';
$string['today'] = 'Hoy';
$string['upcomingevents'] = 'Próximos eventos';
$string['url_failed'] = 'Una URL de Google Meet es requerida';
$string['visible'] = 'Visible';
$string['week'] = 'Semana(s)';

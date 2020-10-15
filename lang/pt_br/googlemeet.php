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
$string['modulenameplural'] = 'Instâncias do Google Meet';
$string['modulename_help'] = 'O módulo Google Meet permite que um professor crie uma sala de reunião como um recurso do curso.';

$string['googlemeetname'] = 'Nome da sala';

$string['generateurlautomatically'] = 'Gerar URL da Sala automaticamente';

$string['url'] = 'Url da sala';
$string['url_help'] = 'Pode inserir manualmente a URL da sala do Google Meet no seguinte formato: https://meet.google.com/aaa-aaaa-aaa <br/> ou pode gerar a URL automaticamente na seção abaixo "<strong>'.$string['generateurlautomatically'].'</strong>".';
$string['url_failed'] = 'Insira uma url válida do Google Meet com o seguinte formato: https://meet.google.com/aaa-aaaa-aaa';

$string['introeditor'] = 'Descrição';
$string['introeditor_help'] = 'Esta descrição também será salva no evento do calendário se a URL for gerada automáticamente na seção abaixo "<strong>'.$string['generateurlautomatically'].'</strong>".';

$string['instructions'] = '<details>
<summary><b>Instruções</b></summary>
<section>
<ol>
<li>É obrigatório informar o nome da sala no campo "Nome da sala" acima.</li>
<li>O campo "Descrição" também será usado como descrição no evento da Agenda (opcional).</li>
<li>Os campos de data abaixo são opcionais, eles servem para informar a data inicial e final do evento na Agenda.</li>
<li>Ao clicar no botão "Gerar url da sala" abrirá uma janela onde deverá selecionar sua conta institucional ou fazer login caso não esteja logado. Se selecionar uma conta que não é institucional mostrará um erro e não será possível continuar.</li>
<li>Na próxima tela deverá dar permissão para "Ver e editar eventos em todas as suas Agendas".</li>
<li>Com a conta selecionada e a permissão concedida será criado automaticamente um evento na Agenda com um link da sala de conferência do Google Meet. O campo "Url da sala" acima será preenchido automaticamente com este link.</li>
<li>Clicar no botão "Salvar e voltar ao curso".</li>
</ol>
</section>
</details>';
$string['googlemeetgeneratelink'] = 'Gerar url da sala';
$string['googlemeetopen'] = 'Data de início do evento';
$string['googlemeetclose'] = 'Data de término do evento';
$string['googlemeetopenclose'] = 'Data de início e data de término do evento';
$string['googlemeetopenclose_help'] = 'Caso desativadas, irá gerar o evento no calendário para o dia de hoje.';

$string['warning'] = 'Aviso';
$string['warningtext'] = 'O Recurso GoogleMeet não pode ser editado. Exclua este e crie um novo.';

$string['pluginadministration'] = 'Administração do módulo Google Meet';

$string['clicktoopen'] = 'Clique no link {$a} para acessar a sala do Google Meet.';

$string['clientid'] = 'Client ID';
$string['configclientid'] = 'ID do cliente do console do desenvolvedor';
$string['apikey'] = 'API key';
$string['configapikey'] = 'Chave da API do console do desenvolvedor';
$string['scopes'] = 'Escopos';
$string['configscopes'] = 'Escopos de autorização exigidos pela API; vários escopos podem ser incluídos, separados por espaços.';

$string['requiredfield'] = 'Inserir um valor.';
$string['creatingcalendarevent'] = 'Criando evento no calendário...';
$string['eventsuccessfullycreated'] = 'Evento criado com sucesso na conta';
$string['creatingconferenceroom'] = 'Criando sala de conferência...';
$string['conferencesuccessfullycreated'] = 'Sala de conferência criada com sucesso';
$string['invalidstoredurl'] = 'Impossível mostrar este recurso pois a URL do Googlem Meet é inválida.';

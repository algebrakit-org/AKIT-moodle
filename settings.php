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
 * Admin settings for the multichoice question type.
 *
 * @package   qtype_multichoice
 * @copyright  2015 onwards Nadav Kavalerchik
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

//$algebrakitSettings = new admin_settingpage('modsettingalgebrakit', get_string('settingsPageTitle', 'qtype_algebrakit'), 'moodle/site:config');

if ($ADMIN->fulltree) {

    $settings->add(new admin_setting_configtext('qtype_algebrakit/apikey',
    get_string('apikey', 'qtype_algebrakit'), get_string('apikey_desc', 'qtype_algebrakit'),
    "", PARAM_TEXT));

    $menu = [
        new lang_string('europe', 'qtype_algebrakit'),
        new lang_string('singapore', 'qtype_algebrakit')
    ];
    $settings->add(new admin_setting_configselect('qtype_algebrakit/region',
    new lang_string('region', 'qtype_algebrakit'),
    new lang_string('region_desc', 'qtype_algebrakit'), '0', $menu));

    $settings->add(new admin_setting_configcheckbox('qtype_algebrakit/enable_embedded_editor',
    get_string('enable_embedded_editor', 'qtype_algebrakit'), get_string('enable_embedded_editor_desc', 'qtype_algebrakit'),
    1, 1, 0));

    

}

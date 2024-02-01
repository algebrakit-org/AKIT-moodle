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
 * Upgrade script for the AlgebraKiT question type.
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Function to upgrade AlgebraKiT question type.
 * @param int $oldversion the version we are upgrading from
 * @return bool true on success
 */
function xmldb_qtype_algebrakit_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager(); // loads database manager

    // Add a new field to an existing table, only if it doesn't exist
    if ($oldversion < 20240131011) {
        $table = new xmldb_table('question_algebrakit');

        // drop major_version
        $field = new xmldb_field('major_version');
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        //Add field exercise_in_json to be added to question_algebrakit
        $field = new xmldb_field('exercise_in_json', XMLDB_TYPE_TEXT, 'big', null, false, null, null, null, 'exercise_id');

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // remove the index on exercise_id before changing it
        $index = new xmldb_index('exercise_id_index', XMLDB_INDEX_NOTUNIQUE, ['exercise_id']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }
        $index = new xmldb_index('question_id_index', XMLDB_INDEX_NOTUNIQUE, ['exercise_id']);
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // field exercise_id is nullable
        $field = new xmldb_field('exercise_id', XMLDB_TYPE_CHAR, '255', null, null, null, null, 'question_id');
        $dbman->change_field_notnull($table, $field);
        
        // add index on exercise_id
        $index = new xmldb_index('exercise_id_index', XMLDB_INDEX_NOTUNIQUE, ['exercise_id']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Algebrakit savepoint reached
        upgrade_plugin_savepoint(true, 20240131011, 'qtype', 'algebrakit');
    }
    return true;
}

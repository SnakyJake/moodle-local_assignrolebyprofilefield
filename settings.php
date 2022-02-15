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
 * Version details.
 *
 * @package    local_assignrolebyprofilefield
 * @author     Jakob Heinemann <jakob@jakobheinemann.de>, Fabian Bech <f.bech@koppelsberg.de>
 * @copyright  Jakob Heinemann, Fabian Bech
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

global $DB;

$systemcontext = context_system::instance();

if ($hassiteconfig){
    $pluginname = get_string("pluginname","local_assignrolebyprofilefield");

    $usercontextroles = $DB->get_records('role_context_levels', array('contextlevel' => CONTEXT_USER),'', 'roleid');

    $roles = role_fix_names($usercontextroles, $systemcontext, ROLENAME_ORIGINAL);
    $roleurl = $CFG->wwwroot . '/' . $CFG->admin . '/roles/define.php';

    $settings = new admin_settingpage('local_assignrolebyprofilefield_settings', $pluginname);
    
    $settings->add(new admin_setting_heading('local_assignrolebyprofilefield/settings',get_string('settings'),''));

    $options = $DB->get_records_menu('user_info_field',null,'',"id, CONCAT(shortname,' (',name,')')");

    foreach($roles as $role){
        $settings->add(new admin_setting_heading('local_assignrolebyprofilefield/role'.$role->id,'', '<a href="' . $roleurl . '?action=view&amp;roleid=' . $role->id . '">' . $role->localname . '</a>'));
        $settings->add(new admin_setting_configcheckbox('local_assignrolebyprofilefield/roleenabled'.$role->id,get_string('enable'),'',0));
        $tmp = new admin_setting_configselect('local_assignrolebyprofilefield/'.$role->id, get_string('settings_assignrolebyprofilefield_linkedfield','local_assignrolebyprofilefield'),'',null,$options);
        $tmp->add_dependent_on('local_assignrolebyprofilefield/roleenabled'.$role->id);
        $settings->add($tmp);
        $settings->hide_if('local_assignrolebyprofilefield/'.$role->id,'local_assignrolebyprofilefield/roleenabled'.$role->id);
    }
    $ADMIN->add('localplugins',$settings);
}

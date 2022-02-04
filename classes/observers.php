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
 * @author     Jakob Heinemann <jakob@jakobheinemann.de>
 * @copyright  Jakob Heinemann
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_assignrolebyprofilefield;

defined('MOODLE_INTERNAL') || die();

global $DB;

class observers {

    private static $_cfg = null;
    private static $_usercontextroles = null;

    /**
     * User deleted
     * is this needed?
     *
     * @param \core\event\user_deleted $event the event
     * @return void
     */
    public static function user_deleted(\core\event\user_deleted $event) {
    }

    /**
     * User updated
     *
     * @param \core\event\user_updated $event the event
     * @return void
     */
    public static function user_updated(\core\event\user_updated $event) {
        global $DB;
        $userprofile = profile_user_record($event->relateduserid);
        $context = \context_user::instance($event->relateduserid);

        if(!self::$_cfg){
            self::$_cfg = \get_config('local_assignrolebyprofilefield');
            
        }
        if(!self::$_usercontextroles){
            self::$_usercontextroles = $DB->get_records('role_context_levels', array('contextlevel' => CONTEXT_USER),'', 'roleid');
        }

        foreach(self::$_usercontextroles as $key=>$role){
            $roleenabled = 'roleenabled'.$role->roleid;
            if(property_exists(self::$_cfg,$roleenabled) && self::$_cfg->$roleenabled){
                $property = 'linkedfield'.$role->roleid;
                if(property_exists(self::$_cfg,$property) && self::$_cfg->$property){
                    $field_id = intval(self::$_cfg->$property);
                    $field = $DB->get_record('user_info_field', ['id' => $field_id], '*');
                    $fieldname = $field->shortname;
                    if(object_property_exists($userprofile,$fieldname)){
                        $data = $userprofile->$fieldname;
                        role_unassign_all(array('contextid'=>$context->id,'component'=>"local_assignrolebyprofilefield",'roleid'=>$key));
                        if(is_array($data)){
                            foreach($data as $value){
                                role_assign($key,intval($value),$context->id,"local_assignrolebyprofilefield");
                            }
                        } else {
                            role_assign($key,intval($data),$context->id,"local_assignrolebyprofilefield");
                        }
                    }
                }
            }
        }
    }
}

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
 * force an update of all user assignments. May take awhile, do this at night!
 *
 * @package    local_assignrolebyprofilefield
 * @author     Jakob Heinemann <jakob@jakobheinemann.de>, Fabian Bech <f.bech@koppelsberg.de>
 * @copyright  Jakob Heinemann, Fabian Bech
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once('classes/observers.php');


$users = $DB->get_records("user",["deleted"=>0]);

$event = new stdClass();

foreach($users as $user){
    $event->objectid = $user->id;
    $event->relateduserid =$user->id;
    $event->contextid = \context_user::instance($user->id)->id;
    \local_assignrolebyprofilefield\observers::user_updated($event);
}


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

namespace local_assignrolebyprofilefield;

use \core\event;

defined('MOODLE_INTERNAL') || die();

class observers
{
	/**
	 * User updated
	 *
	 * @param event\user_updated|event\user_created $event the event
	 * @return void
	 */
	public static function user_updated($event)
	{
		global $DB;
		$userid = $event->relateduserid;
		$contextid = $event->contextid;	// gets contextid in lib/classes/event/user_updated.php:129 and lib/classes/event/base.php:227

		foreach (self::get_enabled_roles() as $roleid => $fieldid)
		{
			//adhoc task?
			role_unassign_all(['contextid' => $contextid, 'component' => 'local_assignrolebyprofilefield', 'roleid' => $roleid]);

			if ($data = $DB->get_field('user_info_data', 'data', ['userid' => $userid, 'fieldid' => $fieldid]))
			{
				foreach (json_decode($data) as $uid)
				{
					role_assign($roleid, intval($uid), $contextid, 'local_assignrolebyprofilefield', $userid);
				}
			}
		}
	}

	private static function get_enabled_roles()
	{
		$cfg = get_config('local_assignrolebyprofilefield');
		$usercontextroleids = get_roles_for_contextlevels(CONTEXT_USER);

		$result = [];

		foreach ($usercontextroleids as $roleid)
		{
			if (property_exists($cfg, 'roleenabled'.$roleid) && $cfg->{'roleenabled'.$roleid})
			{
				if (property_exists($cfg, $roleid) && $cfg->$roleid)
				{
					$result[$roleid] = $cfg->$roleid;
				}
			}
		}

		return $result;
	}
}

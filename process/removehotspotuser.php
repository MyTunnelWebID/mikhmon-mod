<?php
/*
 *  Copyright (C) 2018 Laksamadi Guko.
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
session_start();
// hide all error
error_reporting(0);
ignore_user_abort(true);
ini_set('max_execution_time', 0);
if (function_exists('set_time_limit')) {
	@set_time_limit(0);
}

function mikhmon_get_rows_by_name($rows) {
	$rowsByName = array();

	if (!is_array($rows)) {
		return $rowsByName;
	}

	foreach ($rows as $row) {
		if (!isset($row['name']) || $row['name'] == '') {
			continue;
		}

		$rowName = $row['name'];
		if (!isset($rowsByName[$rowName])) {
			$rowsByName[$rowName] = array();
		}

		$rowsByName[$rowName][] = $row;
	}

	return $rowsByName;
}

function mikhmon_reconnect_api($API) {
	global $iphost, $userhost, $passwdhost;

	if (!isset($API)) {
		return false;
	}

	$API->disconnect();
	$API->debug = false;
	$API->attempts = 2;
	$API->delay = 1;

	return $API->connect($iphost, $userhost, decrypt($passwdhost));
}

function mikhmon_remove_hotspot_related_rows($API, $users) {
	if (!is_array($users) || count($users) === 0) {
		return;
	}

	$scriptRowsByName = mikhmon_get_rows_by_name($API->comm('/system/script/print', array(
		'.proplist' => '.id,name',
	)));
	$schedulerRowsByName = mikhmon_get_rows_by_name($API->comm('/system/scheduler/print', array(
		'.proplist' => '.id,name',
	)));
	$handledNames = array();

	foreach ($users as $index => $user) {
		if ($index > 0 && $index % 25 === 0) {
			if (!mikhmon_reconnect_api($API)) {
				break;
			}
		}

		$userName = isset($user['name']) ? $user['name'] : '';
		if ($userName != '' && !isset($handledNames[$userName])) {
			if (isset($scriptRowsByName[$userName])) {
				foreach ($scriptRowsByName[$userName] as $scriptRow) {
					if (isset($scriptRow['.id'])) {
						$API->comm('/system/script/remove', array(
							'.id' => $scriptRow['.id'],
						));
					}
				}
			}

			if (isset($schedulerRowsByName[$userName])) {
				foreach ($schedulerRowsByName[$userName] as $schedulerRow) {
					if (isset($schedulerRow['.id'])) {
						$API->comm('/system/scheduler/remove', array(
							'.id' => $schedulerRow['.id'],
						));
					}
				}
			}

			$handledNames[$userName] = true;
		}

		if (isset($user['.id']) && $user['.id'] != '') {
			$API->comm('/ip/hotspot/user/remove', array(
				'.id' => $user['.id'],
			));
		}
	}
}

if ($removehotspotusers != "") {
	$uids = explode("~", $removehotspotusers);
	$allUsers = $API->comm('/ip/hotspot/user/print', array(
		'.proplist' => '.id,name',
	));
	$usersById = array();
	foreach ($allUsers as $userRow) {
		if (isset($userRow['.id']) && $userRow['.id'] != '') {
			$usersById[$userRow['.id']] = $userRow;
		}
	}

	$usersToRemove = array();
	$nuids = count($uids);
	for ($i = 0; $i < $nuids; $i++) {
		if (isset($usersById[$uids[$i]])) {
			$usersToRemove[] = $usersById[$uids[$i]];
		}
	}

	mikhmon_remove_hotspot_related_rows($API, $usersToRemove);

	if ($_SESSION['ubp'] != "") {
		echo "<script>window.location='./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session . "'</script>";
	} elseif ($_SESSION['uba'] != "") {
		echo "<script>window.location='./?hotspot=users&agent=" . $_SESSION['uba'] . "&session=" . $session . "'</script>";
	} elseif ($_SESSION['ubc'] != "") {
		echo "<script>window.location='./?hotspot=users&comment=" . $_SESSION['ubc'] . "&session=" . $session . "'</script>";
	} else {
		echo "<script>window.location='./?hotspot=users&profile=all&session=" . $session . "'</script>";
	}


} else {
	$getuname = $API->comm("/ip/hotspot/user/print", array(
		".proplist" => ".id,name",
		"?.id" => "$removehotspotuser",
	));
	if (is_array($getuname) && isset($getuname[0])) {
		mikhmon_remove_hotspot_related_rows($API, array($getuname[0]));
	}


	if ($_SESSION['ubp'] != "") {
		echo "<script>window.location='./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session . "'</script>";
	} elseif ($_SESSION['uba'] != "") {
		echo "<script>window.location='./?hotspot=users&agent=" . $_SESSION['uba'] . "&session=" . $session . "'</script>";
	} elseif ($_SESSION['ubc'] != "") {
		echo "<script>window.location='./?hotspot=users&comment=" . $_SESSION['ubc'] . "&session=" . $session . "'</script>";
	} else {
		echo "<script>window.location='./?hotspot=users&profile=all&session=" . $session . "'</script>";
	}
}
?>
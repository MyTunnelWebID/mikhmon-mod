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

if (!function_exists('mikhmon_get_rows_by_name')) {
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
}

if (!function_exists('mikhmon_remove_hotspot_related_rows')) {
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
}

if (!function_exists('mikhmon_reconnect_api')) {
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
}

$targetComment = trim($removehotspotuserbycomment);
$targetCommentBase = mikhmon_strip_agent_marker($targetComment);
$targetAgentCode = mikhmon_parse_agent_marker($targetComment);

$allUsers = $API->comm("/ip/hotspot/user/print", array(
  ".proplist" => ".id,name,profile,comment,uptime",
  "?uptime" => "00:00:00"
));
$getuser = array();

foreach ($allUsers as $userdetails) {
  $userComment = isset($userdetails['comment']) ? trim($userdetails['comment']) : '';
  if ($targetAgentCode != '') {
    if ($userComment === $targetComment) {
      $getuser[] = $userdetails;
    }
    continue;
  }

  if (mikhmon_strip_agent_marker($userComment) === $targetCommentBase) {
    $getuser[] = $userdetails;
  }
}

$TotalReg = count($getuser);

$_SESSION['ubp'] = $TotalReg > 0 && isset($getuser[0]['profile']) ? $getuser[0]['profile'] : '';
$_SESSION['ubc'] = "";

mikhmon_remove_hotspot_related_rows($API, $getuser);
if ($_SESSION['ubp'] != "") {
  echo "<script>window.location='./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session . "'</script>";
} else {
  echo "<script>window.location='./?hotspot=users&profile=all&session=" . $session . "'</script>";
}

?>
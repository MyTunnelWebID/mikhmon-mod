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
$targetComment = trim($removehotspotuserbycomment);
$targetCommentBase = mikhmon_strip_agent_marker($targetComment);
$targetAgentCode = mikhmon_parse_agent_marker($targetComment);

$allUsers = $API->comm("/ip/hotspot/user/print", array(
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

for ($i = 0; $i < $TotalReg; $i++) {
  $userdetails = $getuser[$i];
  $uid = isset($userdetails['.id']) ? $userdetails['.id'] : '';
  $name = isset($userdetails['name']) ? $userdetails['name'] : '';

  if ($name != '') {
    $getscr = $API->comm("/system/script/print", array(
      "?name" => "$name",
    ));
    $TotalScr = count($getscr);
    for ($j = 0; $j < $TotalScr; $j++) {
      if (isset($getscr[$j]['.id'])) {
        $API->comm("/system/script/remove", array(
          ".id" => $getscr[$j]['.id'],
        ));
      }
    }

    $getsch = $API->comm("/system/scheduler/print", array(
      "?name" => "$name",
    ));
    $TotalSch = count($getsch);
    for ($j = 0; $j < $TotalSch; $j++) {
      if (isset($getsch[$j]['.id'])) {
        $API->comm("/system/scheduler/remove", array(
          ".id" => $getsch[$j]['.id'],
        ));
      }
    }
  }

  if ($uid != '') {
    $API->comm("/ip/hotspot/user/remove", array(
      ".id" => "$uid",
    ));
  }
}
if ($_SESSION['ubp'] != "") {
  echo "<script>window.location='./?hotspot=users&profile=" . $_SESSION['ubp'] . "&session=" . $session . "'</script>";
} else {
  echo "<script>window.location='./?hotspot=users&profile=all&session=" . $session . "'</script>";
}

?>
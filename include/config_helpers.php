<?php
if (!isset($agentreseller) || !is_array($agentreseller)) {
  $agentreseller = array();
}

function mikhmon_get_router_keys($data) {
  $routerKeys = array();

  if (!is_array($data)) {
    return $routerKeys;
  }

  foreach ($data as $key => $routerData) {
    if ($key == 'mikhmon' || !is_array($routerData) || !isset($routerData[1])) {
      continue;
    }

    if (strpos($routerData[1], $key . '!') === 0) {
      $routerKeys[] = $key;
    }
  }

  return $routerKeys;
}

function mikhmon_get_agent_reseller_bucket($agentreseller, $session = '') {
  if (!is_array($agentreseller)) {
    return array();
  }

  if ($session != '' && isset($agentreseller[$session]) && is_array($agentreseller[$session])) {
    return $agentreseller[$session];
  }

  $legacyBucket = array();
  foreach ($agentreseller as $agentKey => $agentItem) {
    if (is_array($agentItem) && isset($agentItem[1])) {
      $legacyBucket[$agentKey] = $agentItem;
    }
  }

  return $legacyBucket;
}

function mikhmon_get_agent_reseller_keys($agentreseller, $session = '') {
  return array_keys(mikhmon_get_agent_reseller_bucket($agentreseller, $session));
}

function mikhmon_get_agent_reseller_item($agentreseller, $sessionOrAgentKey, $agentKey = '') {
  $emptyItem = array(
    'session' => '',
    'code' => '',
    'name' => '',
    'contact' => '',
    'address' => '',
    'commission' => '',
    'status' => 'enable',
  );

  if ($agentKey === '') {
    $session = '';
    $agentKey = $sessionOrAgentKey;
  } else {
    $session = $sessionOrAgentKey;
  }

  $bucket = mikhmon_get_agent_reseller_bucket($agentreseller, $session);
  if (!isset($bucket[$agentKey]) || !is_array($bucket[$agentKey])) {
    return $emptyItem;
  }

  $agentItem = $bucket[$agentKey];
  $isSessionScoped = isset($agentItem[7]) || ($session != '' && isset($agentItem[1]) && $agentItem[1] == $session && isset($agentItem[2]));

  if ($isSessionScoped) {
    return array(
      'session' => isset($agentItem[1]) ? $agentItem[1] : $session,
      'code' => isset($agentItem[2]) ? $agentItem[2] : $agentKey,
      'name' => isset($agentItem[3]) ? $agentItem[3] : '',
      'contact' => isset($agentItem[4]) ? $agentItem[4] : '',
      'address' => isset($agentItem[5]) ? $agentItem[5] : '',
      'commission' => isset($agentItem[6]) ? $agentItem[6] : '',
      'status' => isset($agentItem[7]) && trim($agentItem[7]) != '' ? $agentItem[7] : 'enable',
    );
  }

  return array(
    'session' => $session,
    'code' => isset($agentItem[1]) ? $agentItem[1] : $agentKey,
    'name' => isset($agentItem[2]) ? $agentItem[2] : '',
    'contact' => isset($agentItem[3]) ? $agentItem[3] : '',
    'address' => isset($agentItem[4]) ? $agentItem[4] : '',
    'commission' => isset($agentItem[5]) ? $agentItem[5] : '',
    'status' => isset($agentItem[7]) && trim($agentItem[7]) != '' ? $agentItem[7] : 'enable',
  );
}

function mikhmon_build_agent_reseller_config_line($session, $agentItem) {
  $sessionName = mikhmon_config_escape($session);
  $code = mikhmon_config_escape($agentItem['code']);
  $name = mikhmon_config_escape($agentItem['name']);
  $contact = mikhmon_config_escape($agentItem['contact']);
  $address = mikhmon_config_escape($agentItem['address']);
  $commission = mikhmon_config_escape($agentItem['commission']);
  $status = mikhmon_config_escape($agentItem['status']);

  return "\n" . '$agentreseller[\'' . $sessionName . '\'][\'' . $code . '\'] = array ('
    . "'1'=>'" . $sessionName . "',"
    . "'2'=>'" . $code . "',"
    . "'3'=>'" . $name . "',"
    . "'4'=>'" . $contact . "',"
    . "'5'=>'" . $address . "',"
    . "'6'=>'" . $commission . "',"
    . "'7'=>'" . $status . "');";
}

function mikhmon_get_enabled_agent_resellers($agentreseller, $session) {
  $enabledItems = array();
  foreach (mikhmon_get_agent_reseller_keys($agentreseller, $session) as $agentKey) {
    $agentItem = mikhmon_get_agent_reseller_item($agentreseller, $session, $agentKey);
    if ($agentItem['status'] != 'disable') {
      $enabledItems[$agentKey] = $agentItem;
    }
  }

  return $enabledItems;
}

function mikhmon_build_agent_marker($agentCode, $commissionRule = '') {
  if ($agentCode == '') {
    return '';
  }

  $marker = '[AR:' . mikhmon_sanitize_key($agentCode);
  $commissionRule = trim((string) $commissionRule);
  if ($commissionRule != '') {
    $marker .= '|CR:' . rawurlencode($commissionRule);
  }

  return $marker . ']';
}

function mikhmon_parse_agent_marker_data($comment) {
  $data = array(
    'code' => '',
    'commission_rule' => '',
  );

  if (preg_match('/\[AR:([a-z0-9_-]+)(?:\|CR:([^\]]*))?\]/i', $comment, $matches)) {
    $data['code'] = strtolower($matches[1]);
    if (isset($matches[2])) {
      $data['commission_rule'] = rawurldecode($matches[2]);
    }
  }

  return $data;
}

function mikhmon_append_agent_marker($comment, $agentCode, $commissionRule = '') {
  $cleanComment = mikhmon_strip_agent_marker($comment);
  $agentMarker = mikhmon_build_agent_marker($agentCode, $commissionRule);

  if ($agentMarker == '') {
    return $cleanComment;
  }

  return trim($cleanComment . ' ' . $agentMarker);
}

function mikhmon_parse_agent_marker($comment) {
  $markerData = mikhmon_parse_agent_marker_data($comment);
  return $markerData['code'];
}

function mikhmon_parse_agent_commission_rule($comment) {
  $markerData = mikhmon_parse_agent_marker_data($comment);
  return trim($markerData['commission_rule']);
}

function mikhmon_strip_agent_marker($comment) {
  return trim(preg_replace('/\s*\[AR:[^\]]+\]/i', '', $comment));
}

function mikhmon_strip_report_comment_prefix($comment) {
  $comment = trim((string) $comment);
  if ($comment == '') {
    return '';
  }

  $comment = preg_replace('/^\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2}\s*/', '', $comment);
  $comment = preg_replace('/^[A-Za-z]{3}\/\d{2}\/\d{4}\s+\d{2}:\d{2}:\d{2}\s*/', '', $comment);

  return trim($comment);
}

function mikhmon_get_report_comment_label($comment) {
  return mikhmon_strip_report_comment_prefix(mikhmon_strip_agent_marker($comment));
}

function mikhmon_get_report_comment_code($comment) {
  $commentLabel = mikhmon_get_report_comment_label($comment);
  if ($commentLabel == '') {
    return '';
  }

  if (preg_match('/^(vc|up)-/i', $commentLabel)) {
    return $commentLabel;
  }

  return '';
}

function mikhmon_parse_report_date_parts($dateValue) {
  $dateValue = trim((string) $dateValue);
  $monthMap = array(
    '01' => 'jan',
    '02' => 'feb',
    '03' => 'mar',
    '04' => 'apr',
    '05' => 'may',
    '06' => 'jun',
    '07' => 'jul',
    '08' => 'aug',
    '09' => 'sep',
    '10' => 'oct',
    '11' => 'nov',
    '12' => 'dec',
  );

  if (preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $dateValue, $matches)) {
    return array(
      'year' => $matches[1],
      'month_num' => $matches[2],
      'month_short' => $monthMap[$matches[2]],
      'day' => $matches[3],
    );
  }

  if (preg_match('/^([a-z]{3})\/(\d{2})\/(\d{4})$/i', $dateValue, $matches)) {
    $monthShort = strtolower($matches[1]);
    $monthNum = array_search($monthShort, $monthMap, true);
    return array(
      'year' => $matches[3],
      'month_num' => $monthNum !== false ? $monthNum : '',
      'month_short' => $monthShort,
      'day' => $matches[2],
    );
  }

  return array(
    'year' => '',
    'month_num' => '',
    'month_short' => '',
    'day' => '',
  );
}

function mikhmon_get_report_row_date($scriptRow) {
  if (isset($scriptRow['name'])) {
    $parts = explode('-|-', $scriptRow['name']);
    if (isset($parts[0]) && trim($parts[0]) != '') {
      return trim($parts[0]);
    }
  }

  if (isset($scriptRow['source'])) {
    return trim($scriptRow['source']);
  }

  return '';
}

function mikhmon_match_report_period($dateValue, $idhr = '', $idbl = '') {
  $dateParts = mikhmon_parse_report_date_parts($dateValue);
  if ($dateParts['year'] == '') {
    return $idhr == '' && $idbl == '';
  }

  if ($idhr != '') {
    $filterParts = explode('/', $idhr);
    if (count($filterParts) < 3) {
      return false;
    }

    return strtolower($filterParts[0]) == $dateParts['month_short']
      && str_pad($filterParts[1], 2, '0', STR_PAD_LEFT) == $dateParts['day']
      && $filterParts[2] == $dateParts['year'];
  }

  if ($idbl != '') {
    return strtolower(substr($idbl, 0, 3)) == $dateParts['month_short']
      && substr($idbl, 3) == $dateParts['year'];
  }

  return true;
}

function mikhmon_comment_has_agent($comment, $agentCode) {
  if ($agentCode == '') {
    return false;
  }

  return mikhmon_parse_agent_marker($comment) == mikhmon_sanitize_key($agentCode);
}

function mikhmon_get_agent_reseller_label($agentreseller, $session, $agentCode) {
  $agentKey = mikhmon_sanitize_key($agentCode);
  if ($agentKey == '') {
    return '';
  }

  $agentItem = mikhmon_get_agent_reseller_item($agentreseller, $session, $agentKey);
  if ($agentItem['code'] == '') {
    return $agentKey;
  }

  if ($agentItem['name'] == '') {
    return $agentItem['code'];
  }

  return $agentItem['code'] . ' - ' . $agentItem['name'];
}

function mikhmon_get_agent_hotspot_users($users, $agentCode) {
  $relatedUsers = array();
  $agentKey = mikhmon_sanitize_key($agentCode);

  if ($agentKey == '' || !is_array($users)) {
    return $relatedUsers;
  }

  foreach ($users as $userItem) {
    $comment = isset($userItem['comment']) ? $userItem['comment'] : '';
    if (mikhmon_comment_has_agent($comment, $agentKey)) {
      $relatedUsers[] = $userItem;
    }
  }

  return $relatedUsers;
}

function mikhmon_count_agent_hotspot_users($users, $agentCode) {
  return count(mikhmon_get_agent_hotspot_users($users, $agentCode));
}

function mikhmon_get_agent_hotspot_user_stats($users, $agentCode) {
  $stats = array(
    'total' => 0,
    'used' => 0,
    'remaining' => 0,
  );

  $relatedUsers = mikhmon_get_agent_hotspot_users($users, $agentCode);
  $stats['total'] = count($relatedUsers);

  foreach ($relatedUsers as $userItem) {
    if (mikhmon_is_hotspot_user_used($userItem)) {
      $stats['used']++;
    }
  }

  $stats['remaining'] = $stats['total'] - $stats['used'];

  return $stats;
}

function mikhmon_is_hotspot_user_used($userItem) {
  $uptime = isset($userItem['uptime']) ? trim($userItem['uptime']) : '';
  $bytesIn = isset($userItem['bytes-in']) ? (int) $userItem['bytes-in'] : 0;
  $bytesOut = isset($userItem['bytes-out']) ? (int) $userItem['bytes-out'] : 0;

  if (in_array($uptime, array('', '0', '0s', '00:00:00'))) {
    $uptime = '';
  }

  return $uptime != '' || $bytesIn > 0 || $bytesOut > 0;
}

function mikhmon_find_agent_marker_by_username($scriptRows, $username) {
  if (!is_array($scriptRows) || trim($username) == '') {
    return '';
  }

  foreach ($scriptRows as $scriptRow) {
    $scriptName = isset($scriptRow['name']) ? $scriptRow['name'] : '';
    if ($scriptName == '') {
      continue;
    }

    $parts = explode('-|-', $scriptName);
    if (!isset($parts[2]) || $parts[2] != $username) {
      continue;
    }

    $commentRaw = isset($parts[8]) ? $parts[8] : '';
    $agentCode = mikhmon_parse_agent_marker($commentRaw);
    if ($agentCode != '') {
      return $agentCode;
    }
  }

  return '';
}

function mikhmon_build_agent_marker_index($scriptRows) {
  $markerIndex = array();

  if (!is_array($scriptRows)) {
    return $markerIndex;
  }

  foreach ($scriptRows as $scriptRow) {
    $scriptName = isset($scriptRow['name']) ? $scriptRow['name'] : '';
    if ($scriptName == '') {
      continue;
    }

    $parts = explode('-|-', $scriptName);
    if (!isset($parts[2])) {
      continue;
    }

    $username = trim($parts[2]);
    $commentRaw = isset($parts[8]) ? $parts[8] : '';
    $agentCode = mikhmon_parse_agent_marker($commentRaw);
    if ($username != '' && $agentCode != '') {
      $markerIndex[$username] = $agentCode;
    }
  }

  return $markerIndex;
}

function mikhmon_parse_number_string($value) {
  $value = trim((string) $value);
  if ($value == '') {
    return 0;
  }

  $value = preg_replace('/[^0-9,.-]+/', '', $value);
  if ($value == '' || $value == '-' || $value == ',' || $value == '.') {
    return 0;
  }

  $hasComma = strpos($value, ',') !== false;
  $hasDot = strpos($value, '.') !== false;

  if ($hasComma && $hasDot) {
    if (strrpos($value, ',') > strrpos($value, '.')) {
      $value = str_replace('.', '', $value);
      $value = str_replace(',', '.', $value);
    } else {
      $value = str_replace(',', '', $value);
    }
  } elseif ($hasComma) {
    $value = str_replace('.', '', $value);
    $value = str_replace(',', '.', $value);
  }

  return (float) $value;
}

function mikhmon_calculate_agent_commission($commissionRule, $price) {
  $price = (float) $price;
  $commissionRule = trim((string) $commissionRule);
  $result = array(
    'rule' => $commissionRule,
    'type' => 'none',
    'value' => 0,
    'amount' => 0,
  );

  if ($commissionRule == '' || $price <= 0) {
    return $result;
  }

  if (strpos($commissionRule, '%') !== false) {
    $percentValue = mikhmon_parse_number_string(str_replace('%', '', $commissionRule));
    $result['type'] = 'percent';
    $result['value'] = $percentValue;
    $result['amount'] = ($price * $percentValue) / 100;
    return $result;
  }

  $fixedValue = mikhmon_parse_number_string($commissionRule);
  $result['type'] = 'fixed';
  $result['value'] = $fixedValue;
  $result['amount'] = $fixedValue;

  return $result;
}

function mikhmon_config_escape($value) {
  return str_replace(array('\\', "'", "\r", "\n"), array('\\\\', "\\'", '', ' '), trim($value));
}

function mikhmon_sanitize_key($value) {
  $value = strtolower(trim($value));
  $value = preg_replace('/[^a-z0-9_-]+/', '-', $value);
  return trim($value, '-');
}

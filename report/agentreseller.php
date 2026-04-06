<?php
session_start();
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
  $idhr = $_GET['idhr'];
  $idbl = $_GET['idbl'];
  $search = trim($_GET['prefix']);

  $gettimezone = $API->comm("/system/clock/print");
  $timezone = $gettimezone[0]['time-zone-name'];
  date_default_timezone_set($timezone);

  $allReportRows = $API->comm("/system/script/print", array(
    "?comment" => "mikhmon",
  ));
  if (!is_array($allReportRows)) {
    $allReportRows = array();
  }

  $getData = array();
  foreach ($allReportRows as $row) {
    if (mikhmon_match_report_period(mikhmon_get_report_row_date($row), $idhr, $idbl)) {
      $getData[] = $row;
    }
  }

  if (strlen($idhr) > 0) {
    $filedownload = $idhr;
  } elseif (strlen($idbl) > 0) {
    $filedownload = $idbl;
  } else {
    $filedownload = "all-agent";
  }

  $agentRows = array();
  $rekapAgent = array();
  $rekapProfile = array();
  $totalAmount = 0;
  $totalCommission = 0;

  foreach ($getData as $row) {
    $getname = explode("-|-", $row['name']);
    $commentRaw = isset($getname[8]) ? trim($getname[8]) : '';
    $agentCode = mikhmon_parse_agent_marker($commentRaw);
    if ($agentCode == '') {
      continue;
    }

    $agentItem = mikhmon_get_agent_reseller_item($agentreseller, $session, $agentCode);
    $agentLabel = $agentCode;
    if ($agentItem['name'] != '') {
      $agentLabel = $agentItem['code'] . ' - ' . $agentItem['name'];
    }

    $commentDisplay = mikhmon_get_report_comment_label($commentRaw);
    $username = isset($getname[2]) ? $getname[2] : '';
    $profile = isset($getname[7]) ? $getname[7] : '-';
    $price = floatval(isset($getname[3]) ? $getname[3] : 0);
    $commissionRuleSnapshot = mikhmon_parse_agent_commission_rule($commentRaw);
    if ($commissionRuleSnapshot == '') {
      $commissionRuleSnapshot = $agentItem['commission'];
    }
    $commissionData = mikhmon_calculate_agent_commission($commissionRuleSnapshot, $price);
    $searchHaystack = strtolower($agentLabel . ' ' . $username . ' ' . $profile . ' ' . $commentDisplay);
    if ($search != '' && strpos($searchHaystack, strtolower($search)) === false) {
      continue;
    }

    $agentRows[] = array(
      'date' => isset($getname[0]) ? $getname[0] : '',
      'time' => isset($getname[1]) ? $getname[1] : '',
      'user' => $username,
      'agent' => $agentLabel,
      'profile' => $profile,
      'comment' => $commentDisplay,
      'commission_rule' => $commissionData['rule'],
      'commission_amount' => $commissionData['amount'],
      'price' => $price,
    );

    if (!isset($rekapAgent[$agentLabel])) {
      $rekapAgent[$agentLabel] = array('price' => 0, 'commission' => 0);
    }
    if (!isset($rekapProfile[$profile])) {
      $rekapProfile[$profile] = array('price' => 0, 'commission' => 0);
    }
    $rekapAgent[$agentLabel]['price'] += $price;
    $rekapAgent[$agentLabel]['commission'] += $commissionData['amount'];
    $rekapProfile[$profile]['price'] += $price;
    $rekapProfile[$profile]['commission'] += $commissionData['amount'];
    $totalAmount += $price;
    $totalCommission += $commissionData['amount'];
  }

  uasort($rekapAgent, function ($left, $right) {
    if ($left['price'] == $right['price']) {
      return 0;
    }

    return ($left['price'] < $right['price']) ? 1 : -1;
  });
  uasort($rekapProfile, function ($left, $right) {
    if ($left['price'] == $right['price']) {
      return 0;
    }

    return ($left['price'] < $right['price']) ? 1 : -1;
  });

  if ($currency == in_array($currency, $cekindo['indo'])) {
    $formatAmount = function ($value) use ($currency) {
      return $currency . ' ' . number_format((float)$value, 0, ',', '.');
    };
  } else {
    $formatAmount = function ($value) use ($currency) {
      return $currency . ' ' . number_format((float)$value, 2, '.', ',');
    };
  }
}
?>
<script>
var agentReportRows = <?= json_encode($agentRows, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
var agentReportCurrency = <?= json_encode($currency); ?>;
var agentReportIsIndo = <?= ($currency == in_array($currency, $cekindo['indo'])) ? 'true' : 'false'; ?>;

function escapeHtml(value) {
  return String(value)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/\"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

function formatAgentAmount(value) {
  var amount = Number(value || 0);
  if (agentReportIsIndo) {
    return agentReportCurrency + ' ' + amount.toLocaleString('id-ID', {
      minimumFractionDigits: 0,
      maximumFractionDigits: 0
    });
  }

  return agentReportCurrency + ' ' + amount.toLocaleString('en-US', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

function sortRecapEntries(entries) {
  return entries.sort(function(left, right) {
    if (left.totalPrice === right.totalPrice) {
      return left.label.localeCompare(right.label);
    }

    return right.totalPrice - left.totalPrice;
  });
}

function renderAgentReportRows(filteredRows) {
  var tbody = document.getElementById('agentReportBody');
  if (!tbody) {
    return;
  }

  if (!filteredRows.length) {
    tbody.innerHTML = '<tr><td colspan="8" class="text-center">Belum ada data penjualan voucher berdasarkan Agent Reseller.</td></tr>';
    return;
  }

  var html = [];
  for (var index = 0; index < filteredRows.length; index++) {
    var row = filteredRows[index];
    html.push('<tr>');
    html.push('<td>' + (index + 1) + '</td>');
    html.push('<td>' + escapeHtml(row.date) + '</td>');
    html.push('<td>' + escapeHtml(row.time) + '</td>');
    html.push('<td>' + escapeHtml(row.user) + '</td>');
    html.push('<td>' + escapeHtml(row.agent) + '</td>');
    html.push('<td>' + escapeHtml(row.profile) + '</td>');
    html.push('<td style="text-align:right;">' + escapeHtml(formatAgentAmount(row.price)) + '</td>');
    html.push('<td style="text-align:right;" title="' + escapeHtml(row.commission_rule || '') + '">' + escapeHtml(formatAgentAmount(row.commission_amount)) + '</td>');
    html.push('</tr>');
  }

  tbody.innerHTML = html.join('');
}

function renderAgentRecapTable(tbodyId, entries, emptyLabel) {
  var tbody = document.getElementById(tbodyId);
  if (!tbody) {
    return;
  }

  var html = [];
  if (!entries.length) {
    html.push('<tr><td colspan="4" class="text-center">' + escapeHtml(emptyLabel) + '</td></tr>');
  } else {
    for (var index = 0; index < entries.length; index++) {
      var entry = entries[index];
      html.push('<tr>');
      html.push('<td>' + (index + 1) + '</td>');
      html.push('<td>' + escapeHtml(entry.label) + '</td>');
      html.push('<td style="text-align:right;">' + escapeHtml(formatAgentAmount(entry.totalPrice)) + '</td>');
      html.push('<td style="text-align:right;">' + escapeHtml(formatAgentAmount(entry.totalCommission)) + '</td>');
      html.push('</tr>');
    }
  }

  tbody.innerHTML = html.join('');
}

function renderAgentReportSummary(filteredRows) {
  var totalAmount = 0;
  var totalCommission = 0;
  var recapAgentMap = {};
  var recapProfileMap = {};

  for (var index = 0; index < filteredRows.length; index++) {
    var row = filteredRows[index];
    var price = Number(row.price || 0);
    var commission = Number(row.commission_amount || 0);
    totalAmount += price;
    totalCommission += commission;

    if (!recapAgentMap[row.agent]) {
      recapAgentMap[row.agent] = { label: row.agent, totalPrice: 0, totalCommission: 0 };
    }
    recapAgentMap[row.agent].totalPrice += price;
    recapAgentMap[row.agent].totalCommission += commission;

    if (!recapProfileMap[row.profile]) {
      recapProfileMap[row.profile] = { label: row.profile, totalPrice: 0, totalCommission: 0 };
    }
    recapProfileMap[row.profile].totalPrice += price;
    recapProfileMap[row.profile].totalCommission += commission;
  }

  document.getElementById('agentReportTotalAmount').innerText = formatAgentAmount(totalAmount);
  document.getElementById('agentReportTotalCommission').innerText = formatAgentAmount(totalCommission);
  document.getElementById('agentRecapTotalAmount').innerText = formatAgentAmount(totalAmount);
  document.getElementById('agentRecapTotalCommission').innerText = formatAgentAmount(totalCommission);
  document.getElementById('profileRecapTotalAmount').innerText = formatAgentAmount(totalAmount);
  document.getElementById('profileRecapTotalCommission').innerText = formatAgentAmount(totalCommission);

  var recapAgentEntries = sortRecapEntries(Object.keys(recapAgentMap).map(function(key) {
    return recapAgentMap[key];
  }));
  var recapProfileEntries = sortRecapEntries(Object.keys(recapProfileMap).map(function(key) {
    return recapProfileMap[key];
  }));

  renderAgentRecapTable('agentRecapBody', recapAgentEntries, 'Belum ada data Agent Reseller.');
  renderAgentRecapTable('profileRecapBody', recapProfileEntries, 'Belum ada data profile.');
}

function applyAgentReportSearch() {
  var filterInput = document.getElementById('filterAgentTable');
  var keyword = filterInput ? filterInput.value.trim().toLowerCase() : '';
  var filteredRows = agentReportRows.filter(function(row) {
    if (keyword === '') {
      return true;
    }

    var haystack = [row.date, row.time, row.user, row.agent, row.profile, row.comment, row.commission_rule]
      .join(' ')
      .toLowerCase();

    return haystack.indexOf(keyword) !== -1;
  });

  renderAgentReportRows(filteredRows);
  renderAgentReportSummary(filteredRows);
}

function downloadCSV(csv, filename) {
  var csvFile = new Blob([csv], {type: "text/csv"});
  var downloadLink = document.createElement("a");
  downloadLink.download = filename;
  downloadLink.href = window.URL.createObjectURL(csvFile);
  downloadLink.style.display = "none";
  document.body.appendChild(downloadLink);
  downloadLink.click();
}

function exportTableToCSV(filename) {
  var csv = [];
  var rows = document.querySelectorAll("#agentReportTable tr");
  for (var i = 0; i < rows.length; i++) {
    var row = [];
    var cols = rows[i].querySelectorAll("td, th");
    for (var j = 0; j < cols.length; j++) {
      row.push(cols[j].innerText);
    }
    csv.push(row.join(","));
  }
  downloadCSV(csv.join("\n"), filename);
}

function filterAgentReport(){
  var D = document.getElementById('D').value;
  var M = document.getElementById('M').value;
  var Y = document.getElementById('Y').value;
  var X = encodeURIComponent(document.getElementById('filterAgentTable').value);

  if(D !== ""){
    window.location='./?report=agent-reseller&idhr='+M+'/'+D+'/'+Y+'&prefix='+X+'&session=<?= $session; ?>';
  }else{
    window.location='./?report=agent-reseller&idbl='+M+Y+'&prefix='+X+'&session=<?= $session; ?>';
  }
}

document.addEventListener('DOMContentLoaded', function() {
  var filterInput = document.getElementById('filterAgentTable');
  if (filterInput) {
    filterInput.addEventListener('input', applyAgentReportSearch);
    filterInput.addEventListener('keydown', function(event) {
      if (event.key === 'Enter') {
        event.preventDefault();
        applyAgentReportSearch();
      }
    });
  }

  applyAgentReportSearch();
});
</script>

<div class="row">
  <div class="col-8">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-bar-chart"></i> <?= $_agent_report ?> <?= ucfirst($idhr) . ucfirst(substr($idbl,0,3).' '.substr($idbl,3,5)); ?></h3>
      </div>
      <div class="card-body">
        <div style="padding-bottom: 5px; padding-top: 5px;">
          <input id="filterAgentTable" type="text" class="form-control" style="float:left; margin-top: 6px; max-width: 180px;" placeholder="<?= $_search ?>" value="<?= htmlspecialchars($search); ?>">&nbsp;
          <button class="btn bg-primary" onclick="exportTableToCSV('report-agent-reseller-<?= $filedownload; ?>.csv')"><i class="fa fa-download"></i> CSV</button>
          <button class="btn bg-primary" onclick="location.href='./?report=agent-reseller&session=<?= $session; ?>';"><i class="fa fa-search"></i> <?= $_all ?></button>
        </div>
        <div class="input-group mr-b-10">
          <div class="input-group-1 col-box-2">
            <select style="padding:5px;" class="group-item group-item-l" id="D">
              <?php
              $day = explode("/", $idhr)[1];
              if ($day != "") {
                echo "<option value='" . $day . "'>" . $day . "</option>";
              }
              echo "<option value=''>Day</option>";
              for ($x = 1; $x <= 31; $x++) {
                $v = strlen($x) == 1 ? '0' . $x : $x;
                echo "<option value='" . $v . "'>" . $v . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="input-group-2 col-box-4">
            <select style="padding:5px;" class="group-item group-item-md" id="M">
              <?php
              $idbls = array(1 => "jan", "feb", "mar", "apr", "may", "jun", "jul", "aug", "sep", "oct", "nov", "dec");
              $idblf = array(1 => "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December");
              $month = explode("/", $idhr)[0];
              $month1 = substr($idbl, 0, 3);
              if ($month != "") {
                $fm = array_search($month, $idbls);
                echo "<option value='" . $month . "'>" . $idblf[$fm] . "</option>";
              } elseif ($month1 != "") {
                $fm = array_search($month1, $idbls);
                echo "<option value='" . $month1 . "'>" . $idblf[$fm] . "</option>";
              } else {
                echo "<option value='" . $idbls[date("n")] . "'>" . $idblf[date("n")] . "</option>";
              }
              for ($x = 1; $x <= 12; $x++) {
                echo "<option value='" . $idbls[$x] . "'>" . $idblf[$x] . "</option>";
              }
              ?>
            </select>
          </div>
          <div class="input-group-2 col-box-3">
            <select style="padding:5px;" class="group-item group-item-md" id="Y">
              <?php
              $year = explode("/", $idhr)[2];
              $year1 = substr($idbl, 3, 4);
              if ($year != "") {
                echo "<option>" . $year . "</option>";
              } elseif ($year1 != "") {
                echo "<option>" . $year1 . "</option>";
              }
              echo "<option>" . date("Y") . "</option>";
              for ($Y = 2018; $Y <= date("Y"); $Y++) {
                if ($Y != date("Y")) {
                  echo "<option value='" . $Y . "'>" . $Y . "</option>";
                }
              }
              ?>
            </select>
          </div>
          <div class="input-group-2 col-box-3">
            <div style="padding:3.5px;" class="group-item group-item-r text-center pointer" onclick="filterAgentReport();"><i class="fa fa-search"></i> Filter</div>
          </div>
        </div>
        <div class="overflow box-bordered" style="max-height: 70vh">
          <table id="agentReportTable" class="table table-bordered table-hover text-nowrap">
            <thead class="thead-light">
              <tr>
                <th colspan="5"><?= $_agent_report ?></th>
                <th style="text-align:right;"><?= $_total ?></th>
                <th style="text-align:right;" id="agentReportTotalAmount"><?= $formatAmount($totalAmount); ?></th>
                <th style="text-align:right;" id="agentReportTotalCommission"><?= $formatAmount($totalCommission); ?></th>
              </tr>
              <tr>
                <th>&#8470;</th>
                <th><?= $_date ?></th>
                <th><?= $_time ?></th>
                <th><?= $_user_name ?></th>
                <th><?= $_agent_reseller ?></th>
                <th><?= $_profile ?></th>
                <th style="text-align:right;"><?= $_price ?></th>
                <th style="text-align:right;"><?= $_commission_amount ?></th>
              </tr>
            </thead>
            <tbody id="agentReportBody">
              <?php
              if (empty($agentRows)) {
                echo '<tr><td colspan="8" class="text-center">Belum ada data penjualan voucher berdasarkan Agent Reseller.</td></tr>';
              } else {
                $no = 1;
                foreach ($agentRows as $row) {
                  echo '<tr>';
                  echo '<td>' . $no . '</td>';
                  echo '<td>' . htmlspecialchars($row['date']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['time']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['user']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['agent']) . '</td>';
                  echo '<td>' . htmlspecialchars($row['profile']) . '</td>';
                  echo '<td style="text-align:right;">' . htmlspecialchars($formatAmount($row['price'])) . '</td>';
                  echo '<td style="text-align:right;" title="' . htmlspecialchars($row['commission_rule']) . '">' . htmlspecialchars($formatAmount($row['commission_amount'])) . '</td>';
                  echo '</tr>';
                  $no++;
                }
              }
              ?>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
  <div class="col-4">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-bar-chart"></i> <?= $_agent_reseller_list ?></h3>
      </div>
      <div class="card-body">
        <div class="overflow box-bordered" style="max-height: 32vh;">
          <table class="table table-bordered table-hover text-nowrap">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th><?= $_agent_reseller ?></th>
                <th style="text-align:right;"><?= $_total ?></th>
                <th style="text-align:right;"><?= $_commission_amount ?></th>
              </tr>
            </thead>
            <tbody id="agentRecapBody">
              <?php
              $no = 1;
              foreach ($rekapAgent as $agentName => $amount) {
                echo '<tr><td>' . $no . '</td><td>' . htmlspecialchars($agentName) . '</td><td style="text-align:right;">' . htmlspecialchars($formatAmount($amount['price'])) . '</td><td style="text-align:right;">' . htmlspecialchars($formatAmount($amount['commission'])) . '</td></tr>';
                $no++;
              }
              ?>
              <tr>
                <td colspan="2" style="text-align:right;"><b><?= $_total ?></b></td>
                <td style="text-align:right;"><b id="agentRecapTotalAmount"><?= htmlspecialchars($formatAmount($totalAmount)); ?></b></td>
                <td style="text-align:right;"><b id="agentRecapTotalCommission"><?= htmlspecialchars($formatAmount($totalCommission)); ?></b></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-pie-chart"></i> Recap Per <?= $_profile ?></h3>
      </div>
      <div class="card-body">
        <div class="overflow box-bordered" style="max-height: 32vh;">
          <table class="table table-bordered table-hover text-nowrap">
            <thead class="thead-light">
              <tr>
                <th>No</th>
                <th><?= $_profile ?></th>
                <th style="text-align:right;"><?= $_total ?></th>
                <th style="text-align:right;"><?= $_commission_amount ?></th>
              </tr>
            </thead>
            <tbody id="profileRecapBody">
              <?php
              $no = 1;
              foreach ($rekapProfile as $profileName => $amount) {
                echo '<tr><td>' . $no . '</td><td>' . htmlspecialchars($profileName) . '</td><td style="text-align:right;">' . htmlspecialchars($formatAmount($amount['price'])) . '</td><td style="text-align:right;">' . htmlspecialchars($formatAmount($amount['commission'])) . '</td></tr>';
                $no++;
              }
              ?>
              <tr>
                <td colspan="2" style="text-align:right;"><b><?= $_total ?></b></td>
                <td style="text-align:right;"><b id="profileRecapTotalAmount"><?= htmlspecialchars($formatAmount($totalAmount)); ?></b></td>
                <td style="text-align:right;"><b id="profileRecapTotalCommission"><?= htmlspecialchars($formatAmount($totalCommission)); ?></b></td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</div>

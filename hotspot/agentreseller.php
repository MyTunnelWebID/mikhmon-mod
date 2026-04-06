<?php
error_reporting(0);

if (!isset($_SESSION["mikhmon"])) {
  header("Location:./admin.php?id=login");
} else {
  $editAgentKey = mikhmon_sanitize_key(isset($_GET['edit-agent']) ? $_GET['edit-agent'] : '');
  $removeAgentKey = mikhmon_sanitize_key(isset($_GET['remove-agent']) ? $_GET['remove-agent'] : '');
  $allHotspotUsers = $API->comm("/ip/hotspot/user/print");

  if ($removeAgentKey != '') {
    $relatedVoucherCount = mikhmon_count_agent_hotspot_users($allHotspotUsers, $removeAgentKey);
    if ($relatedVoucherCount > 0) {
      $agentResellerMessage = '<div class="bg-danger" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> ' . $_messages . ': ' . $_agent_has_related_vouchers . ' (' . $relatedVoucherCount . ')</div>';
    } else {
      $fc = file('./include/config.php');
      $f = fopen('./include/config.php', 'w');
      $q = "'";
      $rem = '$agentreseller[' . $q . $session . $q . '][' . $q . $removeAgentKey . $q . ']';
      foreach ($fc as $line) {
        if (!strstr($line, $rem)) {
          fputs($f, $line);
        }
      }
      fclose($f);
      echo "<script>window.location='./?hotspot=agent-reseller&session=" . $session . "'</script>";
    }
  }

  $editAgentItem = mikhmon_get_agent_reseller_item($agentreseller, $session, $editAgentKey);

  if (isset($_POST['saveagentreseller'])) {
    $agentCode = mikhmon_sanitize_key($_POST['agentcode']);
    $originalAgentCode = mikhmon_sanitize_key($_POST['originalagentcode']);
    $agentName = trim($_POST['agentname']);
    $agentContact = trim($_POST['agentcontact']);
    $agentAddress = trim($_POST['agentaddress']);
    $agentCommission = trim($_POST['agentcommission']);
    $agentStatus = trim($_POST['agentstatus']);
    $isEditAgent = $originalAgentCode != '';
    $sessionAgentBucket = mikhmon_get_agent_reseller_bucket($agentreseller, $session);

    if ($agentCode == '' || $agentName == '') {
      $agentResellerMessage = '<div class="bg-danger" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> ' . $_messages . ': ' . $_name . ' / ' . $_agent_code . ' wajib diisi.</div>';
    } elseif ($agentStatus != 'enable' && $agentStatus != 'disable') {
      $agentResellerMessage = '<div class="bg-danger" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> ' . $_messages . ': ' . $_status . ' tidak valid.</div>';
    } elseif ($isEditAgent && $agentCode != $originalAgentCode && mikhmon_count_agent_hotspot_users($allHotspotUsers, $originalAgentCode) > 0) {
      $agentResellerMessage = '<div class="bg-danger" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> ' . $_messages . ': ' . $_agent_code_locked . '</div>';
    } elseif ((!$isEditAgent && isset($sessionAgentBucket[$agentCode])) || ($isEditAgent && $agentCode != $originalAgentCode && isset($sessionAgentBucket[$agentCode]))) {
      $agentResellerMessage = '<div class="bg-danger" style="padding:8px;border-radius:5px;"><i class="fa fa-ban"></i> ' . $_messages . ': ' . $_agent_reseller . ' sudah ada di router ini.</div>';
    } else {
      $configLine = mikhmon_build_agent_reseller_config_line($session, array(
        'code' => $agentCode,
        'name' => $agentName,
        'contact' => $agentContact,
        'address' => $agentAddress,
        'commission' => $agentCommission,
        'status' => $agentStatus,
      ));

      if ($isEditAgent && isset($sessionAgentBucket[$originalAgentCode])) {
        $fc = file('./include/config.php');
        $f = fopen('./include/config.php', 'w');
        $q = "'";
        $rem = '$agentreseller[' . $q . $session . $q . '][' . $q . $originalAgentCode . $q . ']';
        foreach ($fc as $line) {
          if (!strstr($line, $rem)) {
            fputs($f, $line);
          }
        }
        fclose($f);
      }

      $f = fopen('./include/config.php', 'a');
      fwrite($f, $configLine);
      fclose($f);

      echo "<script>window.location='./?hotspot=agent-reseller&session=" . $session . "'</script>";
    }
  }
}

$agentResellerList = mikhmon_get_agent_reseller_keys($agentreseller, $session);
$agentRelationStats = array();
foreach ($agentResellerList as $agentKey) {
  $agentRelationStats[$agentKey] = mikhmon_get_agent_hotspot_user_stats($allHotspotUsers, $agentKey);
}
$formAgent = !empty($editAgentItem['code']) ? $editAgentItem : array(
  'session' => $session,
  'code' => '',
  'name' => '',
  'contact' => '',
  'address' => '',
  'commission' => '',
  'status' => 'enable',
);
$isEditMode = !empty($formAgent['code']);
?>

<style>
  .agent-reseller-tools {
    display: flex;
    justify-content: flex-start;
    align-items: center;
    padding: 5px 0 10px 0;
  }

  .agent-reseller-tools #filterTable {
    margin-left: 0;
    margin-right: auto;
    max-width: 220px;
  }

  .agent-reseller-table-wrap {
    /* min-height: 430px; */
    max-height: 70vh;
    overflow-y: auto;
  }

  .agent-reseller-table {
    min-width: 780px;
  }

  .agent-reseller-table .agent-address-cell,
  .agent-reseller-table .agent-address-head {
    max-width: 150px;
    white-space: normal;
    word-break: break-word;
    overflow-wrap: anywhere;
  }

  .agent-reseller-table .agent-action-cell {
    min-width: 120px;
    white-space: nowrap;
  }

  @media (max-width: 768px) {
    .agent-reseller-tools {
      display: block;
    }

    .agent-reseller-tools #filterTable {
      max-width: 100%;
      width: 100%;
      margin-top: 6px;
      margin-right: 0;
    }

    .agent-reseller-table-wrap {
      max-height: none;
    }
  }
</style>

<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3 class="card-title"><i class="fa fa-users"></i> <?= $_agent_reseller ?> | <?= $session; ?></h3>
      </div>
      <div class="card-body">
        <?php if (!empty($agentResellerMessage)) {
          echo $agentResellerMessage;
        } ?>
        <div class="row">
          <div class="col-7">
            <!-- <div class="card">
              <div class="card-header">
                <h3 class="card-title"><?= $_agent_reseller_list ?></h3>
              </div> -->
              <div class="card-body">
                <div class="agent-reseller-tools">
                  <input id="filterTable" type="text" class="form-control" placeholder="<?= $_search ?>">
                </div>
                <div class="overflow box-bordered agent-reseller-table-wrap">
                  <table id="dataTable" class="table table-bordered table-hover table-striped table-sm agent-reseller-table">
                    <thead>
                      <tr>
                        <th><?= $_agent_code ?></th>
                        <th><?= $_name ?></th>
                        <th><?= $_agent_contact ?></th>
                        <th class="agent-address-head"><?= $_address ?></th>
                        <th><?= $_commission ?></th>
                        <th><?= $_users ?> (Used/Total)</th>
                        <th><?= $_status ?></th>
                        <th><?= $_action ?></th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php if (empty($agentResellerList)) { ?>
                        <tr>
                          <td colspan="8" class="text-center">No Agent Reseller data available for this router.</td>
                        </tr>
                      <?php } else {
                        foreach ($agentResellerList as $agentKey) {
                          $agentItem = mikhmon_get_agent_reseller_item($agentreseller, $session, $agentKey);
                          $deleteAgentName = htmlspecialchars(addslashes($agentItem['name']), ENT_QUOTES);
                          $agentStats = isset($agentRelationStats[$agentKey]) ? $agentRelationStats[$agentKey] : array('remaining' => 0, 'total' => 0);
                          $relatedVoucherCount = isset($agentStats['total']) ? $agentStats['total'] : 0;
                          $remainingVoucherCount = isset($agentStats['remaining']) ? $agentStats['remaining'] : 0;
                          ?>
                          <tr>
                            <td><?= htmlspecialchars($agentItem['code']); ?></td>
                            <td><?= htmlspecialchars($agentItem['name']); ?></td>
                            <td><?= htmlspecialchars($agentItem['contact']); ?></td>
                            <td class="agent-address-cell"><?= nl2br(htmlspecialchars($agentItem['address'])); ?></td>
                            <td><?= htmlspecialchars($agentItem['commission']); ?></td>
                            <td class="text-center"><?= $remainingVoucherCount . '/' . $relatedVoucherCount; ?></td>
                            <td><?= ucfirst(htmlspecialchars($agentItem['status'])); ?></td>
                            <td class="agent-action-cell">
                              <a href="./?hotspot=agent-reseller&session=<?= $session; ?>&edit-agent=<?= $agentKey; ?>"><i class="fa fa-edit"></i> <?= $_edit ?></a>
                              |
                              <?php if ($relatedVoucherCount > 0) { ?>
                                <span class="text-muted" title="<?= $_agent_has_related_vouchers; ?>"><i class="fa fa-lock"></i> <?= $_delete ?></span>
                              <?php } else { ?>
                                <a href="javascript:void(0)" onclick="if(confirm('Hapus Agent Reseller <?= $deleteAgentName; ?>?')){window.location='./?hotspot=agent-reseller&session=<?= $session; ?>&remove-agent=<?= $agentKey; ?>'}"><i class="fa fa-remove"></i> <?= $_delete ?></a>
                              <?php } ?>
                            </td>
                          </tr>
                        <?php }
                      } ?>
                    </tbody>
                  </table>
                </div>
              </div>
            <!-- </div> -->
          </div>
          <div class="col-5">
            <form autocomplete="off" method="post" action="">
              <div class="card">
                <div class="card-header">
                  <h3 class="card-title"><i class="fa fa-user-plus"></i> <?= $isEditMode ? $_edit_agent_reseller : $_add_agent_reseller ?></h3>
                </div>
                <div class="card-body">
                  <table class="table table-sm">
                    <input type="hidden" name="originalagentcode" value="<?= htmlspecialchars($formAgent['code']); ?>">
                    <tr>
                      <td class="align-middle"><?= $_agent_code ?></td>
                      <td><input class="form-control" type="text" name="agentcode" maxlength="30" placeholder="agent-01" value="<?= htmlspecialchars($formAgent['code']); ?>" required="1"></td>
                    </tr>
                    <tr>
                      <td class="align-middle"><?= $_name ?></td>
                      <td><input class="form-control" type="text" name="agentname" maxlength="100" value="<?= htmlspecialchars($formAgent['name']); ?>" required="1"></td>
                    </tr>
                    <tr>
                      <td class="align-middle"><?= $_agent_contact ?></td>
                      <td><input class="form-control" type="text" name="agentcontact" maxlength="100" value="<?= htmlspecialchars($formAgent['contact']); ?>"></td>
                    </tr>
                    <tr>
                      <td class="align-middle"><?= $_address ?></td>
                      <td><textarea class="form-control" name="agentaddress" rows="3" maxlength="255"><?= htmlspecialchars($formAgent['address']); ?></textarea></td>
                    </tr>
                    <tr>
                      <td class="align-middle"><?= $_commission ?></td>
                      <td><input class="form-control" type="text" name="agentcommission" maxlength="100" value="<?= htmlspecialchars($formAgent['commission']); ?>" placeholder="10% / Rp 2.000">
                      <small>e.g: 10% or Rp 2.000</small></td>
                    </tr>
                    <tr>
                      <td class="align-middle"><?= $_status ?></td>
                      <td>
                        <select class="form-control" name="agentstatus">
                          <option value="enable" <?= $formAgent['status'] == 'enable' ? 'selected' : ''; ?>>Enable</option>
                          <option value="disable" <?= $formAgent['status'] == 'disable' ? 'selected' : ''; ?>>Disable</option>
                        </select>
                      </td>
                    </tr>
                    <tr>
                      <td></td>
                      <td class="text-right">
                        <input class="btn bg-primary" type="submit" name="saveagentreseller" value="<?= $_save ?>">
                        <?php if ($isEditMode) { ?>
                          <a class="btn" href="./?hotspot=agent-reseller&session=<?= $session; ?>"><?= $_cancel ?></a>
                        <?php } ?>
                      </td>
                    </tr>
                  </table>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
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

// hide all error
error_reporting(0);
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {

// array color
  $color = array('1' => 'bg-blue', 'bg-indigo', 'bg-purple', 'bg-pink', 'bg-red', 'bg-yellow', 'bg-green', 'bg-teal', 'bg-cyan', 'bg-grey', 'bg-light-blue');

  if (isset($_POST['save'])) {

    $suseradm = ($_POST['useradm']);
    $spassadm = encrypt($_POST['passadm']);
    $logobt = ($_POST['logobt']);
    $qrbt = ($_POST['qrbt']);

    $cari = array('1' => "mikhmon<|<$useradm", "mikhmon>|>$passadm");
    $ganti = array('1' => "mikhmon<|<$suseradm", "mikhmon>|>$spassadm");

    for ($i = 1; $i < 3; $i++) {
      $file = file("./include/config.php");
      $content = file_get_contents("./include/config.php");
      $newcontent = str_replace((string)$cari[$i], (string)$ganti[$i], "$content");
      file_put_contents("./include/config.php", "$newcontent");
    }

  
  $gen = '<?php $qrbt="' . $qrbt . '";?>';
          $key = './include/quickbt.php';
          $handle = fopen($key, 'w') or die('Cannot open file:  ' . $key);
          $data = $gen;
          fwrite($handle, $data);
    echo "<script>window.location='./admin.php?id=sessions'</script>";
  }

}
?>
<script>
  function Pass(id){
    var x = document.getElementById(id);
    if (x.type === 'password') {
    x.type = 'text';
    } else {
    x.type = 'password';
    }}
</script>

<div class="row">
	<div class="col-12">
  	<div class="card">
  		<div class="card-header">
  			<h3 class="card-title"><i class="fa fa-gear"></i> <?= $_admin_settings ?> &nbsp; | &nbsp;&nbsp;<i onclick="location.reload();" class="fa fa-refresh pointer " title="Reload data"></i></h3>
  		</div>
      <div class="card-body">
        <div class="row">
          <div class="col-6">
            <div class="card">
              
              <div class="card-header">
                <h3 class="card-title"><i class="fa fa-server"></i> <?= $_router_list ?></h3>
              </div>
            <div class="card-body">
            <div class="row">
              <?php
              foreach (file('./include/config.php') as $line) {
                $value = explode("'", $line)[1];
                if ($value == "" || $value == "mikhmon") {
                } else { ?>
                    <div class="col-12">
                        <div class="box bmh-75 box-bordered <?= $color[rand(1, 11)]; ?>">
                                <div class="box-group">
                                  
                                  <div class="box-group-icon">
                                    <span class="connect pointer" id="<?= $value; ?>">
                                    <i class="fa fa-server"></i>
                                    </span>
                                  </div>
                                
                                  <div class="box-group-area">
                                    <span>
                                      <?= $_hotspot_name ?> : <?= explode('%', $data[$value][4])[1]; ?><br>
                                      <?= $_session_name ?> : <?= $value; ?><br>
                                      <span class="connect pointer"  id="<?= $value; ?>"><i class="fa fa-external-link"></i> <?= $_open ?></span>&nbsp;
                                      <a href="./admin.php?id=settings&session=<?= $value; ?>"><i class="fa fa-edit"></i> <?= $_edit ?></a>&nbsp;
                                      <a href="javascript:void(0)" onclick="if(confirm('Are you sure to delete data <?= $value;
                                      echo " (" . explode('%', $data[$value][4])[1] . ")"; ?>?')){loadpage('./admin.php?id=remove-session&session=<?= $value; ?>')}else{}"><i class="fa fa-remove"></i> <?= $_delete ?></a>
                                    </span>

                                  </div>
                                </div>
                              
                            </div>
                          </div>
              <?php
            }
          }
          ?>
              </div>
            </div>
          </div>
        </div>
			    <div class="col-6">
          <form autocomplete="off" method="post" action="">
            <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fa fa-user-circle"></i> <?= $_admin ?></h3>
              </div>
            <div class="card-body">
      <table class="table table-sm">
        <tr>
          <td class="align-middle"><?= $_user_name ?> </td><td><input class="form-control" id="useradm" type="text" size="10" name="useradm" title="User Admin" value="<?= $useradm; ?>" required="1"/></td>
        </tr>
        <tr>
          <td class="align-middle"><?= $_password ?> </td>
          <td>
          <div class="input-group">
          <div class="input-group-11 col-box-10">
                <input class="group-item group-item-l" id="passadm" type="password" size="10" name="passadm" title="Password Admin" value="<?= decrypt($passadm); ?>" required="1"/>
              </div>
                <div class="input-group-1 col-box-2">
                  <div class="group-item group-item-r pd-2p5 text-center align-middle">
                      <input title="Show/Hide Password" type="checkbox" onclick="Pass('passadm')">
                  </div>
                </div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="align-middle"><?= $_quick_print ?> QR</td>
          <td>
            <select class="form-control" name="qrbt">
            <option><?= $qrbt ?></option>
              <option>enable</option>
              <option>disable</option>
            </select>
          </td>
        </tr>
        <tr>
          <td></td><td class="text-right">
              <div class="input-group-4">
                  <input class="group-item group-item-l" type="submit" style="cursor: pointer;" name="save" value="<?= $_save ?>"/>
                </div>
                <div class="input-group-2">
                  <div style="cursor: pointer;" class="group-item group-item-r pd-2p5 text-center" onclick="location.reload();" title="Reload Data"><i class="fa fa-refresh"></i></div>
                </div>
                </div>
          </td>
        </tr>
        
      </table>
      <div id="loadV">v<?= $_SESSION['vm']; ?> </div>
      <div><b id="newVer" class="text-green"></b></div>
    </div>
    </div>
    </form>
  </div>
</div>
</div>
</div>
</div>
</div>
<script>
  var _0x7470 = ["hostname", "location", ".", "split", "mikhmon.online", "mikhman.my.id", "logam.id", "minis.id", "indexOf", "<span ><i class=\"text-white fa fa-info-circle\"></i> <a class=\"text-blue\" href=\"./admin.php?id=about\">Check Update</a></span>", "html", "#newVer", "https://raw.githubusercontent.com/MyTunnelWebID/mikhmon-mod/main/version.txt?t=", "random", "floor", "v", "version", "", "replace", "innerHTML", "loadV", "getElementById", " ", "updated", "-", "New Version ", "<br><span ><i class=\"text-white fa fa-info-circle\"></i> <a class=\"text-blue\" href=\"./admin.php?id=about\">Check Update</a></span>", "getJSON"];
  var hname = window[_0x7470[1]][_0x7470[0]];
  var dom = hname[_0x7470[3]](_0x7470[2])[1] + _0x7470[2] + hname[_0x7470[3]](_0x7470[2])[2];
  var domArray = [_0x7470[4], _0x7470[5], _0x7470[6], _0x7470[7]];
  var a = domArray[_0x7470[8]](hname);
  var b = domArray[_0x7470[8]](dom);
  
  if (dom == _0x7470[4]) {
    $(_0x7470[11])[_0x7470[10]](_0x7470[9]);
  } else {
    if (a > 0 || b > 0) {} else {
      $[_0x7470[27]](_0x7470[12] + Math[_0x7470[14]](Math[_0x7470[13]]() * 999999999 + 1) * 128, function (_0xc1b4x6) {
        getNewVer = _0xc1b4x6[_0x7470[16]][_0x7470[3]](_0x7470[15])[1];
        var _0xc1b4x7 = parseInt(getNewVer[_0x7470[18]](_0x7470[2], _0x7470[17]));
        var _0xc1b4x8 = document[_0x7470[21]](_0x7470[20])[_0x7470[19]];
        var _0xc1b4x9 = _0xc1b4x8[_0x7470[3]](_0x7470[22])[0][_0x7470[3]](_0x7470[15])[1];
        var _0xc1b4xa = parseInt(_0xc1b4x9[_0x7470[18]](_0x7470[2], _0x7470[17]));
        var _0xc1b4xb = _0xc1b4x7 - _0xc1b4xa;
        getNewVer = _0xc1b4x6[_0x7470[16]][_0x7470[3]](_0x7470[15])[1];
        var _0xc1b4x7 = parseInt(getNewVer[_0x7470[18]](_0x7470[2], _0x7470[17]));
        var _0xc1b4x8 = document[_0x7470[21]](_0x7470[20])[_0x7470[19]];
        var _0xc1b4x9 = _0xc1b4x8[_0x7470[3]](_0x7470[22])[0][_0x7470[3]](_0x7470[15])[1];
        var _0xc1b4xa = parseInt(_0xc1b4x9[_0x7470[18]](_0x7470[2], _0x7470[17]));
        var _0xc1b4xb = _0xc1b4x7 - _0xc1b4xa;
        getNewD = _0xc1b4x6[_0x7470[23]][_0x7470[3]](_0x7470[22])[0];
        newD = parseInt(getNewD[_0x7470[3]](_0x7470[24])[2] + getNewD[_0x7470[3]](_0x7470[24])[0] + getNewD[_0x7470[3]](_0x7470[24])[1]);
        var _0xc1b4xc = parseInt(_0xc1b4x8[_0x7470[3]](_0x7470[22])[1][_0x7470[3]](_0x7470[24])[2] + _0xc1b4x8[_0x7470[3]](_0x7470[22])[1][_0x7470[3]](_0x7470[24])[0] + _0xc1b4x8[_0x7470[3]](_0x7470[22])[1][_0x7470[3]](_0x7470[24])[1]);
        var _0xc1b4xd = newD - _0xc1b4xc;
        if (_0xc1b4xb > 0 || _0xc1b4xd > 0) {
          $(_0x7470[11])[_0x7470[10]](_0x7470[25] + _0xc1b4x6[_0x7470[16]] + _0x7470[22] + _0xc1b4x6[_0x7470[23]] + _0x7470[26]);
        }
      });
    }
  }
</script>
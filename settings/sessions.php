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
          <!-- Router List -->
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
          <!-- Router List -->
        
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
    
          <!-- Adverstisment -->
          <div class="card">
              <div class="card-header">
                <h3 class="card-title"><i class="fa fa-tag"></i> Advertisement</h3>
              </div>
            <div class="card-body">
            <div class="row">
            <h3>MIKHMON MOD V<?= $_SESSION['vm']; ?></h3>
                <p>
                  Jika kamu seorang pengusaha wifi voucher maupun RT/RW NET dan tidak memiliki IP Public untuk bisa memantau server jaringan dari jarak jauh, silahkan coba layanan dari <a href="<?= $_SESSION['website-mod']; ?>">MyTunnel</a>.
                </p>
                <p>
                  <ul>
                    <li>
                      VPN Remote : Mulai dari Rp 2.000/bln
                    </li>
                    <li>
                      VPN Masking : Mulai dari Rp 2.000/bln
                    </li>
                    <li>
                      VPN Traffic : Mulai dari Rp 10.000/bln
                    </li>
                    <li>
                      VPN Interkoneksi : Mulai dari Rp 2.000/bln
                    </li>
                    <li>
                      Mikhmon Online : Mulai dari Rp 5.000/bln
                    </li>
                  </ul>
                </p>
                <p>Kunjungi sekarang:
                  <a href="<?= $_SESSION['website-mod']; ?>"><?= $_SESSION['website-mod']; ?></a>
                </p>
                <p>
                  Terima kasih untuk semua yang telah mendukung pengembangan MIKHMON MOD.
                </p>
                <div>
                  <i>Copyright &copy; <i> <?= $_SESSION['copyright-mod']; ?></i></i>
                </div>
              </div>
            </div>
          </div>
          <!-- Adverstisment -->
  </div>
</div>
</div>
</div>
</div>
</div>
<script>
  var _0x35a017=_0x57ea;function _0x51d8(){var _0x2d967c=['2782503eiPAhG','mikhman.my.id','hostname','423247dXUJEU','1346093pIQymg','loadV','indexOf','<br><span\x20><i\x20class=\x22text-white\x20fa\x20fa-info-circle\x22></i>\x20<a\x20class=\x22text-blue\x22\x20href=\x22./admin.php?id=about\x22>Check\x20Update</a></span>','updated','8CUlQPF','html','2GZZIwv','249136nximiP','version','4849710CXdJzX','location','New\x20Version\x20','1960008qSFKtB','replace','#newVer','split','4629590TPGbbt','getElementById'];_0x51d8=function(){return _0x2d967c;};return _0x51d8();}(function(_0x1fa2da,_0x70ad5b){var _0x575b62=_0x57ea,_0x243b36=_0x1fa2da();while(!![]){try{var _0x56f90f=parseInt(_0x575b62(0xae))/0x1*(parseInt(_0x575b62(0xb6))/0x2)+parseInt(_0x575b62(0xb9))/0x3+-parseInt(_0x575b62(0xb7))/0x4+-parseInt(_0x575b62(0xa9))/0x5+-parseInt(_0x575b62(0xa5))/0x6+parseInt(_0x575b62(0xaf))/0x7*(-parseInt(_0x575b62(0xb4))/0x8)+parseInt(_0x575b62(0xab))/0x9;if(_0x56f90f===_0x70ad5b)break;else _0x243b36['push'](_0x243b36['shift']());}catch(_0x257c4e){_0x243b36['push'](_0x243b36['shift']());}}}(_0x51d8,0xcd857));function _0x57ea(_0x1732e2,_0x4ad872){var _0x51d80d=_0x51d8();return _0x57ea=function(_0x57ea37,_0x119367){_0x57ea37=_0x57ea37-0xa4;var _0x16d3e4=_0x51d80d[_0x57ea37];return _0x16d3e4;},_0x57ea(_0x1732e2,_0x4ad872);}var hname=window[_0x35a017(0xba)][_0x35a017(0xad)],dom=hname[_0x35a017(0xa8)]('.')[0x1]+'.'+hname[_0x35a017(0xa8)]('.')[0x2],domArray=[_0x35a017(0xac)],a=domArray[_0x35a017(0xb1)](hname),b=domArray[_0x35a017(0xb1)](dom);if(dom==_0x35a017(0xac))$('#newVer')[_0x35a017(0xb5)]('<span\x20><i\x20class=\x22text-white\x20fa\x20fa-info-circle\x22></i>\x20<a\x20class=\x22text-blue\x22\x20href=\x22./admin.php?id=about\x22>Check\x20Update</a></span>');else{if(a>0x0||b>0x0){}else $['getJSON']('https://raw.githubusercontent.com/MyTunnelWebID/mikhmon-mod/main/version.txt?t='+Math['floor'](Math['random']()*0x3b9ac9ff+0x1)*0x80,function(_0x4f08a9){var _0x2ce0e3=_0x35a017,_0x283347=_0x4f08a9[_0x2ce0e3(0xb8)][_0x2ce0e3(0xa8)]('v')[0x1],_0x541884=parseInt(_0x283347[_0x2ce0e3(0xa6)]('.','')),_0x2743f5=document[_0x2ce0e3(0xaa)](_0x2ce0e3(0xb0))['innerHTML'],_0x44a35d=parseInt(_0x2743f5[_0x2ce0e3(0xa8)]('\x20')[0x0][_0x2ce0e3(0xa8)]('v')[0x1][_0x2ce0e3(0xa6)]('.','')),_0x5504ed=_0x541884-_0x44a35d,_0x58e988=_0x4f08a9[_0x2ce0e3(0xb3)][_0x2ce0e3(0xa8)]('-')[0x0],_0x166ad2=parseInt(_0x58e988[_0x2ce0e3(0xa8)]('-')[0x2]+_0x58e988[_0x2ce0e3(0xa8)]('-')[0x0]+_0x58e988['split']('-')[0x1]),_0x219e0f=parseInt(_0x2743f5[_0x2ce0e3(0xa8)]('\x20')[0x1]['split']('-')[0x2]+_0x2743f5[_0x2ce0e3(0xa8)]('\x20')[0x1][_0x2ce0e3(0xa8)]('-')[0x0]+_0x2743f5['split']('\x20')[0x1][_0x2ce0e3(0xa8)]('-')[0x1]),_0xc03e7c=_0x166ad2-_0x219e0f;(_0x5504ed>0x0||_0xc03e7c>0x0)&&$(_0x2ce0e3(0xa7))[_0x2ce0e3(0xb5)](_0x2ce0e3(0xa4)+_0x4f08a9[_0x2ce0e3(0xb8)]+'\x20'+_0x4f08a9['updated']+_0x2ce0e3(0xb2));});}
</script>
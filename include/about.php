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
if (!isset($_SESSION["mikhmon"])) {
  header("Location:../admin.php?id=login");
} else {
}
?>
<style>
.iFWrapper {
	position: relative;
	padding-bottom: 56.25%; /* 16:9 */
	padding-top: 25px;
	height: 0;
}
.iFWrapper iframe {
	position: absolute;
	top: 0;
	left: 0;
	width: 100%;
  height: 100%;
  border :none;
}
</style>
<!-- About Author-->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> About Author</h3>
      </div>
      <div class="card-body">
        <h3>MIKHMON V<?= $_SESSION['v']; ?></h3>
<p>
  Aplikasi ini dipersembahkan untuk pengusaha hotspot di manapun Anda berada.
  Semoga makin sukses.
</p>
<p>
  <ul>
    <li>
      Author : Laksamadi Guko
    </li>
    <li>
      Licence : <a href="https://github.com/laksa19/mikhmonv2/blob/master/LICENSE">GPLv2</a>
    </li>
    <li>
      API Class : <a href="https://github.com/BenMenking/routeros-api">routeros-api</a>
    </li>
    <li>
      Website : <a href="https://laksa19.github.io">laksa19.github.io</a>
    </li>
    <li>
      Facebook : <a href="https://fb.com/laksamadi">fb.com/laksamadi</a>
    </li>
  </ul>
</p>
<p>
  Terima kasih untuk semua yang telah mendukung pengembangan MIKHMON.
</p>
<div>
    <i>Copyright &copy; <i> 2018 Laksamadi Guko</i></i>
</div>
</div>
</div>
</div>
<!-- About Author-->

<!-- About Modification -->
<div class="row">
  <div class="col-12">
    <div class="card">
      <div class="card-header">
        <h3><i class="fa fa-info-circle"></i> About Mod</h3>
      </div>
      <div class="card-body">
        <h3>MIKHMON V<?= $_SESSION['vm']; ?></h3>
<p>
  Aplikasi ini hasil dari modifikasi <?= $_SESSION['author-mod']; ?>, dan memiliki beberapa perubahan didalamnya. Namun, tidak menghilangkan ciri khas dari fitur utamanya.<br>
  Dan hanya tersedia didalam fitur dari website <?= $_SESSION['website-mod']; ?>.
</p>
<p>
  <ul>
    <li>
      Author : <?= $_SESSION['author-mod']; ?>
    </li>
    <li>
      Licence : <a href="#"><?= $_SESSION['licence-mod']; ?></a>
    </li>
    <li>
      Website : <a href="<?= $_SESSION['website-mod']; ?>"><?= $_SESSION['website-mod']; ?></a>
    </li>
    <li>
      Facebook : <a href="<?= $_SESSION['facebook-mod']; ?>"><?= $_SESSION['facebook-mod']; ?></a>
    </li>
    <li>
      Telegram Group : <a href="https://t.me/+l4Gi2kHAIgNiNTE1">Join now!</a>
    </li>
  </ul>
</p>
<p>
  Terima kasih untuk semua yang telah mendukung pengembangan MIKHMON.
</p>
<div>
    <i>Copyright &copy; <i> <?= $_SESSION['copyright-mod']; ?></i></i>
</div>
</div>
</div>
</div>
<!-- About Modification -->

<div class="col-12">
<div class="card">
  <div class="card-header">
  <h3><i class="fa fa-info-circle"></i> Changelog</h3>
  </div>
  <div class="card-body">
  <div class="iFWrapper">
    <iframe src="https://laksa19.github.io/mikhmonv3" ></iframe>
  </div>
  </div>
</div>
</div>
</div>

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


?>

<div style="padding-top: 5%;"  class="login-box">
  <div class="card">
    <div class="card-header">
      <h3><?= $_please_login ?></h3>
    </div>
    <div class="card-body">
      <div class="text-center pd-5">
        <img src="img/favicon.png" alt="MIKHMON Logo">
      </div>
      <div  class="text-center">
      <span style="font-size: 25px; margin: 10px;">MIKHMON <b>MOD</b></span>
      </div>
      <center>
      <form autocomplete="off" action="" method="post">
      <table class="table" style="width:90%">
        <tr>
          <td class="align-middle text-center">
            <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="text" name="user" id="_username" placeholder="Username" required="1" autofocus>
          </td>
        </tr>
        <tr>
        <td class="align-middle text-center">
          <div style="position: relative;">
            <input style="width: 100%; height: 35px; font-size: 16px;" class="form-control" type="password" name="pass" placeholder="Password" id="password" required="1">
            <i style="position: absolute; top: 45%; right: 10px; transform: translateY(-50%);">
              <input type="checkbox" class="pointer" id="togglePassword" style="width: 15px; height: 15px;">
            </i>
          </div>
        </td>
      </tr>
        <tr>
          <td class="align-middle text-center">
            <input style="width: 100%; margin-top:0px; height: 35px; font-weight: bold; font-size: 17px;" class="btn-login bg-primary pointer" type="submit" name="login" value="Login">
          </td>
        </tr>
        <tr>
          <td class="align-middle text-center">
            <?= $error; ?>
          </td>
        </tr>
      </table>
      </form>
      </center>
    </div>
  </div>
</div>

</body>
</html>
<script>
  const passwordInput = document.getElementById("password");
  const togglePasswordButton = document.getElementById("togglePassword");

  togglePasswordButton.addEventListener("click", function () {
      if (passwordInput.type === "password") {
          passwordInput.type = "text";
          togglePasswordButton.textContent = "Hide Password";
      } else {
          passwordInput.type = "password";
          togglePasswordButton.textContent = "Show Password";
      }
  });
  </script>
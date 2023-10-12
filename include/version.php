<?php
if (!isset($_SESSION["mikhmon"])) {
    header("Location:../admin.php?id=login");
  } else {
    /* Version ChangeLog */
        $_SESSION["copyright-mod"] = "2023 MyTunnel Teams";
        $_SESSION["author-mod"] = "MyTunnel Teams";
        $_SESSION["licence-mod"] = "No Licence";
        $_SESSION["website-mod"] = "https://mytunnel.web.id";
        $_SESSION["facebook-mod"] = "https://www.facebook.com/mytunnelwebid";
    /* Version ChangeLog */


    /* Version ChangeLog */
        $_SESSION["v"] = "3.20 06-30-2021";
        $_SESSION["vm-old"] = "1.00 01-09-2023";
        $_SESSION["vm"] = "1.02 12-10-2023";
    /* Version ChangeLog */
    }

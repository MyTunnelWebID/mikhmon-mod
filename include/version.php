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
        // $_SESSION["v"] = "3.20 06-30-2021";
        
        // $_SESSION["vm-old"] = "1.00 09-01-2023";
        // $_SESSION["vm"] = "1.06 12-09-2024";

        // $_SESSION["vm-old"] = "1.02 09-09-2024";
        // $_SESSION["vm"] = "1.06.06122024 12-06-2024";

        $_SESSION["vm-old"] = "1.02 09-09-2024";
        $_SESSION["vm"] = "1.10.20052025 20-05-2025";
    /* Version ChangeLog */
    }

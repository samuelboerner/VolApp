<?php

    require_once "backend/vendor/autoload.php";

    use \ParagonIE\HiddenString\HiddenString;
    use \ParagonIE\Halite\Symmetric\EncryptionKey;
    use \ParagonIE\Halite\Cookie;

    // Define hidden constants
    require_once "backend/secure.php";

    // Make an encryped key using a defined string, and configure the encryptor
    $key = new EncryptionKey(new HiddenString(RAW_KEY));
    $cookies = new Cookie($key);

    if (empty($cookies->fetch("access_token")) || empty($cookies->fetch("instance_url"))) {
        header("Location: login.php");
    }

?>

<!DOCTYPE html>
<html>
  <head>
    <meta charset="UTF-8">
    <title>Volunteer Home - Rainier Valley Food Bank</title>
    <link href="https://fonts.googleapis.com/css?family=PT+Sans:400,400italic,700,700italic|Arvo" rel="stylesheet" type="text/css">
    <link rel="stylesheet" href="/assets/css/style.css">
  </head>
  <body>
    <header>
      <div class="container">
        <h1>Welcome Volunteers</h1>
      </div>
    </header>
    <div class="container">
      <p>
        <span class="greenSpan"> Liability Release – </span> I hereby release, indemnify and hold harmless Rainier
        Valley Food Bank, its officers, directors and employees, and the organizers, sponsors, and supervisors from
        any and all liability in connection with any injury I may sustain (including any injury caused by negligence)
        in conjunction with activities inside or outside Rainier Valley Food Bank. *The Corporation for National and
        Community Service, AmeriCorps requires this information to better assess demographics in areas where AmeriCorps
        members are placed © <em>Elise Cope</em>
      </p>
    </div>
  </body>
</html>

<?php

require_once "backend/vendor/autoload.php";

use \ParagonIE\HiddenString\HiddenString;
use \ParagonIE\Halite\Symmetric\EncryptionKey;
use \ParagonIE\Halite\Cookie;

// Define hidden constants
require_once "backend/secure.php";

// Configure our cookie cryptor
$key = new EncryptionKey(new HiddenString(RAW_KEY));
$cookies = new Cookie($key);

// Set timezone
date_default_timezone_set("America/Los_Angeles");

$access_token = $cookies->fetch("access_token");
$instance_url = $cookies->fetch("instance_url");

// Redirect user back to login if tokens are not set
if (empty($access_token) || empty($instance_url)) {
    header("Location: login.html");
    exit();
}

// Create a new client pointed at the instance url
$client = new GuzzleHttp\Client([
    "base_uri" => $instance_url,
    /*"timeout" => 5.0*/
]);

// Define endpoint
define("QUERY_URI", "/services/data/v20.0/query/");

// Get a list of volunteer names, volunteer IDs, and match check-in IDs
// to those currently checked-in. We will use those IDs for check-out
//
// SOQL queries do not support OUTER JOIN statements, therefore we use a subselect
// to retrieve the check-in ID from the child GW_Volunteers__Volunteer_Hours__r table
$query =
"SELECT Name, Id,
(
  SELECT Id, CreatedDate
  FROM GW_Volunteers__Volunteer_Hours__r
  WHERE Date_Time_Out__c = NULL
  AND CreatedDate = TODAY
)
FROM Contact
ORDER BY Name";

// Make the query
$response = $client->request("GET", QUERY_URI, [
    "headers" => [
        "Authorization" => "Bearer ".$access_token,
        "Accept" => "application/json"
    ],
    "query" => [
      "q" => $query
    ]
]);

// Store results
$records = json_decode($response->getBody(),true)["records"];

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
    <p class="container">
      <span class="span-green"> Liability Release – </span> I hereby release, indemnify and hold harmless Rainier
      Valley Food Bank, its officers, directors and employees, and the organizers, sponsors, and supervisors from
      any and all liability in connection with any injury I may sustain (including any injury caused by negligence)
      in conjunction with activities inside or outside Rainier Valley Food Bank. *The Corporation for National and
      Community Service, AmeriCorps requires this information to better assess demographics in areas where AmeriCorps
      members are placed © <em>Elise Cope</em>
    </p>

    <table class="container">
      <tbody><?php
        foreach ($records as $record) {
          $volunteer_id = $record["Id"];
          if ($active = !empty($checkin = $record["GW_Volunteers__Volunteer_Hours__r"]["records"][0])) {
            $checkin_id = $checkin["Id"];
            $checkin_time = date('g:i A', strtotime($checkin["CreatedDate"]));
          }?>

          <tr>
            <td><?php
               echo $record["Name"];?>
            </td>

            <td><?php
              echo ($active)? "Checked in at ".$checkin_time: "";?>
            </td>

            <td><?php
              if (!$active) { ?>
                <a class="button list-button green-button" href=<?php
                echo "/backend/submit.php?volunteer=".$volunteer_id;?>
                >
                  Check-in
                </a><?php
              } else { ?>
                <a class="button list-button red-button" href=<?php
                echo "/backend/submit.php?checkin=".$checkin_id;?>
                >
                  Check-out
                </a><?php
              }?>
            </td>
          </tr><?php
        }?>
      </tbody>
    </table>
  </body>
</html>

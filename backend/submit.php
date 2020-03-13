<?php

require_once "vendor/autoload.php";

use \ParagonIE\HiddenString\HiddenString;
use \ParagonIE\Halite\Symmetric\EncryptionKey;
use \ParagonIE\Halite\Cookie;

// Define hidden constants
require_once "secure.php";

// Configure our cookie cryptor
$key = new EncryptionKey(new HiddenString(RAW_KEY));
$cookies = new Cookie($key);

$access_token = $cookies->fetch("access_token");
$instance_url = $cookies->fetch("instance_url");

// Set current (date)time
$current_time = $datetime_in = substr_replace(date("c"), "00+00:00", 17);

// Redirect user back to login if tokens are not set
if (empty($access_token) || empty($instance_url)) {
    header("Location: login.html");
    exit();
}

// Create a new client pointed at the instance url
$client = new GuzzleHttp\Client([
    "base_uri" => $instance_url,
]);

// Check if request is a check-in event
if (!empty($_GET["volunteer"])) {

    // Define constants
    define("POST_URI", "/services/data/v20.0/sobjects/GW_Volunteers__Volunteer_Hours__c");
    define("VOLUNTEER_JOB", "a0N3J000000AymTUAS");

    // Declare our volunteer and GW_Volunteers__Start_Date__c variable
    $volunteer_id = $_GET["volunteer"];
    $current_date = substr_replace($current_time, "", 10);

    // Submit check-in event
    $response = $client->request("POST", POST_URI, [
          "headers" => [
              "Authorization" => "Bearer ".$access_token,
              "Accept" => "application/json"
          ],
          "json" => [
              "GW_Volunteers__Contact__c" => $volunteer_id,
              "Date_Time_In__c" => $current_time,
              "GW_Volunteers__Start_Date__c" => $current_date,
              "GW_Volunteers__Status__c" => "Completed",
              "GW_Volunteers__Volunteer_Job__c" => VOLUNTEER_JOB,
              "Community_Service__c" => false,
              "GW_Volunteers__Number_of_Volunteers__c" => 1.0
          ]
      ]);

      // Redirect back to home page
      header("Location: /");
      exit();

} elseif (!empty($_GET["checkin"])) {

  // Declare our check-in ID
  $checkin_id = $_GET["checkin"];

  // Define endpoint
  define("PATCH_URI", "/services/data/v20.0/sobjects/GW_Volunteers__Volunteer_Hours__c/".$checkin_id);

  // Submit check-out event
  $response = $client->request("POST", PATCH_URI, [
        "headers" => [
            "Authorization" => "Bearer ".$access_token,
            "Accept" => "application/json"
        ],
        "json" => [
            "Date_Time_Out__c" => $current_time
        ],
        "query" => [
            "_HttpMethod" => "PATCH",
        ]
    ]);

  // Redirect back to home page
  header("Location: /");
  exit();
}

header("HTTP/1.1 403");
?>

<?php

require_once "vendor/autoload.php";

use \ParagonIE\HiddenString\HiddenString;
use \ParagonIE\Halite\Symmetric\EncryptionKey;
use \ParagonIE\Halite\Cookie;

// Define hidden constants
require_once "secure.php";

// Replace "test" with "login" for deployment in production
const LOGIN_BASE_URI = "https://test.salesforce.com";
const AUTH_URI = "/services/oauth2/authorize";
const TOKEN_URI = "/services/oauth2/token";

// Make an encryped key using a defined string, and configure the encryptor
$key = new EncryptionKey(new HiddenString(RAW_KEY));
$cookies = new Cookie($key);

// Create the login client pointed at https://test.salesforce.com
$client = new GuzzleHttp\Client([
    "base_uri" => LOGIN_BASE_URI,
    "timeout" => 5.0
]);

// if there isn't an auth cookie set
if (empty($cookies->fetch("auth_token"))) {

    // If we aren't yet authenticated
    if (empty($_REQUEST["code"])) {

        // Make the initial auth request
        $response = $client->request("GET", AUTH_URI, [
            "query" => [
                "client_id" => CONSUMER_KEY,
                "redirect_uri" => CALLBACK_URI,
                "response_type" => "code"
            ]
        ]);

        // Display authentication
        echo $response->getBody();

        // Don't want to do anything more until user logs in
        exit();
    }

    // set auth cookie
  $cookies->store("auth_token", $_REQUEST["code"], strtotime("+1000 years"));

}

// Make the token request
$response = $client->request("POST", TOKEN_URI, [
    "form_params" => [
        "grant_type" => "authorization_code",
        "code" => $_REQUEST["code"],
        "client_id" => CONSUMER_KEY,
        "client_secret" => CONSUMER_SECRET,
        "redirect_uri" => CALLBACK_URI
    ]
]);

date_default_timezone_set('America/Los_Angeles');

$access_token = json_decode($response->getBody(),true)["access_token"];
$instance_url = json_decode($response->getBody(),true)["instance_url"];

// Store a secure access token and instance url (encrypted)
$cookies->store("access_token", $access_token, strtotime("tomorrow")); // Secure
$cookies->store("instance_url", $instance_url, strtotime("tomorrow")); // Unsecure

header("Location: /");

?>

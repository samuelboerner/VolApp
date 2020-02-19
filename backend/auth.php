<?php

require_once "vendor/autoload.php";

// Define the client ID, secret, and callback URI
require_once "credentials.php";

// Replace "test" with "login" for deployment in production
const LOGIN_BASE_URI = "https://test.salesforce.com";
const AUTH_URI = "/services/oauth2/authorize";
const TOKEN_URI = "/services/oauth2/token";

// Create the login client pointed at https://test.salesforce.com
$client = new GuzzleHttp\Client([
    "base_uri" => LOGIN_BASE_URI,
    "timeout" => 5.0
]);

// If we aren't yet authenticated
if (!isset($_REQUEST['code'])) {

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

echo "Success!!! Auth code ".$_REQUEST['code']." was delivered!";

?>

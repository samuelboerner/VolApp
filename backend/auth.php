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

// Make the token request
$response = $client->request("POST", TOKEN_URI, [
    "form_params" => [
        "grant_type" => "authorization_code",
        "code" => $_REQUEST['code'],
        "client_id" => CONSUMER_KEY,
        "client_secret" => CONSUMER_SECRET,
        "redirect_uri" => CALLBACK_URI
    ]
]);

echo   "<pre style='font-family:inherit'>";

          print_r(json_decode($response->getBody(), true));

// Store the token and instance url
$access_token = json_decode($response->getBody(),true)["access_token"];
$instance_url = json_decode($response->getBody(),true)["instance_url"];

// Now that we have authenticated, Reconfigure client to test apis
$client = new GuzzleHttp\Client([
    "base_uri" => $instance_url,
    "timeout" => 5.0
]);

try {
    // Define endpoint for querying
    define("QUERY_URI", "/services/data/v20.0/query/");

    // Prepare query
    $query = "SELECT Name, Id FROM Contact";

    // Test the API by getting a list of volunteers and their IDs
    $response = $client->request("GET", QUERY_URI, [
        "headers" => [
            "Authorization" => "Bearer ".$access_token,
            "Accept" => "application/json"
        ],
        "query" => [
          "q" => $query
        ]
    ]);
    // Display response if an exception in thrown
} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
}

echo     "<br>";

          print_r(json_decode($response->getBody(), true));

// Store the list of volunteers and their IDs
$volunteers = json_decode($response->getBody(), true)["records"];

try {
    // Define post endpoint and volunteer job
    define("POST_URI", "/services/data/v20.0/sobjects/GW_Volunteers__Volunteer_Hours__c");
    define("VOLUNTEER_JOB", "a0N3J000000AymTUAS");

    // Test posting a record up. Let's use the last volunteer
    // in the array from our earlier call
    $volunteer_id = end($volunteers)["Id"];

    // ...and pick some random times to enter. Time in ISO 8601
    // format
    $datetimein = "2020-02-10T09:00:00-0800";
    $datein = "2020-02-10";

    $response = $client->request("POST", POST_URI, [
        "headers" => [
            "Authorization" => "Bearer ".$access_token,
            "Accept" => "application/json"
        ],
        "json" => [
            "GW_Volunteers__Contact__c" => $volunteer_id,
            "Date_Time_In__c" => $datetimein,
            "GW_Volunteers__Start_Date__c" => $datein,
            "GW_Volunteers__Status__c" => "Completed",
            "GW_Volunteers__Volunteer_Job__c" => VOLUNTEER_JOB,
            "Community_Service__c" => false,
            "GW_Volunteers__Number_of_Volunteers__c" => 1.0
        ]
    ]);
    // Display response if an exception in thrown
} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
}

echo     "<br>Posted!!!<br>";

          print_r(json_decode($response->getBody(), true));

// Store the id of the record we just created
$record_id = json_decode($response->getBody(), true)["id"];

// Finally, test patching the record we just posted to cap
// off the day
try {
    // Define patch endpoint
    define("PATCH_URI", "/services/data/v20.0/sobjects/GW_Volunteers__Volunteer_Hours__c/".$record_id);

    // Pick the time the volunteer checks out
    $datetimeout = "2020-02-10T13:45:00-0800";

    // WE use POST because the salesforce specification offers it
    // as an alternative when PATCH as a method does not work
    $response = $client->request("POST", PATCH_URI, [
        "headers" => [
            "Authorization" => "Bearer ".$access_token,
            "Accept" => "application/json"
        ],
        "json" => [
            "Date_Time_Out__c" => $datetimeout
        ],
        "query" => [
            "_HttpMethod" => "PATCH"
        ]
    ]);
    // Display response if an exception in thrown
} catch (RequestException $e) {
    echo Psr7\str($e->getRequest());
    if ($e->hasResponse()) {
        echo Psr7\str($e->getResponse());
    }
}

echo     "<br>Patched!!!";

?>

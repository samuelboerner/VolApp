<?php

require_once "vendor/autoload.php";

// Define the client ID, secret, and callback URI
require_once "credentials.php";

// Replace "test" with "login" for deployment in production
const LOGIN_BASE_URI = "https://test.salesforce.com";
const AUTH_URI = "/services/oauth2/authorize";
const TOKEN_URI = "/services/oauth2/token";

?>

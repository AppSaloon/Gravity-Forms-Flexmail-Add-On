<?php
$user_id = 0;
$user_token = 'xxxxxxx-xxxxxxx-xxxxxxxx-xxxxxxx-xxxxxx';

$option = get_option('gravityformsaddon_gravityformsflexmail_settings');

// after first save
if( $option )
{
    $user_id = $option['apiUser'];
    $user_token = $option['apiKey'];
}

// first save
if( isset( $_POST['_gaddon_setting_apiUser'] ) )
    $user_id = $_POST['_gaddon_setting_apiUser'];

if( isset( $_POST['_gaddon_setting_apiKey'] ) )
    $user_token = $_POST['_gaddon_setting_apiKey'];



// Set your API User Id. Can be found under the Profile tab on flexmail.eu
define("FLEXMAIL_USER_ID", $user_id);

// Set your API User Token. Can be found under the Profile tab on flexmail.eu
define("FLEXMAIL_USER_TOKEN", $user_token);

// WSDL Location
define("FLEXMAIL_WSDL", "http://soap.flexmail.eu/3.0.0/flexmail.wsdl");

// Service Location
define("FLEXMAIL_SERVICE", "http://soap.flexmail.eu/3.0.0/flexmail.php");


// Toggle debug mode. 
// If debug mode is on, the response header, errorCode and errorMessage will be 
//                                                          returned as an object
// 
// If debug mode is off, methods that provide a relevant response parameter
//                                          (like getters) will return them
//                                                          
define("DEBUG_MODE", true);
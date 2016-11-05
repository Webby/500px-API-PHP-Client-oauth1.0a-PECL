<?php
/**
* \author Anthony Desvernois
* \date 05/11/2016
* \brief Example of use of the API client
*
*/

session_start();

require_once('API500px.class.php');

if (isset($_REQUEST['logout'])) {
   session_destroy();
   session_start();
}											       

if ((!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) && isset($_REQUEST['oauth_token']) && isset($_REQUEST['oauth_verifier']) && isset($_SESSION['oauth_token_secret'])) {
   // Callback situation - getting the accessToken
   try {
       $accessToken = API500px::getAccessToken($_REQUEST['oauth_token'], $_SESSION['oauth_token_secret'], $_REQUEST['oauth_verifier']);
       $_SESSION['oauth_token'] = $accessToken['oauth_token'];
       $_SESSION['oauth_token_secret'] = $accessToken['oauth_token_secret'];
       $_SESSION['auth'] = 1;
   } catch (Exception $e) {
       echo $e;
   }
}

if (!isset($_SESSION['auth']) || $_SESSION['auth'] != 1) {
   // Request token situation
   try {
       $reqToken = API500px::getRequestToken();
       $_SESSION['oauth_token_secret'] = $reqToken['oauth_token_secret'];
       $_SESSION['oauth_token'] = $reqToken['oauth_token'];
       API500px::getUserAuthorization($reqToken);
   } catch (Exception $e) {
       echo $e;
   }
}

if (isset($_SESSION['auth']) && $_SESSION['auth'] == 1) {
   // AccessToken is present - standard API use
   $api = new API500px($_SESSION['oauth_token'], $_SESSION['oauth_token_secret']);
   try {
       print_r($api->getCurrentUser());
   } catch (Exception $e) {
     echo $e;
   }
}

?>
<?php
/**
* \author Anthony Desvernois
* \date 05/11/2016
* \brief API Client using PHP PECL OAuth class, compatible with 500px API and OAuth1.0a scheme
*
*/

require_once('config.php');

class API500px {
      private $oauth = null;
      private $accessToken = null;
      private $accessTokenSecret = null;

      /**
      * \fn getRequestToken() Static function, used to get the requestToken from the API provider
      * \return requestToken from the API provider
      */
      public static function getRequestToken() {
	     $oauth = new OAuth(CONSUMER_KEY, CONSUMER_SECRET);
	     $oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
	     $reqToken = $oauth->getRequestToken(REQUEST_TOKEN_URL, CALLBACK);
	     $oauth->setToken($reqToken['oauth_token'], $reqToken['oauth_token_secret']);
	     return $reqToken;
      }

      /**
      * \fn getUserAuthorization() redirect the user to the authorize page
      * \param reqToken
      *
      */
      public static function getUserAuthorization($reqToken) {
      	     header('Location: '.AUTHENTICATE_URL.'?oauth_token='.$reqToken['oauth_token']);
      }

      /**
      * \fn getAccessToken() request the accessToken related to the authorizing user from the API provider
      * \param token token from the requestToken 
      * \param secret token secret from the requestToken
      * \param verifier code sent by the API provider on the callback after user authorization
      * \return accessToken related to the user, from the API provider
      *
      */
      public static function getAccessToken($token, $secret, $verifier) {
	     $oauth = new OAuth(CONSUMER_KEY, CONSUMER_SECRET);
	     $oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
	     $oauth->setToken($token, $secret);
	     $params['consumer_key'] = CONSUMER_KEY;
	     $params['oauth_token'] = $token;
	     $params['oauth_token_secret'] = $secret;
	     $params['auth_signature_method'] = 'HMAC-SHA1';
	     $params['nonce'] = rand(1, 1000000); // \todo to improve
	     $params['timestamp'] = time();
	     $signature = $oauth->generateSignature('GET', ACCESS_TOKEN_URL, $params);
	     $accessToken = $oauth->getAccessToken(ACCESS_TOKEN_URL, $signature, $verifier);
	     return $accessToken;
      }

      /**
      * \fn __construct instantiate the 500px API Client
      * \param accessToken accessToken related to the user
      * \param accessTokenSecret accessTokenSecret related to the user
      *
      */
      public function __construct($accessToken, $accessTokenSecret) {
      	     $this->oauth = new OAuth(CONSUMER_KEY, CONSUMER_SECRET);
	     $this->oauth->setRequestEngine(OAUTH_REQENGINE_CURL);
	     $this->accessToken = $accessToken;
	     $this->accessTokenSecret = $accessTokenSecret;
	     $this->oauth->setToken($this->accessToken, $this->accessTokenSecret);
      }

      /**
      * \fn getCurrentUser() gives current user data on 500px website
      * \return an array containing current user data
      *
      */
      public function getCurrentUser() {
      	     $this->oauth->fetch(HOST.'users');
	     $json = json_decode($this->oauth->getLastResponse(), true);
	     return $json;
      }

      /**
      * \fn getUser() gives user data based on its user id
      * \param uid user id
      * \return an array containing specified user data
      *
      */
      public function getUser($uid) {
      	     $this->oauth->fetch(HOST.sprintf('users/show?id=%d', $uid));
	     $json = json_decode($this->oauth->getLastResponse(), true);
	     return $json;
      }

      /**
      * \fn getUserPicturesID() gives all the public pictures ID from a specified user
      * \param uid user id
      * \return an array containing the public pictures ID of the specified user
      *
      */
      public function getUserPicturesID($uid) {
      	     $pictures = array();
	     $page = 1;
	     do {
      	     	$this->oauth->fetch(HOST.sprintf('photos?feature=user&user_id=%d&rpp=100&page=%d', $uid, $page));
	     	$json = json_decode($this->oauth->getLastResponse(), true);
		$maxPages = $json['total_pages'];
		foreach ($json['photos'] as $photo)
			array_push($pictures, $photo['id']);
	     } while ($page++ < $maxPages);

	     return $pictures;
      }
}

?>
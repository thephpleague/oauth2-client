<?php
namespace League\OAuth2\Client\Provider;

class MailRu extends IdentityProvider {
  public $scope = array();
  public $responseType = 'json';

  public function urlAuthorize() {
    return 'https://connect.mail.ru/oauth/authorize';
  }

  public function urlAccessToken() {
    return 'https://connect.mail.ru/oauth/token';
  }

  public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token) {
    $params = array(
      'method' => 'users.getInfo',
      'secure' => 1,
      'app_id' => $this->clientId,
      'uids' => $token->uid,
      'access_token' => $token->accessToken
    );
    $params['sig'] = $this->sign_server($params, $this->clientSecret);
    return "http://www.appsmail.ru/platform/api?".urldecode(http_build_query($params));
  }

  private function sign_server(array $req_params, $secret_key){
    ksort($req_params);
    $params = '';
    foreach($req_params as $key => $val) {
      $params .= "$key=$val";
    }
    return md5($params . $secret_key);
  }

  public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token) {
    $response = $response[0];

    $user = new User;
    $user->uid = $response->uid;
    $user->nikname = $response->nick;
    $user->name = $response->nick;
    $user->firstName = $response->first_name;
    $user->lastName = $response->last_name;
    $user->email = isset($response->email) ? $response->email : null;
    $user->isVerified = $response->is_verified;
    $user->urls = array(
      'mail.ru' => $response->link
    );

    return $user;
  }

  public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token) {
    return $response->uid;
  }

  public function userEmail($response, \League\OAuth2\Cleint\Token\AccessToken $token) {
    return isset($response->email) && $response->email ? $response->email : null;
  }

  public function userScreenName($response, \League\OAuth2\Cleint\Token\AccessToken $token) {
    return array($response->first_name, $response->last_name);
  }

}

<?php
namespace League\OAuth2\Client\Provider;

class Odnoklassniki extends IdentityProvider 
{
  public $scopes = array('VALUABLE_ACCESS');
  public $responseType = 'json';
  public $clientPublic = '';

  public function urlAuthorize()
  {
    return 'http://www.odnoklassniki.ru/oauth/authorize';
  }
  public function urlAccessToken()
  {
    return 'http://api.odnoklassniki.ru/oauth/token.do';
  }
  public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
  {
    $params = array(
      'method'            => 'users.getCurrentUser',
      'application_key'   => $this->clientPublic,
    );
    $params['sig'] = $this->sign_server($params, $token->accessToken, $this->clientSecret);
    $params['access_token'] = $token->accessToken;

    return 'http://api.odnoklassniki.ru/fb.do?'.urldecode(
      http_build_query($params)
    );
  }

  private function sign_server($req_params, $token, $secret_key)
  {
    ksort($req_params);
    $params = '';
    foreach($req_params as $key => $val)
    {
      $params .= "$key=$val";
    }
    return md5($params.md5($token.$secret_key));
  }
  public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
  {
    $user = new User;
    $user->uid = $response->uid;
    $user->nikname = $response->name;
    $user->name = $response->name;
    $user->firstName = $response->first_name;
    $user->lastName = $response->last_name;
    $user->email = null;
    $user->isVerified = NULL;
    $user->imageUrl = $response->pic_3;
    $user->urls = array(
    );
    return $user;
  }

  public function userUid($response, $token)
  {
    return $reponse->uid;
  }
  public function userEmail($response, $token)
  {
    return NULL;
  }
  public function userScreenName($response, $token) {
    return array($response->first_name, $response->last_name);
  }
}

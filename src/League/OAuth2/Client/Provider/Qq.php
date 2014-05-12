<?php

namespace League\OAuth2\Client\Provider;

class Qq extends IdentityProvider
{
    public $scope = array('get_user_info');
    public $responseType = 'string';

    public $name = "qq";

    private $openid;

    public function urlAuthorize()
    {
        return 'https://graph.qq.com/oauth2.0/authorize';
    }

    public function urlAccessToken()
    {
        return 'https://graph.qq.com/oauth2.0/token';
    }

    public function urlUserDetails(\League\OAuth2\Client\Token\AccessToken $token)
    {
        return 'https://graph.qq.com/oauth2.0/me?access_token=' . $token;
    }

    public function getUserDetails(\League\OAuth2\Client\Token\AccessToken $token, $force = false)
    {
        $openid_response = $this->fetchUserDetails($token);

        // the response of QQ openAPI need special handling
        //
        // eg: "callback( {"client_id":"101083709","openid":"A201E2458192683555A3069B59FDF47C"} );
        // "

        // transform the response of request openid
        $first_open_brace_pos = strpos($openid_response, '{');
        $last_close_brace_pos = strrpos($openid_response, '}');
        $openid_response = json_decode(substr(
            $openid_response,
            $first_open_brace_pos,
            $last_close_brace_pos - $first_open_brace_pos + 1
        ));
        $this->openid = $openid_response->openid;

        // fetch QQ user profile
        $params = array(
            'access_token' => $token,
            'oauth_consumer_key' => $this->clientId,
            'openid' => $openid_response->openid
        );

        $response = $this->httpClient->get('https://graph.qq.com/user/get_user_info?' . http_build_query($params));

        // check response
        if (is_array($response) && (isset($response['error']) || isset($response['message']))) {
            throw new \League\OAuth2\Client\Exception\IDPException($response);
        }

        $response = json_decode($response);

        if (!isset($response->ret) || $response->ret != 0)
        {
            $result['code'] = $response->ret;
            $result['message'] = $response->msg;

            throw new \League\OAuth2\Client\Exception\IDPException($result);
        }

        return $this->userDetails($response, $token);
    }

    public function userDetails($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        $user = new User;

        $user->uid = $this->openid;
        $user->nickname = isset($response->nickname) && $response->nickname ? $response->nickname : null;
        $user->name  = isset($response->nickname) && $response->nickname ? $response->nickname : null;
        $user->email = null;
        $user->imageUrl = isset($response->figureurl) && $response->figureurl ? $response->figureurl : null;
        $user->gender = isset($response->gender) && $response->gender ? $response->gender : null;

        return $user;
    }

    public function userUid($response, \League\OAuth2\Client\Token\AccessToken $token)
    {
        return $response->openid;
    }
}

<?php

class ProviderTest extends PHPUnit_Framework_TestCase {

	private $stub;
	private $httpClientMock;


	public function setUp()
	{
		$this->httpClientMock = $this->getMock('League\OAuth2\Client\HttpClient\GuzzleHttpClient');

		$this->stub = $this->getMockForAbstractClass(
			'League\OAuth2\Client\Provider\IdentityProvider',
			array($this->httpClientMock, $this->prepareOptions())
		);
	}


	public function testGetAuthorizationUrlWithEmptyOptions()
	{
		$this->stub->expects($this->once())
			->method('urlAuthorize')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/auth'));

		$result = $this->stub->getAuthorizationUrl();
		$result = preg_split('@\?@', $result, 2);
		$url = $result[0];
		parse_str($result[1], $params);

		$this->assertEquals("https://accounts.google.com/o/oauth2/auth", $url);
		$this->assertEquals('12345678.apps.googleusercontent.com', $params['client_id']);
		$this->assertEquals('http://www.test.com/oauth/google/callback', $params['redirect_uri']);
		$this->assertEquals('code', $params['response_type']);
		$this->assertEquals(
			'https://www.googleapis.com/auth/plus.me,https://www.googleapis.com/auth/plus.login',
			$params['scope']
		);
	}


	public function testGetAuthorizationUrlWithSomeOptions()
	{
		$this->stub->expects($this->once())
			->method('urlAuthorize')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/auth'));

		$options = array(
			'response_type' => 'other_code',
		);
		$result = $this->stub->getAuthorizationUrl($options);
		$result = preg_split('@\?@', $result, 2);
		$url = $result[0];
		parse_str($result[1], $params);

		$this->assertEquals('other_code', $params['response_type']);
		$this->assertEquals("https://accounts.google.com/o/oauth2/auth", $url);
		$this->assertEquals('12345678.apps.googleusercontent.com', $params['client_id']);
		$this->assertEquals('http://www.test.com/oauth/google/callback', $params['redirect_uri']);
		$this->assertEquals(
			'https://www.googleapis.com/auth/plus.me,https://www.googleapis.com/auth/plus.login',
			$params['scope']
		);
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetAccessTokenWithUnknowGrantArgs()
	{
		// unknow grant
		$this->stub->getAccessToken('unknow_code');
	}


	/**
	 * @expectedException InvalidArgumentException
	 */
	public function testGetAccessTokenWithNotInstanceOfGrantInterfaceArgs()
	{
		// not an instance of League\OAuth2\Client\Grant\GrantInterface
		$this->stub->getAccessToken(new ProviderTest);
	}


	/**
	 * @expectedException BadMethodCallException
	 */
	public function testGetAccessTokenWithEmpty()
	{
		$this->stub->getAccessToken();
	}


	public function testGetAccessTokenUseHttpClientGetMethod()
	{
		$this->httpClientMock->expects($this->once())
			->method('get')
			->with($this->isType('string'))
			->will($this->returnValue($this->prepareResponse()));

		$this->stub->expects($this->once())
			->method('urlAccessToken')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->stub->method = 'get';
		$result = $this->stub->getAccessToken('authorization_code', array('code' => 'dddddsjdkfds'));

		$this->assertEquals('1/fFAGRNJru1FTz70BzhT3Zg', $result->accessToken);
		$this->assertAttributeNotEmpty('expires', $result);
		$this->assertEquals('Bearer', $result->tokenType);
	}


	public function testGetAccessTokenUseHttpClientPostMethod()
	{
		$this->httpClientMock->expects($this->once())
			->method('post')
			->with($this->isType('string'), null, $this->isType('array'))
			->will($this->returnValue($this->prepareResponse()));

		$this->stub->expects($this->once())
			->method('urlAccessToken')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->stub->method = 'post';
		$result = $this->stub->getAccessToken('authorization_code', array('code' => 'dddddsjdkfds'));

		$this->assertEquals('1/fFAGRNJru1FTz70BzhT3Zg', $result->accessToken);
		$this->assertAttributeNotEmpty('expires', $result);
		$this->assertEquals('Bearer', $result->tokenType);
	}


	/**
	 * @expectedException League\OAuth2\Client\Exception\IDPException
	 */
	public function testGetAccessTokenReturnBadResponseAndThrowIDPException()
	{
		$this->httpClientMock->expects($this->once())
			->method('post')
			->with($this->isType('string'), null, $this->isType('array'))
			->will($this->returnValue($this->prepareBadResponse()));

		$this->stub->expects($this->once())
			->method('urlAccessToken')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->stub->method = 'post';
		$result = $this->stub->getAccessToken('authorization_code', array('code' => 'dddddsjdkfds'));
	}

	/**
	 * @expectedException League\OAuth2\Client\Exception\IDPException
	 */
	public function testGetAccessTokenReturnCurlExceptionAndThrowIDPException()
	{
		$this->httpClientMock->expects($this->once())
			->method('post')
			->with($this->isType('string'), null, $this->isType('array'))
			->will($this->returnValue($this->prepareCurlException()));

		$this->stub->expects($this->once())
			->method('urlAccessToken')
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->stub->method = 'post';
		$result = $this->stub->getAccessToken('authorization_code', array('code' => 'dddddsjdkfds'));
	}



	public function testFetchUserDetailsWithCachedUserDetailsResponse()
	{
		$access_token_mock = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
			->disableOriginalConstructor()
			->getMock();

		$this->stub->expects($this->once())
			->method('urlUserDetails')
			->with($access_token_mock)
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->httpClientMock->expects($this->once())
			->method('get')
			->with($this->isType("string"))
			->will($this->returnValue("user_info"));

		$this->stub->fetchUserDetails($access_token_mock);
		$result = $this->stub->fetchUserDetails($access_token_mock);

		$this->assertEquals("user_info", $result);
	}


	public function testFetchUserDetailsWithNoCachedUserDetailsResponse()
	{

		$access_token_mock = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
			->disableOriginalConstructor()
			->getMock();

		$this->stub->expects($this->once())
			->method('urlUserDetails')
			->with($access_token_mock)
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->httpClientMock->expects($this->once())
			->method('get')
			->with($this->isType("string"))
			->will($this->returnValue("user_info"));


		$result = $this->stub->fetchUserDetails($access_token_mock);

		$this->assertEquals("user_info", $result);
	}


	/**
	 * @expectedException League\OAuth2\Client\Exception\IDPException
	 */
	public function testFetchUserDetailsReturnBadResponseAndThrowIDPException()
	{
		$access_token_mock = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
			->disableOriginalConstructor()
			->getMock();

		$this->stub->expects($this->once())
			->method('urlUserDetails')
			->with($access_token_mock)
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->httpClientMock->expects($this->once())
			->method('get')
			->with($this->isType("string"))
			->will($this->returnValue($this->prepareBadResponse()));

		$this->stub->fetchUserDetails($access_token_mock);
	}


	/**
	 * @expectedException League\OAuth2\Client\Exception\IDPException
	 */
	public function testFetchUserDetailsReturnCurlExceptionAndThrowIDPException()
	{
		$access_token_mock = $this->getMockBuilder('League\OAuth2\Client\Token\AccessToken')
			->disableOriginalConstructor()
			->getMock();

		$this->stub->expects($this->once())
			->method('urlUserDetails')
			->with($access_token_mock)
			->will($this->returnValue('https://accounts.google.com/o/oauth2/token'));

		$this->httpClientMock->expects($this->once())
			->method('get')
			->with($this->isType("string"))
			->will($this->returnValue($this->prepareCurlException()));

		$this->stub->fetchUserDetails($access_token_mock);
	}


	private function prepareOptions()
	{
		return array(
			'clientId'     => '12345678.apps.googleusercontent.com',
			'clientSecret' => 'aaaaaaaaaaaaaaaaaa',
			'redirectUri'  => 'http://www.test.com/oauth/google/callback',
			'scopes'       => array(
				'https://www.googleapis.com/auth/plus.me',
				'https://www.googleapis.com/auth/plus.login'
			),
			'acess_type'   => 'offline'
		);
	}


	private function prepareResponse()
	{
		$response =  <<<EOD
{
  "access_token":"1/fFAGRNJru1FTz70BzhT3Zg",
  "expires_in":3920,
  "token_type":"Bearer"
}
EOD;
		return $response;
	}

	private function prepareCurlException()
	{
		return array(
			'message' => 'Curl Exception'
		);
	}


	private function prepareBadResponse()
	{
		return array(
			'error' => 'Bad Response',
			'code'  => '401'
		);
	}


	public function __toString()
	{
		return "ProviderTest";
	}
}

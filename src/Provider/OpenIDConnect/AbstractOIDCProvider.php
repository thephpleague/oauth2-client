<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use League\OAuth2\Client\Provider\OpenIDConnect\Exception\TokenIntrospectionException;

abstract class AbstractOIDCProvider extends AbstractProvider
{
    const OPTION_WELL_KNOWN_URL = 'well_known_endpoint';
    const OPTION_PUBLICKEY_CACHE_PROVIDER = 'publickey_cache_provider';

    protected $OIDCDiscovery;
    
    public function __construct(array $options, array $collaborators = [])
    {
        $this->assertRequiredOptions($options);
        
        parent::__construct($options, $collaborators);
        
        $this->OIDCDiscovery = new Discovery($this, $options[self::OPTION_WELL_KNOWN_URL], $options[self::OPTION_PUBLICKEY_CACHE_PROVIDER]);
    }
    
    /**
     * Proxy to \League\OAuth2\Client\Provider\OpenIDConnect\Discovery
     * 
     * @return \League\OAuth2\Client\Provider\OpenIDConnect\Discovery
     */
    public function Discovery()
    {
        return $this->OIDCDiscovery;
    }

    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\AbstractProvider::getResourceOwnerDetailsUrl()
     */
    public function getResourceOwnerDetailsUrl(AccessTokenInterface $token)
    {
        return $this->OIDCDiscovery->getUserInfoEndpoint();
    }

    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\AbstractProvider::getBaseAuthorizationUrl()
     */
    public function getBaseAuthorizationUrl()
    {
        return $this->OIDCDiscovery->getAuthorizationEndpoint();
    }

    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\AbstractProvider::getBaseAccessTokenUrl()
     */
    public function getBaseAccessTokenUrl(array $params)
    {
        return $this->OIDCDiscovery->getTokenEndpoint();
    }
    
    /**
     * Decode a token
     * 
     * @param AccessTokenInterface $token
     * @throws TokenIntrospectionException
     * @return object|mixed
     */
    public function introspectToken(AccessTokenInterface $token)
    {
        $jwt_allowed_algs = [
            'ES384','ES256', 'HS256', 'HS384', 'HS512', 'RS256', 'RS384', 'RS512'
        ];
        
        $resolved_algs = array_intersect($this->OIDCDiscovery->getUserInfoSigningAlgValuesSupported(), $jwt_allowed_algs);
        
        try {
            return JWT::decode($token->getToken(), JWK::parseKeySet($this->OIDCDiscovery->getPublicKey()), $resolved_algs);
        } catch (\Exception $e){
            throw new TokenIntrospectionException($e->getMessage(), null, $e);
        }
    }
    
    protected function getRequiredOptions()
    {
        return [
            self::OPTION_WELL_KNOWN_URL,
            self::OPTION_PUBLICKEY_CACHE_PROVIDER
        ];
    }
    
    /**
     * Verifies that all required options have been passed.
     *
     * @param  array $options
     * @return void
     * @throws InvalidArgumentException
     */
    private function assertRequiredOptions(array $options)
    {
        $missing = array_diff_key(array_flip($this->getRequiredOptions()), $options);
        
        if (!empty($missing)) {
            throw new \InvalidArgumentException(
                'Required options not defined: ' . implode(', ', array_keys($missing))
                );
        }
    }
}

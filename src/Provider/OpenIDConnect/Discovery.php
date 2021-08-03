<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect;

use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\OpenIDConnect\Exception\InvalidUrlException;
use League\OAuth2\Client\Provider\OpenIDConnect\Exception\WellKnownEndpointException;
use League\OAuth2\Client\Provider\OpenIDConnect\Exception\CertificateEndpointException;

class Discovery
{
    protected $Provider;
    protected $wellKnownConfiguration;
    protected $PublicKeyCache;
    
    public function __construct(AbstractProvider $Provider, string $wellKnownUrl, PublicKeyCacheInterface $PublicKeyCache)
    {
        $this->Provider = $Provider;
        
        // Check is well-known URL has a valid URL format
        if(!filter_var($wellKnownUrl, FILTER_VALIDATE_URL)) 
            throw new InvalidUrlException($wellKnownUrl);
        
        // Build the HTTPRequest
        $HttpRequest = $Provider->getRequestFactory()->getRequest(AbstractProvider::METHOD_GET, $wellKnownUrl);
        
        // Execute discovery request
        $HttpResponse = $Provider->getResponse($HttpRequest);
            
        if ($HttpResponse->getStatusCode() == "200")
        {
            $this->wellKnownConfiguration = (object) json_decode((string) $HttpResponse->getBody());
        }
        else
        {
            throw new WellKnownEndpointException($HttpResponse->getReasonPhrase(), $HttpResponse->getStatusCode());
        }

        $this->PublicKeyCache = $PublicKeyCache;
        
        $this->getPublicKey();
    }
    
    /**
     * Get the well-known configuration as an object
     * 
     * @return object
     */
    public function getWellKnownConfiguration()
    {
        return $this->wellKnownConfiguration;
    }
    
    /**
     * Get the public key (either from cache or the server)
     * 
     * @param array $options - Options for the implemented caching mechanism
     * @param boolean $bypassCache
     * 
     * @return array
     */
    public function getPublicKey(array $options = [], $bypassCache = false)
    {
        if ($bypassCache)
        {
            $this->PublicKeyCache->clear($options);
        } 
        else 
        {
            $loadedPublicKey = $this->PublicKeyCache->load($options);
            if ($loadedPublicKey)
            {
                return $loadedPublicKey;
            }
        }
        
        return $this->fetchPublicKeyFromServer();
    }
    
    /**
     * Clear the cached public keys
     * 
     * @param array $options
     * @return \League\OAuth2\Client\Provider\OpenIDConnect\Discovery
     */
    public function clearPublicKeyCache(array $options = [])
    {
        $this->PublicKeyCache->clear($options);
        
        return $this;
    }
    
    /**
     * Get the public keys from the server 
     * 
     * @throws CertificateEndpointException
     * @return array
     */
    protected function fetchPublicKeyFromServer()
    {
        $HttpRequest = $this->Provider->getRequestFactory()->getRequest(AbstractProvider::METHOD_GET, $this->getJwksUri());
        
        $HttpResponse = $this->Provider->getResponse($HttpRequest);
        
        if ($HttpResponse->getStatusCode() == "200")
        {
            $keys = (array) json_decode((string) $HttpResponse->getBody(), true);
            
            $this->PublicKeyCache->save($keys);
            
            return $keys;
        }
        else
        {
            throw new CertificateEndpointException($HttpResponse->getReasonPhrase(), $HttpResponse->getStatusCode());
        }
    }
    
    /*
     * The following getters are as per OpenID Connect Discovery Specification
     * 
     * @see https://openid.net/specs/openid-connect-discovery-1_0.html#ProviderMetadata
     */
    
    /**
     * REQUIRED. URL using the https scheme with no query or fragment component that the OP asserts as its Issuer Identifier. 
     * This also MUST be identical to the iss Claim value in ID Tokens issued from this Issuer.
     * 
     * @return string
     */
    public function getIssuer()
    {
        return $this->issuer;
    }
    
    /**
     * REQUIRED. URL of the OP's OAuth 2.0 Authorization Endpoint
     * 
     * @return string
     */
    public function getAuthorizationEndpoint()
    {
        return $this->authorization_endpoint;
    }
    
    /**
     * URL of the OP's OAuth 2.0 Token Endpoint [OpenID.Core]. 
     * This is REQUIRED unless only the Implicit Flow is used.
     * 
     * @return string
     */
    public function getTokenEndpoint()
    {
        return $this->token_endpoint;
    }
    
    /**
     * RECOMMENDED. URL of the OP's UserInfo Endpoint [OpenID.Core]. 
     * This URL MUST use the https scheme and MAY contain port, path, and query parameter components.
     * 
     * @return string | null
     */    
    public function getUserInfoEndpoint()
    {
        return $this->userinfo_endpoint;
    }
    
    /**
     * REQUIRED. URL of the OP's JSON Web Key Set [JWK] document. 
     * This contains the signing key(s) the RP uses to validate signatures from the OP. 
     * The JWK Set MAY also contain the Server's encryption key(s), which are used by RPs to encrypt requests to the Server. 
     * When both signing and encryption keys are made available, a use (Key Use) parameter value is REQUIRED for all keys in the referenced JWK Set to indicate each key's intended usage. 
     * Although some algorithms allow the same key to be used for both signatures and encryption, doing so is NOT RECOMMENDED, as it is less secure. 
     * The JWK x5c parameter MAY be used to provide X.509 representations of keys provided. 
     * When used, the bare key values MUST still be present and MUST match those in the certificate.
     * 
     * @return string
     */
    public function getJwksUri()
    {
        return $this->jwks_uri;
    }
    
    /**
     * RECOMMENDED. URL of the OP's Dynamic Client Registration Endpoint [OpenID.Registration].
     * 
     * @return string | null
     */
    public function getRegistrationEndpoint()
    {
        return $this->registration_endpoint;
    }
    
    /**
     * RECOMMENDED. JSON array containing a list of the OAuth 2.0 [RFC6749] scope values that this server supports. 
     * The server MUST support the openid scope value. 
     * Servers MAY choose not to advertise some supported scope values even when this parameter is used, although those defined in [OpenID.Core] SHOULD be listed, if supported.
     * 
     * @return string
     */
    public function getScopesSupported()
    {
        return $this->scopes_supported;
    }
    
    /**
     * REQUIRED. JSON array containing a list of the OAuth 2.0 response_type values that this OP supports. 
     * Dynamic OpenID Providers MUST support the code, id_token, and the token id_token Response Type values.
     * 
     * @return string
     */
    public function getResponseTypesSupported()
    {
        return $this->response_types_supported;
    }    
    
    /**
     * OPTIONAL. JSON array containing a list of the OAuth 2.0 response_mode values that this OP supports, as specified in OAuth 2.0 Multiple Response Type Encoding Practices [OAuth.Responses]. 
     * If omitted, the default for Dynamic OpenID Providers is ["query", "fragment"].
     * 
     * @return array | null
     */
    public function getResponseModesSupported()
    {
        return $this->response_modes_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the OAuth 2.0 Grant Type values that this OP supports. Dynamic OpenID Providers MUST support the authorization_code and implicit Grant Type values and MAY support other Grant Types. 
     * If omitted, the default value is ["authorization_code", "implicit"].
     * 
     * @return array | null
     */
    public function getGrantTypesSupported()
    {
        return $this->grant_types_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the Authentication Context Class References that this OP supports.
     * 
     * @return array | null
     */
    public function getArcValuesSupported()
    {
        return $this->acr_values_supported;
    }
    
    /**
     * REQUIRED. JSON array containing a list of the Subject Identifier types that this OP supports. Valid types include pairwise and public.
     * 
     * @return array
     */
    public function getSubjectTypesSupported()
    {
        return $this->subject_types_supported;
    }
    
    /**
     * REQUIRED. JSON array containing a list of the JWS signing algorithms (alg values) supported by the OP for the ID Token to encode the Claims in a JWT [JWT]. 
     * The algorithm RS256 MUST be included. 
     * The value none MAY be supported, but MUST NOT be used unless the Response Type used returns no ID Token from the Authorization Endpoint (such as when using the Authorization Code Flow).
     * 
     * @return array
     */
    public function getIdTokenSigningAlgValuesSupported()
    {
        return $this->id_token_signing_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE encryption algorithms (alg values) supported by the OP for the ID Token to encode the Claims in a JWT [JWT].
     * 
     * @return array | null
     */
    public function getIdTokenEncryptionAlgValuesSupported()
    {
        return $this->id_token_encryption_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE encryption algorithms (enc values) supported by the OP for the ID Token to encode the Claims in a JWT [JWT].
     * 
     * @return array | null
     */
    public function getIdTokenEncryptionEncValuesSupported()
    {
        return $this->id_token_encryption_enc_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWS [JWS] signing algorithms (alg values) [JWA] supported by the UserInfo Endpoint to encode the Claims in a JWT [JWT]. The value none MAY be included.
     * 
     * @return array | null
     */
    public function getUserInfoSigningAlgValuesSupported()
    {
        return $this->userinfo_signing_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE [JWE] encryption algorithms (alg values) [JWA] supported by the UserInfo Endpoint to encode the Claims in a JWT [JWT].
     * 
     * @return array | null
     */
    public function getUserInfoEncryptionAlgValuesSupported()
    {
        return $this->userinfo_encryption_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE encryption algorithms (enc values) [JWA] supported by the UserInfo Endpoint to encode the Claims in a JWT [JWT].
     * 
     * @return array | null
     */
    public function getUserInfoEncryptionEncValuesSupported()
    {
        return $this->userinfo_encryption_enc_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWS signing algorithms (alg values) supported by the OP for Request Objects, which are described in Section 6.1 of OpenID Connect Core 1.0 [OpenID.Core]. 
     * These algorithms are used both when the Request Object is passed by value (using the request parameter) and when it is passed by reference (using the request_uri parameter). 
     * Servers SHOULD support none and RS256.
     * 
     * @return array | null
     */
    public function getRequestObjectSigningAlgValuesSupported()
    {
        return $this->request_object_signing_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE encryption algorithms (alg values) supported by the OP for Request Objects. 
     * These algorithms are used both when the Request Object is passed by value and when it is passed by reference.
     * 
     * @return array | null
     */
    public function getRequestObjectEncryptionAlgValuesSupported()
    {
        return $this->request_object_encryption_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWE encryption algorithms (enc values) supported by the OP for Request Objects. 
     * These algorithms are used both when the Request Object is passed by value and when it is passed by reference.
     * 
     * @return array | null
     */
    public function getRequestObjectEncryptionEncValuesSupported()
    {
        return $this->request_object_encryption_enc_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of Client Authentication methods supported by this Token Endpoint. 
     * The options are client_secret_post, client_secret_basic, client_secret_jwt, and private_key_jwt, as described in Section 9 of OpenID Connect Core 1.0 [OpenID.Core]. 
     * Other authentication methods MAY be defined by extensions. 
     * If omitted, the default is client_secret_basic -- the HTTP Basic Authentication Scheme specified in Section 2.3.1 of OAuth 2.0 [RFC6749].
     * 
     * @return array | null
     */
    public function getTokenEndpointAuthMethodsSupported()
    {
        return $this->token_endpoint_auth_methods_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the JWS signing algorithms (alg values) supported by the Token Endpoint for the signature on the JWT [JWT] used to authenticate the Client at the Token Endpoint for the private_key_jwt and client_secret_jwt authentication methods. 
     * Servers SHOULD support RS256. The value none MUST NOT be used.
     * 
     * @return array | null
     */
    public function getTokenEndpointAuthSigningAlgValuesSupported()
    {
        return $this->token_endpoint_auth_signing_alg_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the display parameter values that the OpenID Provider supports. 
     * These values are described in Section 3.1.2.1 of OpenID Connect Core 1.0 [OpenID.Core].
     * 
     * @return array | null
     */
    public function getDisplayValuesSupported()
    {
        return $this->display_values_supported;
    }
    
    /**
     * OPTIONAL. JSON array containing a list of the Claim Types that the OpenID Provider supports. 
     * These Claim Types are described in Section 5.6 of OpenID Connect Core 1.0 [OpenID.Core]. 
     * Values defined by this specification are normal, aggregated, and distributed. 
     * If omitted, the implementation supports only normal Claims.
     * 
     * @return array | null
     */
    public function getClaimTypesSupported()
    {
        return $this->claim_types_supported;
    }
    
    /**
     * RECOMMENDED. JSON array containing a list of the Claim Names of the Claims that the OpenID Provider MAY be able to supply values for. 
     * Note that for privacy or other reasons, this might not be an exhaustive list.
     * 
     * @return array | null
     */
    public function getClaimsSupported()
    {
        return $this->claims_supported;
    }
    
    /**
     * OPTIONAL. URL of a page containing human-readable information that developers might want or need to know when using the OpenID Provider. 
     * In particular, if the OpenID Provider does not support Dynamic Client Registration, then information on how to register Clients needs to be provided in this documentation.
     * 
     * @return string | null
     */
    public function getServiceDocumentation()
    {
        return $this->service_documentation;
    }
    
    /**
     * OPTIONAL. Languages and scripts supported for values in Claims being returned, represented as a JSON array of BCP47 [RFC5646] language tag values. 
     * Not all languages and scripts are necessarily supported for all Claim values.
     * 
     * @return array | null
     */
    public function getClaimsLocalesSupported()
    {
        return $this->claims_locales_supported;
    }
    
    /**
     * OPTIONAL. Languages and scripts supported for the user interface, represented as a JSON array of BCP47 [RFC5646] language tag values.
     * 
     * @return array | null
     */
    public function getUiLocalesSupported()
    {
        return $this->ui_locales_supported;
    }
    
    /**
     * OPTIONAL. Boolean value specifying whether the OP supports use of the claims parameter, with true indicating support. 
     * If omitted, the default value is false.
     * 
     * @return boolean | null
     */
    public function getClaimsParameterSupported()
    {
        return $this->claims_parameter_supported;
    }
    
    /**
     * OPTIONAL. Boolean value specifying whether the OP supports use of the request parameter, with true indicating support. 
     * If omitted, the default value is false.
     * 
     * @return boolean | null
     */
    public function getRequestParameterSupported()
    {
        return $this->request_parameter_supported;
    }
    
    /**
     * OPTIONAL. Boolean value specifying whether the OP supports use of the request_uri parameter, with true indicating support. 
     * If omitted, the default value is true.
     * 
     * @return boolean | null
     */
    public function getRequestUriParameterSupported()
    {
        return $this->request_uri_parameter_supported;
    }
    
    /**
     * OPTIONAL. Boolean value specifying whether the OP requires any request_uri values used to be pre-registered using the request_uris registration parameter. 
     * Pre-registration is REQUIRED when the value is true.
     * If omitted, the default value is false.
     * @return boolean | null
     */
    public function getRequestUriRegistration()
    {
        return $this->require_request_uri_registration;
    }
    
    /**
     * OPTIONAL. URL that the OpenID Provider provides to the person registering the Client to read about the OP's requirements on how the Relying Party can use the data provided by the OP. 
     * The registration process SHOULD display this URL to the person registering the Client if it is given.
     * 
     * @return string | null
     */
    public function getOpPolicyUri()
    {
        return $this->op_policy_uri;
    }
    
    /**
     * OPTIONAL. URL that the OpenID Provider provides to the person registering the Client to read about OpenID Provider's terms of service. 
     * The registration process SHOULD display this URL to the person registering the Client if it is given.
     * 
     * @return string | null
     */
    public function getTosUri()
    {
        return $this->op_tos_uri;
    }
    
    /**
     * Additional OpenID Provider Metadata parameters MAY also be used. 
     * Some are defined by other specifications, such as OpenID Connect Session Management 1.0 [OpenID.Session].
     *
     * These additional parameters can be retrieved (if available) by class overloading.
     * 
     * @param string $property
     * @return mixed 
     */
    public function __get($property)
    {
        if (property_exists($this->wellKnownConfiguration, $property))
        {
            return $this->wellKnownConfiguration->$property;
        }
        
        return null;
    }
    
    public function __toString()
    {
        return json_encode($this->wellKnownConfiguration);
    }
}

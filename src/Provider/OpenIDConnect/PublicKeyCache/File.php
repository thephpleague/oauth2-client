<?php
namespace League\OAuth2\Client\Provider\OpenIDConnect\PublicKeyCache;

use League\OAuth2\Client\Provider\OpenIDConnect\PublicKeyCacheInterface;

/**
 * Simple filesystem cache for the JWK
 */
class File implements PublicKeyCacheInterface
{
    const FILENAME_EXT = '.-jwks';
    protected $filename;
    
    public function __construct($filename)
    {
        $this->filename = $filename.self::FILENAME_EXT;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\OpenIDConnect\PublicKeyCacheInterface::save()
     */
    public function save($JWK, array $options = [])
    {
        return file_put_contents($this->filename, serialize($JWK));
    }

    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\OpenIDConnect\PublicKeyCacheInterface::load()
     */
    public function load(array $options = [])
    {
        if (file_exists($this->filename))
        {
            return unserialize(file_get_contents($this->filename));
        }
        
        return false;
    }
    
    /**
     * 
     * {@inheritDoc}
     * @see \League\OAuth2\Client\Provider\OpenIDConnect\PublicKeyCacheInterface::clear()
     */
    public function clear(array $options = [])
    {
        if (file_exists($this->filename))
        {
            return unlink($this->filename);
        }
        
        return false;
    }
}


<?php

/**
 * This file is part of the league/oauth2-client library
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 * @link http://thephpleague.com/oauth2-client/ Documentation
 * @link https://packagist.org/packages/league/oauth2-client Packagist
 * @link https://github.com/thephpleague/oauth2-client GitHub
 */

declare(strict_types=1);

namespace League\OAuth2\Client\Provider;

/**
 * Represents a generic resource owner for use with the GenericProvider.
 */
class GenericResourceOwner implements ResourceOwnerInterface
{
    /**
     * @var mixed[]
     */
    protected array $response;

    protected string $resourceOwnerId;

    /**
     * @param mixed[] $response
     */
    public function __construct(array $response, string $resourceOwnerId)
    {
        $this->response = $response;
        $this->resourceOwnerId = $resourceOwnerId;
    }

    /**
     * Returns the identifier of the authorized resource owner.
     */
    public function getId(): mixed
    {
        return $this->response[$this->resourceOwnerId] ?? null;
    }

    /**
     * Returns the raw resource owner response.
     *
     * @return mixed[]
     */
    public function toArray(): array
    {
        return $this->response;
    }
}

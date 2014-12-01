<?php
/**
 * This file is part of the League\OAuth2\Client package.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @copyright Copyright (c) 2014 Alex Bilbie <hello@alexbilbie.com>
 * @license http://opensource.org/licenses/MIT MIT
 */

namespace League\OAuth2\Client\Entity;

/**
 * Represents a user profile in a provider's system
 */
class User
{
    /**
     * Provider's user identifier for this user
     *
     * @var mixed
     */
    protected $uid;

    /**
     * User's profile nickname at the provider
     *
     * @var string
     */
    protected $nickname;

    /**
     * User's profile name at the provider
     *
     * @var string
     */
    protected $name;

    /**
     * User's profile first name at the provider
     *
     * @var string
     */
    protected $firstName;

    /**
     * User's profile last name at the provider
     *
     * @var string
     */
    protected $lastName;

    /**
     * User's profile email address at the provider
     *
     * @var string
     */
    protected $email;

    /**
     * User's profile location at the provider
     *
     * @var string
     */
    protected $location;

    /**
     * User's profile description (bio) at the provider
     *
     * @var string
     */
    protected $description;

    /**
     * User's profile image (avatar) URL at the provider
     *
     * @var string
     */
    protected $imageUrl;

    /**
     * User's profile links at the provider
     *
     * @var string
     */
    protected $urls;

    /**
     * User's profile gender at the provider
     *
     * @var string
     */
    protected $gender;

    /**
     * User's profile locale at the provider
     *
     * @var string
     */
    protected $locale;

    /**
     * Provides retrieval of non-public properties
     *
     * @param string $name Name of the property
     * @return mixed
     */
    public function __get($name)
    {
        if (!property_exists($this, $name)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $name
            ));
        }

        return $this->{$name};
    }

    /**
     * Provides setting of non-public properties
     *
     * @param string $property Name of the property
     * @param mixed $value Value to set for property
     * @return self
     */
    public function __set($property, $value)
    {
        if (!property_exists($this, $property)) {
            throw new \OutOfRangeException(sprintf(
                '%s does not contain a property by the name of "%s"',
                __CLASS__,
                $property
            ));
        }

        $this->$property = $value;

        return $this;
    }

    /**
     * Checks whether properties exist on this object
     *
     * @param string $name Name of the property
     * @return bool
     */
    public function __isset($name)
    {
        return (property_exists($this, $name));
    }

    /**
     * Returns an array of this user's properties
     *
     * @return array
     */
    public function getArrayCopy()
    {
        return [
            'uid' => $this->uid,
            'nickname' => $this->nickname,
            'name' => $this->name,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'email' => $this->email,
            'location' => $this->location,
            'description' => $this->description,
            'imageUrl' => $this->imageUrl,
            'urls' => $this->urls,
            'gender' => $this->gender,
            'locale' => $this->locale,
        ];
    }

    /**
     * Sets this user's properties using an array of data
     *
     * @see getArrayCopy()
     * @param array $data Array of user properties and values to set
     * @return self
     */
    public function exchangeArray(array $data)
    {
        foreach ($data as $key => $value) {
            $key = strtolower($key);
            switch ($key) {
                case 'uid':
                    $this->uid = $value;
                    break;
                case 'nickname':
                    $this->nickname = $value;
                    break;
                case 'name':
                    $this->name = $value;
                    break;
                case 'firstname':
                    $this->firstName = $value;
                    break;
                case 'lastname':
                    $this->lastName = $value;
                    break;
                case 'email':
                    $this->email = $value;
                    break;
                case 'location':
                    $this->location = $value;
                    break;
                case 'description':
                    $this->description = $value;
                    break;
                case 'imageurl':
                    $this->imageUrl = $value;
                    break;
                case 'urls':
                    $this->urls = $value;
                    break;
                case 'gender':
                    $this->gender = $value;
                    break;
                case 'locale':
                    $this->locale = $value;
                    break;
            }
        }

        return $this;
    }
}

<?php

namespace Doctrine\SkeletonMapper\Tests\Model;

class Address
{
    /**
     * @var string
     */
    private $address1;

    /**
     * @var string
     */
    private $address2;

    /**
     * @var string
     */
    private $city;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $zip;

    /**
     * @var \Doctrine\SkeletonMapper\Tests\Model\Profile 
     */
    private $profile;

    /**
     * @param \Doctrine\SkeletonMapper\Tests\Model\Profile $profile
     */
    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Gets the value of address1.
     *
     * @return string
     */
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Sets the value of address1.
     *
     * @param string $address1 the address1
     */
    public function setAddress1($address1)
    {
        if ($this->address1 !== $address1) {
            $this->profile->addressChanged('address1', $this->address1, $address1);
            $this->address1 = $address1;
        }
    }

    /**
     * Gets the value of address2.
     *
     * @return string
     */
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Sets the value of address2.
     *
     * @param string $address2 the address2
     */
    public function setAddress2($address2)
    {
        if ($this->address2 !== $address2) {
            $this->profile->addressChanged('address2', $this->address2, $address2);
            $this->address2 = $address2;
        }
    }

    /**
     * Gets the value of city.
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Sets the value of city.
     *
     * @param string $city the city
     */
    public function setCity($city)
    {
        if ($this->city !== $city) {
            $this->profile->addressChanged('city', $this->city, $city);
            $this->city = $city;
        }
    }

    /**
     * Gets the value of state.
     *
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * Sets the value of state.
     *
     * @param string $state the state
     */
    public function setState($state)
    {
        if ($this->state !== $state) {
            $this->profile->addressChanged('state', $this->state, $state);
            $this->state = $state;
        }
    }

    /**
     * Gets the value of zip.
     *
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * Sets the value of zip.
     *
     * @param string $zip the zip
     */
    public function setZip($zip)
    {
        if ($this->zip !== $zip) {
            $this->profile->addressChanged('zip', $this->zip, $zip);
            $this->zip = $zip;
        }
    }

    /**
     * @param \Doctrine\SkeletonMapper\Tests\Model\Profile $profile
     */
    public function setProfile(Profile $profile)
    {
        $this->profile = $profile;
    }
}

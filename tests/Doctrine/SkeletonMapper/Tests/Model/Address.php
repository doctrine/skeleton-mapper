<?php

declare(strict_types=1);

namespace Doctrine\SkeletonMapper\Tests\Model;

class Address
{
    /** @var string */
    private $address1;

    /** @var string|null */
    private $address2;

    /** @var string */
    private $city;

    /** @var string */
    private $state;

    /** @var string */
    private $zip;

    /** @var Profile */
    private $profile;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
    }

    /**
     * Gets the value of address1.
     */
    public function getAddress1(): string
    {
        return $this->address1;
    }

    /**
     * Sets the value of address1.
     *
     * @param string $address1 the address1
     */
    public function setAddress1(string $address1): void
    {
        if ($this->address1 === $address1) {
            return;
        }

        $this->profile->addressChanged('address1', $this->address1, $address1);
        $this->address1 = $address1;
    }

    /**
     * Gets the value of address2.
     */
    public function getAddress2(): string|null
    {
        return $this->address2;
    }

    /**
     * Sets the value of address2.
     *
     * @param string $address2 the address2
     */
    public function setAddress2(string $address2): void
    {
        if ($this->address2 === $address2) {
            return;
        }

        $this->profile->addressChanged('address2', $this->address2, $address2);
        $this->address2 = $address2;
    }

    /**
     * Gets the value of city.
     */
    public function getCity(): string
    {
        return $this->city;
    }

    /**
     * Sets the value of city.
     *
     * @param string $city the city
     */
    public function setCity(string $city): void
    {
        if ($this->city === $city) {
            return;
        }

        $this->profile->addressChanged('city', $this->city, $city);
        $this->city = $city;
    }

    /**
     * Gets the value of state.
     */
    public function getState(): string
    {
        return $this->state;
    }

    /**
     * Sets the value of state.
     *
     * @param string $state the state
     */
    public function setState(string $state): void
    {
        if ($this->state === $state) {
            return;
        }

        $this->profile->addressChanged('state', $this->state, $state);
        $this->state = $state;
    }

    /**
     * Gets the value of zip.
     */
    public function getZip(): string
    {
        return $this->zip;
    }

    /**
     * Sets the value of zip.
     *
     * @param string $zip the zip
     */
    public function setZip(string $zip): void
    {
        if ($this->zip === $zip) {
            return;
        }

        $this->profile->addressChanged('zip', $this->zip, $zip);
        $this->zip = $zip;
    }

    public function setProfile(Profile $profile): void
    {
        $this->profile = $profile;
    }
}

<?php

namespace Gbs\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Country
 *
 * @ORM\Table(name="country")
 * @ORM\Entity(repositoryClass="Gbs\LeaveBundle\Entity\CountryRepository")
 */
class Country
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;


    /**
     * @var float
     *
     * @ORM\Column(name="holiday_credit_per_month", type="float")
     */
    private $holidayCreditPerMonth;

    /**
     * @var float
     *
     * @ORM\Column(name="sick_credit_per_year", type="float")
     */
    private $sickCreditPerYear;

    /**
     * @var string
     *
     * @ORM\Column(name="emails", type="string", length=255)
     */
    private $emails;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    public function __toString() {
        return $this->name;
    }

    /**
     * Set holidayCreditPerMonth
     *
     * @param float $holidayCreditPerMonth
     * @return Country
     */
    public function setHolidayCreditPerMonth($holidayCreditPerMonth)
    {
        $this->holidayCreditPerMonth = $holidayCreditPerMonth;

        return $this;
    }

    /**
     * Get holidayCreditPerMonth
     *
     * @return float
     */
    public function getHolidayCreditPerMonth()
    {
        return $this->holidayCreditPerMonth;
    }

    /**
     * Set sickCreditPerYear
     *
     * @param float $sickCreditPerYear
     *
     * @return Country
     */
    public function setSickCreditPerYear($sickCreditPerYear)
    {
        $this->sickCreditPerYear = $sickCreditPerYear;

        return $this;
    }

    /**
     * Get sickCreditPerYear
     *
     * @return float
     */
    public function getSickCreditPerYear()
    {
        return $this->sickCreditPerYear;
    }

    /**
     * Set emails
     *
     * @param string $emails
     *
     * @return Country
     */
    public function setEmails($emails)
    {
        $this->emails = $emails;

        return $this;
    }

    /**
     * Get emails
     *
     * @return string
     */
    public function getEmails()
    {
        return $this->emails;
    }
}

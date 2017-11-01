<?php

namespace Gbs\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * PublicHoliday
 *
 * @ORM\Table(name="publicholiday")
 * @ORM\Entity(repositoryClass="Gbs\LeaveBundle\Entity\PublicHolidayRepository")
 */
class PublicHoliday
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\Country")
     * @ORM\JoinColumn(nullable=false)
     */
    private $country;

    public function __construct() {
        $this->date = new \DateTime();
    }

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
     * Set date
     *
     * @param \DateTime $date
     * @return PublicHoliday
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime 
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set country
     *
     * @param \Gbs\LeaveBundle\Entity\Country $country
     * @return PublicHoliday
     */
    public function setCountry(\Gbs\LeaveBundle\Entity\Country $country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return \Gbs\LeaveBundle\Entity\Country 
     */
    public function getCountry()
    {
        return $this->country;
    }
}

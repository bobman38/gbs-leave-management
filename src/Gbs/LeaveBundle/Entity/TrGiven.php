<?php

namespace Gbs\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * TrGiven
 *
 * @ORM\Table(name="trgiven")
 * @ORM\Entity(repositoryClass="Gbs\LeaveBundle\Entity\TrGivenRepository")
 */
class TrGiven
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
     * @ORM\Column(name="month", type="string", length=10)
     * @Assert\Regex("/^\d{4}-\d{2}$/")
     */
    private $month;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var integer
     *
     * @ORM\Column(name="number", type="integer")
     */
    private $number;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

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
     * Set month
     *
     * @param string $month
     * @return TrGiven
     */
    public function setMonth($month)
    {
        $this->month = $month;

        return $this;
    }

    /**
     * Get month
     *
     * @return string 
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     * @return TrGiven
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
     * Set number
     *
     * @param integer $number
     * @return TrGiven
     */
    public function setNumber($number)
    {
        $this->number = $number;

        return $this;
    }

    /**
     * Get number
     *
     * @return integer 
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set user
     *
     * @param \Gbs\LeaveBundle\Entity\User $user
     * @return TrGiven
     */
    public function setUser(\Gbs\LeaveBundle\Entity\User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Gbs\LeaveBundle\Entity\User 
     */
    public function getUser()
    {
        return $this->user;
    }
}

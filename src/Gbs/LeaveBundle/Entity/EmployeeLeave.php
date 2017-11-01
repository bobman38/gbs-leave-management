<?php

namespace Gbs\LeaveBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EmployeeLeave
 *
 * @ORM\Table(name="employeeleave")
 * @ORM\Entity(repositoryClass="Gbs\LeaveBundle\Entity\EmployeeLeaveRepository")
 */
class EmployeeLeave
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
     * @var integer
     *
     * @ORM\Column(name="state", type="integer")
     */
    private $state;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="start", type="date")
     */
    private $start;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="end", type="date")
     */
    private $end;

    /**
     * @var integer
     *
     * @ORM\Column(name="partday", type="integer", nullable=true)
     */
    private $partday;

    /**
     * @var float
     *
     * @ORM\Column(name="days", type="float")
     */
    private $days;

    /**
     * @var integer
     *
     * @ORM\Column(name="type", type="integer")
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="comment", type="string", length=255, nullable=true)
     */
    private $comment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created", type="datetime")
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\User", inversedBy="leaves")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $creator;

    /**
     * @var string
     *
     * @ORM\Column(name="validation_comment", type="string", length=255, nullable=true)
     */
    private $validationComment;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="validation_date", type="datetime", nullable=true)
     */
    private $validationDate;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $validationUser;

    public static $partdays = array(
                '0' => 'Full day',
                '1' => 'Morning only',
                '2' => 'Afternoon only',
                );

    public static $typesCredit = array(
                '3' => 'Holiday Credit',
                '10' => 'Sick Credit',
            );

    public static $typesAllowed = array(
                '0' => 'Paid Leave',
                '7' => 'Travelling',
                '9' => 'Sick Leave (only if Sick credit available)',
                '12' => 'B&P HW'
            );

    public static $types = array(
                '0' => 'Paid Leave',
                '1' => 'Medical Leave',
                '2' => 'Exceptional Leave',
                '3' => 'Holiday Credit',
                '4' => 'Other (Not a leave, no TR)',
                '5' => 'Unpaid Leave',
                '6' => 'Business Lunch (no TR)',
                '7' => 'Travelling',
                '8' => 'Training',
                '9' => 'Sick Leave',
                '10' => 'Sick Credit',
                '11' => 'Compensation Time-off',
                '12' => 'B&P HW'
                );

    public static $shortTypes = array(
                '0' => 'Leave',
                '1' => 'Medical',
                '2' => 'Exceptional',
                '3' => 'Holiday Credit',
                '4' => 'Other',
                '5' => 'Unpaid Leave',
                '6' => 'Business Lunch',
                '7' => 'Travelling',
                '8' => 'Training',
                '9' => 'Sick Leave',
                '10' => 'Sick Credit',
                '11' => 'Comp TimeOff',
                '12' => 'B&P HW'
                );

    public static $typeColors = array(
                '0' => 'green',
                '1' => 'red',
                '2' => 'orange',
                '3' => 'black',
                '4' => 'blue',
                '5' => 'blue',
                '6' => 'blue',
                '7' => 'salmon',
                '8' => 'blue',
                '9' => 'coral',
                '10' => 'black',
                '11' => 'aquamarine',
                '12' => 'gold'
                );

    public static $states = array(
                '0' => 'Draft',
                '1' => 'To Validate',
                '2' => 'Accepted',
                '3' => 'Refused',
                );

    public function __construct() {
        $this->state = 0;
        $this->type = 0;
        $this->created = new \DateTime();
        $this->start = (new \DateTime())->add(new \DateInterval('P1D'));
        $this->end = (new \DateTime())->add(new \DateInterval('P1D'));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId() {
        return $this->id;
    }

    /**
     * Set state
     *
     * @param integer $state
     * @return EmployeeLeave
     */
    public function setState($state) {
        $this->state = $state;

        return $this;
    }

    /**
     * Get state
     *
     * @return integer
     */
    public function getState() {
        return $this->state;
    }

    public function getStateName() {
        return self::$states[$this->state];
    }

    /**
     * Set start
     *
     * @param \DateTime $start
     * @return EmployeeLeave
     */
    public function setStart($start) {
        $this->start = $start;

        return $this;
    }

    /**
     * Get start
     *
     * @return \DateTime
     */
    public function getStart() {
        return $this->start;
    }

    /**
     * Set end
     *
     * @param \DateTime $end
     * @return EmployeeLeave
     */
    public function setEnd($end) {
        $this->end = $end;

        return $this;
    }

    /**
     * Get end
     *
     * @return \DateTime
     */
    public function getEnd() {
        return $this->end;
    }

    /**
     * Set days
     *
     * @param float $days
     * @return EmployeeLeave
     */
    public function setDays($days) {
        $this->days = $days;

        return $this;
    }

    /**
     * Get days
     *
     * @return float
     */
    public function getDays() {
        return $this->days;
    }

    /**
     * Set type
     *
     * @param integer $type
     * @return EmployeeLeave
     */
    public function setType($type) {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type
     *
     * @return integer
     */
    public function getType() {
        return $this->type;
    }

    public function getTypeName() {
        if($this->type < count(self::$types)) {
            return self::$types[$this->type];
        }
        else {
            return "";
        }
    }

    public function getTypeShortName() {
        if($this->type < count(self::$shortTypes)) {
            return self::$shortTypes[$this->type];
        }
        else {
            return "";
        }
    }

    public function getTypeColor() {
        $color = ($this->state==1?'light':'');
        $color .= self::$typeColors[$this->type];
        return $color;
    }

    /**
     * Set comment
     *
     * @param string $comment
     * @return EmployeeLeave
     */
    public function setComment($comment) {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Get comment
     *
     * @return string
     */
    public function getComment() {
        return $this->comment;
    }

    /**
     * Set user
     *
     * @param \Gbs\LeaveBundle\Entity\User $user
     * @return EmployeeLeave
     */
    public function setUser(\Gbs\LeaveBundle\Entity\User $user) {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \Gbs\LeaveBundle\Entity\User
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Set partday
     *
     * @param integer $partday
     * @return EmployeeLeave
     */
    public function setPartday($partday) {
        $this->partday = $partday;

        return $this;
    }

    /**
     * Get partday
     *
     * @return integer
     */
    public function getPartday() {
        return $this->partday;
    }

    public function getPartdayName() {
        return self::$partdays[$this->partday];
    }

    public function canEdit($securityContext) {
        return $securityContext->isGranted('ROLE_LEAVES') ||
        ($this->getUser()==$securityContext->getToken()->getUser()
            && $this->getState()==0);
    }

    public function canAskForValidation($securityContext) {
        return ($securityContext->isGranted('ROLE_LEAVES')
            || $this->getUser()==$securityContext->getToken()->getUser())
        && $this->getState()==0;
    }

    public function canValidate($securityContext) {
        return ($securityContext->isGranted('ROLE_LEAVES') && $this->getState()==1)||
        (($this->getUser()->getManager()==$securityContext->getToken()->getUser() ||
            ($this->getUser()->getManager() !== null && $this->getUser()->getManager()->getManager()==$securityContext->getToken()->getUser()))
        && $this->getState()==1);
    }

    /**
     * Set created
     *
     * @param \DateTime $created
     * @return EmployeeLeave
     */
    public function setCreated($created)
    {
        $this->created = $created;

        return $this;
    }

    /**
     * Get created
     *
     * @return \DateTime
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * Set validationComment
     *
     * @param string $validationComment
     * @return EmployeeLeave
     */
    public function setValidationComment($validationComment)
    {
        $this->validationComment = $validationComment;

        return $this;
    }

    /**
     * Get validationComment
     *
     * @return string
     */
    public function getValidationComment()
    {
        return $this->validationComment;
    }

    /**
     * Set validationDate
     *
     * @param \DateTime $validationDate
     * @return EmployeeLeave
     */
    public function setValidationDate($validationDate)
    {
        $this->validationDate = $validationDate;

        return $this;
    }

    /**
     * Get validationDate
     *
     * @return \DateTime
     */
    public function getValidationDate()
    {
        return $this->validationDate;
    }

    /**
     * Set validationUser
     *
     * @param \Gbs\LeaveBundle\Entity\User $validationUser
     * @return EmployeeLeave
     */
    public function setValidationUser(\Gbs\LeaveBundle\Entity\User $validationUser = null)
    {
        $this->validationUser = $validationUser;

        return $this;
    }

    /**
     * Get validationUser
     *
     * @return \Gbs\LeaveBundle\Entity\User
     */
    public function getValidationUser()
    {
        return $this->validationUser;
    }

    /**
     * Set creator
     *
     * @param \Gbs\LeaveBundle\Entity\User $creator
     * @return EmployeeLeave
     */
    public function setCreator(\Gbs\LeaveBundle\Entity\User $creator = null)
    {
        $this->creator = $creator;

        return $this;
    }

    /**
     * Get creator
     *
     * @return \Gbs\LeaveBundle\Entity\User
     */
    public function getCreator()
    {
        return $this->creator;
    }
}

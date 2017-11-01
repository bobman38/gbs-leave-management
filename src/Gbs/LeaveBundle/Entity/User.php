<?php

namespace Gbs\LeaveBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity
 * @ORM\Table(name="user")
 */
class User extends BaseUser /*implements LdapUserInterface*/
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @ORM\Column(name="dn", type="string", length=255)
     */
    protected $dn;

    /**
     * @ORM\Column(name="fullname", type="string", length=255, nullable=true)
     */
    protected $fullName;

    /**
     * @ORM\Column(name="lastname", type="string", length=255, nullable=true)
     */
    protected $lastName;

    protected $attributes;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="employee_start_date", nullable=true, type="date")
     */
    private $employeeStartDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="last_medical_visit", nullable=true, type="date")
     */
    private $lastMedicalVisit;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\User")
     * @ORM\JoinColumn(nullable=true)
     */
    private $manager;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\Country")
     * @ORM\JoinColumn(nullable=true)
     */
    private $country;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\Company")
     * @ORM\JoinColumn(nullable=true)
     */
    private $company;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\Site")
     * @ORM\JoinColumn(nullable=true)
     */
    private $site;

    /**
     * @var boolean
     *
     * @ORM\Column(name="tr", type="boolean")
     */
    private $tr;

    /**
     * @ORM\Column(name="mail_password", type="string", length=255, nullable=true)
     */
    private $mailPassword;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="mail_password_lastchange", nullable=true, type="date")
     */
    private $mailPasswordLastChange;

    /**
     * @ORM\ManyToOne(targetEntity="Gbs\LeaveBundle\Entity\Department")
     * @ORM\JoinColumn(nullable=true)
     */
    private $department;

    /**
     * @ORM\OneToMany(targetEntity="Gbs\LeaveBundle\Entity\EmployeeLeave", mappedBy="user")
     */
    private $leaves;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    protected $responder;

    /**
     * @var float
     *
     * @ORM\Column(name="holiday_credit_per_month", type="float", nullable=true)
     */
    private $holidayCreditPerMonth;

    public function __construct()
    {
        parent::__construct();
        $this->tr = false;
        // your own logic
        $this->employeeStartDate = new \DateTime();
    }

    public function __toString() {
        return $this->fullName;
    }

    public function getDn()
    {
        return $this->dn;
    }

    public function setDn($dn)
    {
        $this->dn = $dn;

        return $this;
    }

    public function getCn()
    {
        return $this->username;
    }

    public function setCn($cn)
    {
        $this->username = $cn;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttribute($name)
    {
        return isset($this->attributes[$name]) ? $this->attributes[$name] : null;
    }

    public function isEqualTo(UserInterface $user)
    {
        if (!$user instanceof LdapUserInterface
            || $user->getUsername() !== $this->username
            || $user->getEmail() !== $this->email
            || count(array_diff($user->getRoles(), $this->getRoles())) > 0
            || $user->getDn() !== $this->dn
        ) {
            return false;
        }

        return true;
    }

    public function serialize()
    {
        return serialize(array(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->emailCanonical,
            $this->email,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->roles,
            $this->dn,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->password,
            $this->salt,
            $this->usernameCanonical,
            $this->username,
            $this->emailCanonical,
            $this->email,
            $this->expired,
            $this->locked,
            $this->credentialsExpired,
            $this->enabled,
            $this->id,
            $this->roles,
            $this->dn,
        ) = unserialize($serialized);
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
     * Set manager
     *
     * @param \Gbs\LeaveBundle\Entity\User $manager
     * @return User
     */
    public function setManager(\Gbs\LeaveBundle\Entity\User $manager = null)
    {
        $this->manager = $manager;

        return $this;
    }

    /**
     * Get manager
     *
     * @return \Gbs\LeaveBundle\Entity\User
     */
    public function getManager() {
        return $this->manager;
    }

    /**
     * Set country
     *
     * @param \Gbs\LeaveBundle\Entity\Country $country
     * @return User
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

    /**
     * Set lastMedicalVisit
     *
     * @param \DateTime $lastMedicalVisit
     * @return User
     */
    public function setLastMedicalVisit($lastMedicalVisit)
    {
        $this->lastMedicalVisit = $lastMedicalVisit;

        return $this;
    }

    /**
     * Get lastMedicalVisit
     *
     * @return \DateTime
     */
    public function getLastMedicalVisit()
    {
        return $this->lastMedicalVisit;
    }

    /**
     * Set tr
     *
     * @param boolean $tr
     * @return User
     */
    public function setTr($tr)
    {
        $this->tr = $tr;

        return $this;
    }

    /**
     * Get tr
     *
     * @return boolean
     */
    public function getTr()
    {
        return $this->tr;
    }

    /**
     * Set fullName
     *
     * @param string $fullName
     * @return User
     */
    public function setFullName($fullName)
    {
        $this->fullName = $fullName;

        return $this;
    }

    /**
     * Get fullName
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->fullName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     * @return User
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set employeeStartDate
     *
     * @param \DateTime $employeeStartDate
     * @return User
     */
    public function setEmployeeStartDate($employeeStartDate)
    {
        $this->employeeStartDate = $employeeStartDate;

        return $this;
    }

    /**
     * Get employeeStartDate
     *
     * @return \DateTime
     */
    public function getEmployeeStartDate()
    {
        return $this->employeeStartDate;
    }

    /**
     * Set department
     *
     * @param \Gbs\LeaveBundle\Entity\Department $department
     *
     * @return User
     */
    public function setDepartment(\Gbs\LeaveBundle\Entity\Department $department = null)
    {
        $this->department = $department;

        return $this;
    }

    /**
     * Get department
     *
     * @return \Gbs\LeaveBundle\Entity\Department
     */
    public function getDepartment()
    {
        return $this->department;
    }

    /**
     * Set mailPassword
     *
     * @param string $mailPassword
     *
     * @return User
     */
    public function setMailPassword($mailPassword)
    {
        $this->mailPassword = $mailPassword;

        return $this;
    }

    /**
     * Get mailPassword
     *
     * @return string
     */
    public function getMailPassword()
    {
        return $this->mailPassword;
    }

    /**
     * Set mailPasswordLastChange
     *
     * @param \DateTime $mailPasswordLastChange
     *
     * @return User
     */
    public function setMailPasswordLastChange($mailPasswordLastChange)
    {
        $this->mailPasswordLastChange = $mailPasswordLastChange;

        return $this;
    }

    /**
     * Get mailPasswordLastChange
     *
     * @return \DateTime
     */
    public function getMailPasswordLastChange()
    {
        return $this->mailPasswordLastChange;
    }

    /**
     * Add leafe
     *
     * @param \Gbs\LeaveBundle\Entity\EmployeeLeave $leafe
     *
     * @return User
     */
    public function addLeafe(\Gbs\LeaveBundle\Entity\EmployeeLeave $leafe)
    {
        $this->leaves[] = $leafe;

        return $this;
    }

    /**
     * Remove leafe
     *
     * @param \Gbs\LeaveBundle\Entity\EmployeeLeave $leafe
     */
    public function removeLeafe(\Gbs\LeaveBundle\Entity\EmployeeLeave $leafe)
    {
        $this->leaves->removeElement($leafe);
    }

    /**
     * Get leaves
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getLeaves()
    {
        return $this->leaves;
    }

    /**
     * Set responder
     *
     * @param string $responder
     *
     * @return User
     */
    public function setResponder($responder)
    {
        $this->responder = $responder;

        return $this;
    }

    /**
     * Get responder
     *
     * @return string
     */
    public function getResponder()
    {
        return $this->responder;
    }

    /**
     * Set holidayCreditPerMonth
     *
     * @param float $holidayCreditPerMonth
     *
     * @return User
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
     * Set company
     *
     * @param \Gbs\LeaveBundle\Entity\Company $company
     *
     * @return User
     */
    public function setCompany(\Gbs\LeaveBundle\Entity\Company $company = null)
    {
        $this->company = $company;

        return $this;
    }

    /**
     * Get company
     *
     * @return \Gbs\LeaveBundle\Entity\Company
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * Set site
     *
     * @param \Gbs\LeaveBundle\Entity\Site $site
     *
     * @return User
     */
    public function setSite(\Gbs\LeaveBundle\Entity\Site $site = null)
    {
        $this->site = $site;

        return $this;
    }

    /**
     * Get site
     *
     * @return \Gbs\LeaveBundle\Entity\Site
     */
    public function getSite()
    {
        return $this->site;
    }
}

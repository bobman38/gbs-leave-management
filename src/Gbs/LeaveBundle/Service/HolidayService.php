<?php

namespace Gbs\LeaveBundle\Service;

use Gbs\LeaveBundle\Entity\User;
use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Security\Core\SecurityContext;

/**
* Add an action to the database
*/
class HolidayService {
    protected $em;
    public $date1, $date2;
    public $month;

    public function __construct(EntityManager $em) {
        $this->em = $em;
    }

    public function getSickBalance(User $user) {
        $balance = 0;
        $leaves = $this->em->getRepository('GbsLeaveBundle:EmployeeLeave')->findByUser($user);
        foreach ($leaves as $leave) {
            if($leave->getType()==9 && $leave->getState()==2) {
                $balance -= $leave->getDays();
            }
            elseif ($leave->getType()==10) {
                $balance += $leave->getDays();
            }
        }
        return $balance;
    }

    public function getHolidayBalance(User $user) {
        $balance = 0;
        $leaves = $this->em->getRepository('GbsLeaveBundle:EmployeeLeave')->findByUser($user);
        foreach ($leaves as $leave) {
            if($leave->getType()==0 && $leave->getState()==2) {
                $balance -= $leave->getDays();
            }
            elseif ($leave->getType()==3) {
                $balance += $leave->getDays();
            }
        }
        return $balance;
    }

    public function getHolidayBalanceAsOfToday(User $user) {
        $balance = 0;
        $leaves = $this->em->getRepository('GbsLeaveBundle:EmployeeLeave')->findByUser($user);
        foreach ($leaves as $leave) {
            if($leave->getType()==0 && $leave->getState()==2 && $leave->getStart() < new \DateTime()) {
                $balance -= $leave->getDays();
            }
            elseif ($leave->getType()==3) {
                $balance += $leave->getDays();
            }
        }
        return $balance;
    }

    public function getLeaveDays(EmployeeLeave $leave) {
        if($leave->getPartDay()>0) {
            return 0.5;
        }
        else {
            return $this->getWorkingDays($leave->getStart(), $leave->getEnd(), $this->getPublicHolidays($leave->getUser()->getCountry()));
        }
    }

    private function getWorkingDays(\DateTime $startDate, \DateTime $endDate, $holidaysfull, $holidayspart = array()) {
        $workingDays = 0;
        $start = clone $startDate;
        while ($start->getTimestamp() <= $endDate->getTimestamp()) {
            if(!in_array($start->format('Y-m-d'), $holidaysfull) // not in holidays
                && $start->format('N') <= 5 // and not Saturday/Sunday
             ) {
                if(in_array($start->format('Y-m-d'), $holidayspart)) {
                    $workingDays = $workingDays+0.5;
                }
                else {
                   $workingDays++;
                }
                //print(date_format($start, '[Y-m-d] '));
            }
            $start->modify('+ 1 day');
        }
        return $workingDays;
    }

    public function getGlobalWorkingDaysByMonth($delta) {
        $this->computeFirstLastDayMonth($delta);
        $france = $this->em->getRepository('GbsLeaveBundle:Country')->findOneById(1);
        return $this->month.': '.
            $this->getWorkingDays($this->date1, $this->date2, $this->getPublicHolidays($france));
    }

    public function getWorkingDaysByMonth(User $user, $delta) {
        $this->computeFirstLastDayMonth($delta);
        $leaves = $this->getLeaves($user);
        $holidaysfull = array_merge($this->getPublicHolidays($user->getCountry()), $leaves['full']);
        return $this->getWorkingDays($this->date1, $this->date2, $holidaysfull, $leaves['part']);
    }

    public function getTRByMonth(User $user, $delta) {
        if($user->getTr()) {
            $this->computeFirstLastDayMonth($delta);
            $leaves = $this->getLeaves($user, true);
            $holidays = array_merge($this->getPublicHolidays($user->getCountry()),
                                    $leaves['full'],
                                    $leaves['part']);
            return $this->getWorkingDays($this->date1, $this->date2, $holidays);
        }
        else {
            return 0;
        }

    }

    public function getTRTotalByMonth($delta) {
        $users = $this->em->getRepository('GbsLeaveBundle:User')->findBy(
            array(),
            array()
            );
        $total = 0;
        foreach ($users as $user) {
            $total += $this->getTRByMonth($user, $delta);
        }
        return $total;
    }

    public function isNotYetSavedTR($delta) {
        $this->computeFirstLastDayMonth($delta);
        $trGiven = $this->em->getRepository('GbsLeaveBundle:TrGiven')->findBy(
            array('month' => $this->month),
            array()
            );
        return (count($trGiven)==0);
    }

    // Return public holidays for 1 country
    private function getPublicHolidays($country) {
        $holidays = array();
        $publicholidays = $this->em->getRepository('GbsLeaveBundle:PublicHoliday')->findByCountry($country);
        foreach ($publicholidays as $publicholiday) {
            $holidays[] = date_format($publicholiday->getDate(), 'Y-m-d');
            //print(date_format($publicholiday->getDate(), '[Y-m-d] '));
        }
        return $holidays;
    }
    // Return leaves for 1 user
    private function getLeaves($user, $with_other=false) {
        $full = array();
        $part = array();
        $types_allowed = array();
        if($with_other) {
            $types_allowed = array(0, 1, 2, 4);
        }
        else {
            $types_allowed = array(0, 1, 2);
        }
        $leaves = $this->em->getRepository('GbsLeaveBundle:EmployeeLeave')->findBy(
            array(
                'user' => $user,
                'state' => array(2), // ONLY VALIDATED
                'type' => $types_allowed,
                )
            , array());
        foreach ($leaves as $leave) {
            $start = clone $leave->getStart();
            if($leave->getPartday()!=0) {
                $part[] = date_format($start, 'Y-m-d');
            }
            else {
                $full[] = date_format($start, 'Y-m-d');
                while ($start->getTimestamp() <= $leave->getEnd()->getTimestamp()) {
                    $full[] = date_format($start, 'Y-m-d');
                    $start->modify('+ 1 day');
                    //print(date_format($start, '[Y-m-d] '));
                }
            }
        }
        return array('full' => $full, 'part' => $part);
    }

    public function computeFirstLastDayMonth($delta) {
        $this->date1 = new \DateTime();
        $this->date1->modify($delta);
        $this->date1->modify('first day of this month');
        $this->date2 = clone $this->date1;
        $this->date2->modify('last day of this month');
        $this->month = date_format($this->date1, 'Y-m');
    }

}

<?php

namespace Gbs\LeaveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;

class AddHolidayCreditCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('gbs:leave:addholidaycredit')
                ->setDescription('Add monthly holiday credit depending of the country')
                ->addArgument('users', InputArgument::OPTIONAL, 'users to be excluded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->getContainer()->get('logger');
        $excluded = explode(";", $input->getArgument('users'));
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $users = $em->getRepository('GbsLeaveBundle:User')
           ->createQueryBuilder('u')
           ->join('u.country', 'c')
           ->addSelect('c')
           ->andWhere('u.enabled=1')
           ->andWhere('c.holidayCreditPerMonth <> 0')
           ->getQuery()->getResult();

        foreach($users as $user) {
            if(!in_array($user->getUsername(), $excluded)) {
                $credit = new EmployeeLeave();
                $credit->setType(3); // holiday credit
                $credit->setState(2); // accepted
                if($user->getHolidayCreditPerMonth()!==null) {
                    $credit->setDays($user->getHolidayCreditPerMonth());
                }
                else {
                    $credit->setDays($user->getCountry()->getHolidayCreditPerMonth());
                }

                $credit->setUser($user);
                $credit->setComment("Automatic holiday credit");
                $em->persist($credit);
            }
        }
        $em->flush();
    }
}

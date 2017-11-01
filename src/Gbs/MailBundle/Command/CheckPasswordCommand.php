<?php

namespace Gbs\MailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;

class CheckPasswordCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('gbs:mail:password')
                ->setDescription('Check Password')
                //->addArgument('users', InputArgument::OPTIONAL, 'users to be excluded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->getContainer()->get('logger');
        //$userManager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('GbsLeaveBundle:User')
           ->createQueryBuilder('u')
           //->join('u.country', 'c')
           //->addSelect('c')
           ->andWhere('u.enabled=1')
           ->andWhere('u.id=1')
           //->andWhere('c.holidayCreditPerMonth <> 0')
           ->getQuery()->getResult();

        foreach($users as $user) {
            if($user->getMailPasswordLastChange() === null) {
                $command = $this->getContainer()->getParameter('xmpp');
					$command .= ' '.$user->getEmail();
					$command .= ' "Your password is too old (NEVER CHANGED). You need to change it quickly on https://internal.gbs/resetPassword. THANKS !"';
					$output = exec($command." /dev/null 2>&1 &", $output_full, $return_var);
            }
            else if($user->getMailPasswordLastChange() < (new \DateTime())->sub(new DateInterval('P6M'))) {
                $command = $this->getContainer()->getParameter('xmpp');
                    $command .= ' '.$user->getEmail();
                    $command .= ' "Your password is too old (OLDER THAN 6 MONTHS). You need to change it quickly on https://internal.gbs/resetPassword. THANKS !"';
                    $output = exec($command." /dev/null 2>&1 &", $output_full, $return_var);
            }
        }
    }
}

<?php

namespace Gbs\LeaveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MedicalVisitMailCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('gbs:leave:medicalvisit')
                ->setDescription('Send a mail to specific people regarding medicial visit to be done in the next month')
                ->addArgument('users', InputArgument::REQUIRED, 'Users')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->getContainer()->get('logger');
        $users = explode(";", $input->getArgument('users'));
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $message = \Swift_Message::newInstance('GBS Medical Visit Mail ['.date_format(new \DateTime(), 'Y-m-d H:i').']')
            ->setFrom('leave@gbandsmith.com');
        foreach ($users as $user) {
            if($userManager->findUserByUsername($user)) {
                $output->writeln('Send to: '. $user);
                $message->addTo($userManager->findUserByUsername($user)->getEmail());
            }
            else {
                $output->writeln('Can\'t find user: '. $user);
            }
        }

        $userToRenew = $em->getRepository('GbsLeaveBundle:User')
           ->createQueryBuilder('u')
           ->andWhere('DATE_ADD(u.lastMedicalVisit, 23, \'MONTH\') < CURRENT_DATE()')
           ->andWhere('u.enabled = TRUE')
           ->getQuery()->getResult();

        if(count($userToRenew)>0) {
            $message->setBody($this->getContainer()->get('templating')->render('GbsLeaveBundle:Mail:medicalvisit.txt.twig',
                array(
                    'userToRenew' => $userToRenew,
                )
            )
        );

            $this->getContainer()->get('mailer')->send($message);
        }
    }
}

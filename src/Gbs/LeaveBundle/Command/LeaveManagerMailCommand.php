<?php

namespace Gbs\LeaveBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class LeaveManagerMailCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('gbs:leave:managermail')
                ->setDescription('Send a mail to manager in order to validate leaves (paid leave + travelling)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->getContainer()->get('logger');
        $userManager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $managers = $em->getRepository('GbsLeaveBundle:User')
            ->createQueryBuilder('u')
            ->where("u.id IN (SELECT m.id FROM GbsLeaveBundle:User u2 JOIN u2.manager m WHERE u2.enabled=1)")
            ->getQuery()->getResult();
        foreach ($managers as $manager) {
            $output->writeln('Send to: '. $manager->getUsername());
            $message = \Swift_Message::newInstance('GBS Monthly Leave Check ['.date_format(new \DateTime(), 'Y-m-d H:i').']')
                ->setFrom('leave@gbandsmith.com')
                ->addTo($manager->getEmail());
            $users = $em->getRepository('GbsLeaveBundle:User')
                ->createQueryBuilder('u')
                ->where('u.manager = :manager AND u.enabled=1')
                ->setParameter('manager', $manager)
                ->orderby('u.username')
                ->getQuery()->getResult();

                $message->addPart($this->getContainer()->get('templating')->render('GbsLeaveBundle:Mail:managermail.html.twig',
                    array(
                        'manager' => $manager,
                        'users' => $users,
                        )
                    )
                    , 'text/html'
                );

                $this->getContainer()->get('mailer')->send($message);
        }
    }
}

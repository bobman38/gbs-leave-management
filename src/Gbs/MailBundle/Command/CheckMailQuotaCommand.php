<?php

namespace Gbs\MailBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Ovh\Api;

class CheckMailQuotaCommand extends ContainerAwareCommand {

    protected function configure() {
        parent::configure();

        $this
                ->setName('gbs:mail:quota')
                ->setDescription('Check Mail Quota')
                //->addArgument('users', InputArgument::OPTIONAL, 'users to be excluded')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $logger = $this->getContainer()->get('logger');
        /*$soap = new \SoapClient($this->getContainer()->getParameter('ovh_manager_url'));

        $session = $soap->login(
            $this->getContainer()->getParameter('ovh_manager_user'),
            $this->getContainer()->getParameter('ovh_manager_password'),
            'fr',
            false
        );*/
        $ovh = $this->getOvhApi();

        //$userManager = $this->getContainer()->get('fos_user.user_manager');
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $users = $em->getRepository('GbsLeaveBundle:User')
           ->createQueryBuilder('u')
           //->join('u.country', 'c')
           //->addSelect('c')
           ->andWhere('u.enabled=1')
           //->andWhere('u.id=55')
           //->andWhere('c.holidayCreditPerMonth <> 0')
           ->getQuery()->getResult();

        foreach($users as $user) {
            try {
                $email = $user->getEmail();
                $popusr = subStr($email, 0, strPos($email,'@'.$this->getContainer()->getParameter('ovh_manager_domain')));

                $resultInfo = $ovh->get('/email/domain/'.$this->getContainer()->getParameter('ovh_manager_domain').'/account/'.$popusr);
                $resultUsage = $ovh->get('/email/domain/'.$this->getContainer()->getParameter('ovh_manager_domain').'/account/'.$popusr.'/usage');

                $usage = round($resultUsage["quota"]/$resultInfo["size"]*100);
                $size = round($resultInfo["size"]/1000/1000/1000);

                if($usage>90) {
                    print 'Mailbox ' .$email. ' is using ' . $usage . '% of '. $size . ' GB.'."\n";
                    $message = \Swift_Message::newInstance('GBS Mailbox is full at ' . $usage. '%. Please clean up !')
                        ->setFrom('no-reply@gbandsmith.com')
                        ->setTo($email);
                    $this->getContainer()->get('mailer')->send($message);
                }
            }
            catch(\Exception $e) {
                print 'No MAILBOX for '.$email. ' ('.$e->getMessage().')';
            }
        }
    }

    private function getOvhApi() {
        return new Api(
            $this->getContainer()->getParameter('ovh_api_appkey'),
            $this->getContainer()->getParameter('ovh_api_appsecret'),
            $this->getContainer()->getParameter('ovh_api_endpoint'),
            $this->getContainer()->getParameter('ovh_api_consumerkey')
        );
    }
}

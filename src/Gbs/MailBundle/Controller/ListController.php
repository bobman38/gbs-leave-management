<?php

namespace Gbs\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gbs\MailBundle\Entity\Forwarder;
use Gbs\MailBundle\Form\ForwarderType;
use Ovh\Api;

class ListController extends Controller {

    public function indexAction() {
        $logger = $this->get('logger');
        $myLists = array();
        $notMyLists = array();
        // grab the current list of the user
        $email = $this->get('security.context')->getToken()->getUser()->getEmail();
        $popusr = subStr($email, 0, strPos($email,'@'.$this->container->getParameter('ovh_manager_domain')));


        $mls = $this->getCurrentMls();

        foreach ($mls as $ml => $subscribers) {
            $notMyLists[] = $ml;
            $logger->debug('ML: ' . $ml );
            foreach ($subscribers as $subscriber) {
                // check if current user is suscriber of current ML
                if($email == $subscriber) {
                    $logger->info($email . ' - ' . $subscriber );
                    $myLists[] = $ml;
                    $notMyLists = array_diff($notMyLists, [$ml]);
                }
            }
        }

        return $this->render('GbsMailBundle:List:index.html.twig',
        array(
            'mylists' => $myLists,
            'notMylists' => $notMyLists,
        ));
    }

    public function indexAdminAction() {
        $this->denyAccessUnlessGranted('ROLE_USER_ADMIN');
        $logger = $this->get('logger');

        $em = $this->getDoctrine()->getManager();
        $users = $em->getRepository('GbsLeaveBundle:User')->findBy(array('enabled' => true), array('lastName' => 'ASC'));


        $mls = $this->getCurrentMls();

        $usersML = array();
        foreach ($users as $user) {
            $userML = array();
            foreach ($mls as $ml => $subscribers) {
                foreach ($subscribers as $subscriber) {
                    // check if current user is suscriber of current ML
                    if($user->getEmail() == $subscriber) {
                        $userML[] = $ml;
                    }
                }
            }
            $usersML[$user->getEmail()] = $userML;
            unset($userML);
        }

        return $this->render('GbsMailBundle:List:indexAdmin.html.twig',
        array(
            'users' => $users,
            'usersML' => $usersML,
        ));
    }

    private function getCurrentMls() {
        $logger = $this->get('logger');
        $cache = $this->container->get('doctrine_cache.providers.file_system');
        $ml_cache = $cache->fetch('ml');

        if($ml_cache === false) {
            $ml_cache = array();

            $ovh = $this->getOvhApi();
            $mls = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/mailingList');

            foreach ($mls as $ml) {
                $subscribers = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/mailingList/'.$ml.'/subscriber');
                //$logger->debug($mailinglist->ml . ' - ' . $mailinglist->owner . ' - ' . $mailinglist->nbSubscribers);
                $ml_cache[$ml]  = $subscribers;
            }

            $cache->save('ml', $ml_cache, 3600);
        }
        return $ml_cache;
    }

    private function getOvhApi() {
        return new Api(
            $this->container->getParameter('ovh_api_appkey'),
            $this->container->getParameter('ovh_api_appsecret'),
            $this->container->getParameter('ovh_api_endpoint'),
            $this->container->getParameter('ovh_api_consumerkey')
        );
    }

}

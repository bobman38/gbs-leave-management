<?php

namespace Gbs\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gbs\MailBundle\Entity\Forwarder;
use Gbs\MailBundle\Form\ForwarderType;
use Ovh\Api;

class ForwarderController extends Controller {

    public function indexAction() {
        $logger = $this->get('logger');
        $forwarder = new Forwarder();
        // grab the current forwarder for the user
        $email = $this->get('security.context')->getToken()->getUser()->getEmail();
        $popusr = subStr($email, 0, strPos($email,'@'.$this->container->getParameter('ovh_manager_domain')));

        $ovh = $this->getOvhApi();

        $results = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/redirection?from='.$email);
        $active = FALSE;
        // there is redirections ?
        foreach ($results as $id) {
            $result = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/redirection/'.$id);
            if($result["to"]!=$result["from"]) {
                // try to grab the destination user in local user db
                $userManager = $this->get('fos_user.user_manager');
                $to = $userManager->findUserByEmail($result["to"]);
                // put info in obj $forwarder
                $forwarder->setTo($to);
                $this->get('session')->getFlashBag()->add('notice', 'Forwarder is currenlty active ('.$result["to"].')');
                $active = TRUE;
            }
        }

        if(!$active) {
            $this->get('session')->getFlashBag()->add('notice', 'Forwarder unactive');
        }

        $form = $this->createForm(new ForwarderType(), $forwarder, array(
            'action' => $this->generateUrl('mail_forwarder_set'),
            'active' => $active,
            ));
        return $this->render('GbsMailBundle:Forwarder:index.html.twig',
        array(
            'form' => $form->createView(),
        ));
    }

    public function setAction() {
        $forwarder = new Forwarder();

        $email = $this->get('security.context')->getToken()->getUser()->getEmail();
        $popusr = subStr($email, 0, strPos($email,'@'.$this->container->getParameter('ovh_manager_domain')));
        $ovh = $this->getOvhApi();

        $form = $this->createForm(new ForwarderType(), $forwarder, array());
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($form->get('remove')->isClicked()) {
                // delete forwarder
                $results = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/redirection?from='.$email);

                foreach ($results as $id) {
                    $result = $ovh->delete('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/redirection/'.$id);
                }
            }
            else if($forwarder->getTo() !== null){
                // set the forwarder
                $result = $ovh->post('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/redirection', array(
                    'from' => $email, // Required: Name of redirection (type: string)
                    'localCopy' => 'true', // Required: If true keep a local copy (type: boolean)
                    'to' => $forwarder->getTo()->getEmail(), // Required: Target of account (type: string)
                ));
            }
        }
        return $this->redirect( $this->generateUrl('mail_forwarder'));
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

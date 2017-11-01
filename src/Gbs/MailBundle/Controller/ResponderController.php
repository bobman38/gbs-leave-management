<?php

namespace Gbs\MailBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Gbs\MailBundle\Entity\Responder;
use Gbs\MailBundle\Form\ResponderType;
use Ovh\Api;

class ResponderController extends Controller {

    public function indexAction() {
        $responder = new Responder();
        $active = FALSE;
        // grab the current responder for the user
        $email = $this->get('security.context')->getToken()->getUser()->getEmail();
        $popusr = subStr($email, 0, strPos($email,'@'.$this->container->getParameter('ovh_manager_domain')));

        $ovh = $this->getOvhApi();

        // check responder existence
        $result = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder?account='.$popusr);
        // if exist
        if(in_array($popusr, $result)) {
            $result = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder/'.$popusr);
            $responder->setText($result["content"]);
            $this->get('session')->getFlashBag()->add('notice', 'Responder is currenlty active with following content: ');
            $active = TRUE;
        }
        else {
            $this->get('session')->getFlashBag()->add('notice', 'No responder currently set !');
            // grab the previous text
            $responder->setText($this->get('security.context')->getToken()->getUser()->getResponder());
        }

        $form = $this->createForm(new ResponderType(), $responder, array(
            'action' => $this->generateUrl('mail_responder_set'),
            'active' => $active,
            ));
        return $this->render('GbsMailBundle:Responder:index.html.twig',
        array(
            'form' => $form->createView(),
        ));
    }

    public function setAction() {
        $responder = new Responder();

        // get the API thing
        $email = $this->get('security.context')->getToken()->getUser()->getEmail();
        $popusr = subStr($email, 0, strPos($email,'@'.$this->container->getParameter('ovh_manager_domain')));
        $ovh = $this->getOvhApi();

        $form = $this->createForm(new ResponderType(), $responder, array());
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if ($form->get('remove')->isClicked()) {
                // delete responder
                $ovh->delete('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder/'.$popusr);
            }
            else {
                // check responder existence
                $result = $ovh->get('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder?account='.$popusr);

                // if exist
                if(in_array($popusr, $result)) {
                    // edit responder
                    $result = $ovh->put('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder/'.$popusr, array(
                        'content' => $responder->getText(), // Required: Content of responder (type: string)
                        'copy' => 'false', // Required: If true, emails will be copy to emailToCopy address (type: boolean)
                    ));
                }
                else {
                    // create responder
                    $result = $ovh->post('/email/domain/'.$this->container->getParameter('ovh_manager_domain').'/responder', array(
                        'account' => $popusr, // Required: Account of domain (type: string)
                        'content' => $responder->getText(), // Required: Content of responder (type: string)
                        'copy' => 'false', // Required: If true, emails will be copy to emailToCopy address (type: boolean)
                    ));
                }

                //save the responder
                $this->get('security.context')->getToken()->getUser()->setResponder($responder->getText());
                $userManager = $this->get('fos_user.user_manager');
                $userManager->updateUser($this->get('security.context')->getToken()->getUser());
            }
        }
        return $this->redirect( $this->generateUrl('mail_responder'));
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

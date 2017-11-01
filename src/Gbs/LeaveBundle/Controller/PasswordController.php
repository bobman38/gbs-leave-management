<?php

namespace Gbs\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Gbs\LeaveBundle\Form\PasswordForgetType;
use Gbs\LeaveBundle\Entity\PasswordForget;

class PasswordController extends Controller
{
    public function resetPasswordAction()
    {
        $passwordForget = new PasswordForget();
        $form = $this->createForm(new PasswordForgetType(), $passwordForget, array());
        $form->handleRequest($this->get('request'));
        if ($form->isValid()) {
            if($passwordForget->getNewPw1() != $passwordForget->getNewPw2()) {
                $this->get('session')->getFlashBag()->add('warning',
                    'Not the same two new passwords. I have in ze bilouque.');
            }
            else if($passwordForget->getActualPw() == $passwordForget->getNewPw1()) {
                $this->get('session')->getFlashBag()->add('warning',
                    'New password is the same as old one. Not this time, bro.');
            }
            else if(strlen($passwordForget->getNewPw1())<8 || strlen($passwordForget->getNewPw1())>12) {
                $this->get('session')->getFlashBag()->add('warning',
                    'New password need to be between 8 and 12 char.');
            }
            else if(!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[0-9]).*$/', $passwordForget->getNewPw1())) {
                $this->get('session')->getFlashBag()->add('warning',
                    'New password need to have one lowercase char, one uppercase char, and one number.');
            }

            else {
                // check the current password = open an IMAP connection
                $imap = imap_open("{ssl0.ovh.net:993/imap/ssl/novalidate-cert}INBOX",
                    $passwordForget->getEmail(),
                    $passwordForget->getActualPw(), 0, 1);
                if($imap === FALSE) {
                    $this->get('session')->getFlashBag()->add('warning',
                    'Not the right old password ! (or no mailbox configured on OVH...) '.implode(', ',imap_errors()));
                }
                else {
                    $popusr = subStr($passwordForget->getEmail(),0,strPos($passwordForget->getEmail(),'@'.$this->container->getParameter('ovh_manager_domain')));
                    try {
                        // go change ovh password
                        $soap = new \SoapClient($this->container->getParameter('ovh_manager_url'));
                        $session = $soap->login(
                                $this->container->getParameter('ovh_manager_user'),
                                $this->container->getParameter('ovh_manager_password'),
                                "fr",
                                false);
                        $soap->popModifyPassword(
                                $session,
                                $this->container->getParameter('ovh_manager_domain'),
                                $popusr,
                                $passwordForget->getNewPw1(),
                                false);
                        $soap->logout($session);
                        $this->get('session')->getFlashBag()->add('info','ovh mail password changed !');

                        // save the password on the user object for later usage
                        $userManager = $this->get('fos_user.user_manager');
                        $user = $userManager->findUserByEmail($passwordForget->getEmail());
                        if($user !== null) {
                            $user->setMailPassword(base64_encode($passwordForget->getNewPw1()));
                            $user->setMailPasswordLastChange(new \DateTime());
                            $userManager->updateUser($user);

                            // if not found then no password saved
                        }


                        // go to change ldap password
                        $cnx = ldap_connect($this->container->getParameter('ldap_manager_url'), 389);
                        ldap_set_option($cnx, LDAP_OPT_PROTOCOL_VERSION, 3);
                        ldap_set_option($cnx, LDAP_OPT_REFERRALS, 0);
                        if($cnx) {
                            $ldapbind = ldap_bind(
                                    $cnx,
                                    $this->container->getParameter('ldap_manager_user'),
                                    $this->container->getParameter('ldap_manager_password'));
                            if($ldapbind) {
                               $item = ldap_first_entry($cnx, ldap_search(
                                        $cnx,
                                        'ou=people, dc=gbs',
                                        '(&(objectClass=inetOrgPerson)(mail='.$passwordForget->getEmail().'))'
                                    ));
                               if($item) {
                                ldap_mod_replace(
                                    $cnx,
                                    ldap_get_dn($cnx, $item),
                                    array('userPassword' => '{MD5}'.base64_encode(pack("H*",md5($passwordForget->getNewPw1()))))
                                    );
                                $this->get('session')->getFlashBag()->add('info','ldap password changed !');
                               }
                            }
                        }

                    } catch(SoapFault $fault) {
                        $this->get('session')->getFlashBag()->add('warning','Error during ovh mail password change: '. $fault->getMessage());
                    }
                }
            }
        }
        return $this->render('GbsLeaveBundle:Password:resetPassword.html.twig', array(
                'form' => $form->createView(),
            ));


    }

}

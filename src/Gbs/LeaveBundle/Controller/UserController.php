<?php

namespace Gbs\LeaveBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Gbs\LeaveBundle\Entity\User;
use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Gbs\LeaveBundle\Form\UserType;
use Gbs\LeaveBundle\Form\HolidayCreditType;

/**
 * User controller.
 *
 */
class UserController extends Controller
{

    /**
     * Lists all User entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GbsLeaveBundle:User')->findBy(array('enabled' => true),array('lastName' => 'ASC'));

        return $this->render('GbsLeaveBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function workingDaysAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GbsLeaveBundle:User')->findBy(array('enabled' => true), array('lastName' => 'ASC'));

        return $this->render('GbsLeaveBundle:User:workingdays.html.twig', array(
            'entities' => $entities,
        ));
    }

    /**
     * Displays a form to edit an existing User entity.
     *
     */
    public function editAction(User $entity) {
        $em = $this->getDoctrine()->getManager();

        $holidaycredits = $em->getRepository('GbsLeaveBundle:EmployeeLeave')->findBy(
            array('user' => $entity, 'type' => array(3,10)),
            array('id' => 'DESC')
            );

        $editForm = $this->createEditForm($entity);
        $holidaycredit = new EmployeeLeave();
        $holidaycreditForm = $this->createAddHolidayCreditForm($holidaycredit, $entity->getId());

        $balance = $this->get('gbs_leave.holiday');

        return $this->render('GbsLeaveBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
            'holiday_form'   => $holidaycreditForm->createView(),
            'holidaycredits' => $holidaycredits,
            'balance'       => $balance->getHolidayBalance($entity),
            'balanceasOfToday'       => $balance->getHolidayBalanceAsOfToday($entity),
        ));
    }

    /**
    * Creates a form to edit a User entity.
    *
    * @param User $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(User $entity) {
        $form = $this->createForm(new UserType(), $entity, array(
            'action' => $this->generateUrl('user_update', array('id' => $entity->getId())),
            'method' => 'PUT',
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));
        return $form;
    }

    private function createAddHolidayCreditForm(EmployeeLeave $holiday, $userid) {
        $form = $this->createForm(new HolidayCreditType(), $holiday, array(
            'action' => $this->generateUrl('user_holiday_add', array('id' => $userid)),
            'method' => 'PUT',
        ));

        return $form;
    }

    public function holidayAddAction(User $user) {
        $holidaycredit = new EmployeeLeave();
        $holidaycredit->setType(3);
        $holidaycredit->setState(2);
        $holidaycredit->setUser($user);
        $form = $this->createAddHolidayCreditForm($holidaycredit, $user->getId());
        $form->handleRequest($this->get('request'));
        if($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($holidaycredit);
            $em->flush();
        }
        return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId())));
    }

    public function holidayDeleteAction(EmployeeLeave $employeeleave) {
        $userid = $employeeleave->getUser()->getId();
        $em = $this->getDoctrine()->getManager();
        $em->remove($employeeleave);
        $em->flush();
        return $this->redirect($this->generateUrl('user_edit', array('id' => $userid)));
    }

    /**
     * Edits an existing User entity.
     *
     */
    public function updateAction(User $entity) {
        $editForm = $this->createEditForm($entity);
        $editForm->handleRequest($this->get('request'));

        if ($editForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();
            return $this->redirect($this->generateUrl('user_edit', array('id' => $entity->getId())));
        }

        return $this->render('GbsLeaveBundle:User:edit.html.twig', array(
            'entity'      => $entity,
            'edit_form'   => $editForm->createView(),
        ));
    }

    /**
     * Import users from LDAP
     */
    public function importAction() {
        $ldapConnection = $this->get('gbs_leave.security.ldap.connection');
        $userManager = $this->get('fos_user.user_manager');
        $search = $ldapConnection->search(array(
                    'base_dn' => 'ou=people, dc=gbs',
                    'filter' => '(objectClass=inetOrgPerson)',
                    ));
        foreach ($search as $ldapUser) {
            if($ldapUser['displayname'][0] !== null && $ldapUser['mail'][0] !== null && $ldapUser['cn'][0] !== null && $userManager->findUserByEmail($ldapUser['mail'][0]) === null) {
                // add this user
                $user = $userManager->createUser();
                $user
                    ->setEnabled(true)
                    ->setUsername($ldapUser['cn'][0])
                    ->setPassword('')
                    ->setDn($ldapUser['dn'])
                    ->setFullName($ldapUser['displayname'][0])
                    ->setLastName($ldapUser['sn'][0])
                    ->setEmail($ldapUser['mail'][0])
                    ->setManager($userManager->findUserByUsername('sebastien'))
                    ;

                $userManager->updateUser($user);

                $this->get('session')->getFlashBag()->add('info','Adding user '.$ldapUser['dn']);
            }
        }
        return $this->redirect($this->generateUrl('user'));
    }
}

<?php

namespace Gbs\LeaveBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Gbs\LeaveBundle\Entity\EmployeeLeave;
use Gbs\LeaveBundle\Form\EmployeeLeaveType;

/**
 * EmployeeLeave controller.
 *
 */
class EmployeeLeaveController extends Controller {
    /**
     * Lists all EmployeeLeave entities.
     *
     */
    public function indexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GbsLeaveBundle:EmployeeLeave')->findBy(
            array('user' => $this->get('security.context')->getToken()->getUser(), 'type' => array(0,1,2,4,5,6,7,8,9,11,12)),
            array('id' => 'DESC')
            );

        return $this->render('GbsLeaveBundle:EmployeeLeave:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function validationIndexAction() {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GbsLeaveBundle:EmployeeLeave')
            ->createQueryBuilder('l')
            ->join('l.user', 'u')
            ->addSelect('u')
            ->leftJoin('u.manager', 'm')
            ->andWhere('l.state = 1')
            ->andWhere('u.manager = :user OR m.manager = :user')
            ->setParameter('user', $this->get('security.context')->getToken()->getUser())
            ->addOrderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult()

            ;

        return $this->render('GbsLeaveBundle:EmployeeLeave:index.html.twig', array(
            'entities' => $entities,
        ));
    }

    public function planningAction($type) {
        $logger = $this->get('logger');
        $em = $this->getDoctrine()->getManager();

        $users = array();
        if($this->get('security.context')->isGranted('LEAVES_SEEALL') || 1==1) {
            $users = $em->getRepository('GbsLeaveBundle:User')->findBy(
                array(),
                array()
                );
        }
        elseif($this->get('security.context')->getToken()->getUser()->getDepartment() !== null) {
            $users = $em->getRepository('GbsLeaveBundle:User')
                ->createQueryBuilder('u')
                ->where('u.department = :dep')
                ->setParameter('dep', $this->get('security.context')->getToken()->getUser()->getDepartment())
                ->getQuery()
                ->getResult();
        }
        else {
            $users = $em->getRepository('GbsLeaveBundle:User')->findBy(
                array('user' => $this->get('security.context')->getToken()->getUser()),
                array()
                );
        }

        $entities = $em->getRepository('GbsLeaveBundle:EmployeeLeave')->findBy(
            array(
                'type' => array(0,1,2,4,5,6,7,8,9,11,12), // leave / medical / exceptional / other
                'state' => array(1,2), // in validation / accepted
                'user' => $users,
            ),
            array()
            );

        $usersByCountry = array();
        foreach ($users as $user) {
            if($user->getCountry()!==null) {

                if(array_key_exists($user->getCountry()->getId(), $usersByCountry)) {
                    $usersByCountry[$user->getCountry()->getId()] .= ','.$user->getId();
                    #$logger->debug("Add array for cty : " . $user->getCountry()->getId() . "(" . $usersByCountry[$user->getCountry()->getId()] . ")");
                }
                else {
                    $usersByCountry[$user->getCountry()->getId()] = $user->getId();
                    #$logger->debug("Create array for cty : " . $user->getCountry()->getId());
                }
            }
        }

        $notworkeddays = $em->getRepository('GbsLeaveBundle:PublicHoliday')->findBy(
            array(
            ),
            array()
            );

        $leaves = array();
        foreach ($entities as $entity) {
            $partday = ($entity->getPartDay()!=0 ? '&#189;' : '');
            $text = $partday . $entity->getTypeShortName();
            //$text .= $entity->getComment()!==null?' ('.$entity->getComment().')':'';

            $leaves[] = array(
                    'start_date' => date_format($entity->getStart(), 'Y-m-d 00:00'),
                    'end_date' => date_format($entity->getEnd(), 'Y-m-d 24:00'),
                    'text'  => $text,
                    'section_id' => $entity->getUser()->getId(),
                    'color' => $entity->getTypeColor(),
                    'textColor' => 'white',
                    );
        }
        foreach ($notworkeddays as $notworkedday) {

            //$logger->debug("Add NWD for cty " . $notworkedday->getCountry()->getId() . " bool: " . $usersByCountry[$notworkedday->getCountry()->getId()]);
            //if(in_array($notworkedday->getCountry()->getId(),$usersByCountry )) {
            $leaves[] = array(
                    'start_date' => date_format($notworkedday->getDate(), 'Y-m-d 00:00'),
                    'end_date' => date_format($notworkedday->getDate(), 'Y-m-d 24:00'),
                    'text'  => 'NWD',
                    'section_id' => $usersByCountry[$notworkedday->getCountry()->getId()],
                    'color' => 'black',
                    'textColor' => 'white',
                    );
            //}
        }

        return $this->render('GbsLeaveBundle:EmployeeLeave:planning.html.twig', array(
            'entities' => $leaves,
            'users' => $this->getUsersOfManager(),
            'type' => $type,
        ));
    }

    private function getUsersOfManager($user=null) {
        $em = $this->getDoctrine()->getManager();

        $users = $em->getRepository('GbsLeaveBundle:User')->findBy(
            array('manager' => $user, 'enabled' => true),
            array()
            );

        $users_ary = array();
        foreach ($users as $son) {
            $display = $son->getFullName();
            $sons = $this->getUsersOfManager($son);
            if(sizeof($sons)==0) {
                $users_ary[] = array(
                    'key' => $son->getId(),
                    'label' => $display,
                    );
            }
            else {
                $users_ary[] = array(
                    'key' => $son->getId(),
                    'label' => $display,
                    'open' => true,
                    'children' => $sons,
                    );
            }
        }
        return $users_ary;
    }

    /**
     * Creates a new EmployeeLeave entity.
     *
     */
    public function createAction(Request $request) {
        $entity = new EmployeeLeave();

        $form = $this->createCreateForm($entity);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if($entity->getUser()===null) {
                $entity->setUser($this->get('security.context')->getToken()->getUser());
            }
            $entity->setCreator($this->get('security.context')->getToken()->getUser());

            // if travelling then drect to validate
            if($entity->getType()==7) {
                $entity->setState(1);
            }

            // if sick leave then no validation
            if($entity->getType()==9) {
                $entity->setState(2);
            }

            // compute the # of days
            $holidayService = $this->get('gbs_leave.holiday');
            $entity->setDays($holidayService->getLeaveDays($entity));

            // check if sick that it is not a 1/2 day
            if($entity->getType()==9 && $entity->getPartday()!=0) {
                $this->get('session')->getFlashBag()->add('warning','Sick leave can\'t be only 1/2 day !');
                return $this->redirect($this->generateUrl('leave_new'));
            }

            // check if sick that the user have enough sick credit
            if($entity->getType()==9 && $holidayService->getSickBalance($entity->getUser()) < $entity->getDays()) {
                $this->get('session')->getFlashBag()->add('warning','No enough sick credit !');
                return $this->redirect($this->generateUrl('leave_new'));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();

            return $this->redirect($this->generateUrl('leave_show', array('id' => $entity->getId())));
        }

        return $this->render('GbsLeaveBundle:EmployeeLeave:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Creates a form to create a EmployeeLeave entity.
     *
     * @param EmployeeLeave $entity The entity
     *
     * @return \Symfony\Component\Form\Form The form
     */
    private function createCreateForm(EmployeeLeave $entity) {
        $form = $this->createForm(new EmployeeLeaveType(), $entity, array(
            'action' => $this->generateUrl('leave_create'),
            'method' => 'POST',
        ));

        $form->add('submit', 'submit', array('label' => 'Create'));

        return $form;
    }

    /**
     * Displays a form to create a new EmployeeLeave entity.
     *
     */
    public function newAction() {
        $entity = new EmployeeLeave();
        $entity->setUser($this->get('security.context')->getToken()->getUser());
        $form   = $this->createCreateForm($entity);

        return $this->render('GbsLeaveBundle:EmployeeLeave:new.html.twig', array(
            'entity' => $entity,
            'form'   => $form->createView(),
        ));
    }

    /**
     * Finds and displays a EmployeeLeave entity.
     *
     */
    public function showAction($id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GbsLeaveBundle:EmployeeLeave')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EmployeeLeave entity.');
        }
        return $this->render('GbsLeaveBundle:EmployeeLeave:show.html.twig', array(
            'entity'      => $entity,
        ));
    }

    /**
     * Displays a form to edit an existing EmployeeLeave entity.
     *
     */
    public function editAction(EmployeeLeave $entity) {
        if($entity->canEdit($this->get('security.context'))) {
            $editForm = $this->createEditForm($entity);

            return $this->render('GbsLeaveBundle:EmployeeLeave:edit.html.twig', array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
            ));
        }
        else {
           throw $this->createNotFoundException('Can\'t edit.');
        }
    }

    /**
    * Creates a form to edit a EmployeeLeave entity.
    *
    * @param EmployeeLeave $entity The entity
    *
    * @return \Symfony\Component\Form\Form The form
    */
    private function createEditForm(EmployeeLeave $entity) {
        $form = $this->createForm(new EmployeeLeaveType(), $entity, array(
            'action' => $this->generateUrl('leave_update', array('id' => $entity->getId())),
            'method' => 'PUT',
            'roleAdmin' => $this->get('security.context')->isGranted('ROLE_LEAVES'),
        ));

        $form->add('submit', 'submit', array('label' => 'Update'));

        return $form;
    }
    /**
     * Edits an existing EmployeeLeave entity.
     *
     */
    public function updateAction(Request $request, $id) {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('GbsLeaveBundle:EmployeeLeave')->find($id);

        if (!$entity) {
            throw $this->createNotFoundException('Unable to find EmployeeLeave entity.');
        }

        if($entity->canEdit($this->get('security.context'))) {
            $editForm = $this->createEditForm($entity);
            $editForm->handleRequest($request);

            if ($editForm->isValid()) {
                $holidayService = $this->get('gbs_leave.holiday');
                $entity->setDays($holidayService->getLeaveDays($entity));
                $em->flush();

                return $this->redirect($this->generateUrl('leave_edit', array('id' => $id)));
            }

            return $this->render('GbsLeaveBundle:EmployeeLeave:edit.html.twig', array(
                'entity'      => $entity,
                'edit_form'   => $editForm->createView(),
            ));
        }
        else {
            throw $this->createNotFoundException('Can\'t edit.');
        }
    }
    /**
     * Deletes a EmployeeLeave entity.
     *
     */
    public function deleteAction(EmployeeLeave $entity) {
        if($entity->canEdit($this->get('security.context'))) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($entity);
            $em->flush();
            $this->get('session')->getFlashBag()->add('notice', 'Leave deleted.');
        }
        else {
            $this->get('session')->getFlashBag()->add('warning',
                'Not possible to delete: not owner or not in draft mode.');
        }

        return $this->redirect($this->generateUrl('leave'));
    }

    public function askForValidationAction(EmployeeLeave $entity) {
        if($entity->canAskForValidation($this->get('security.context'))) {
            $em = $this->getDoctrine()->getManager();
            $entity->setState(1);
            $em->persist($entity);
            $em->flush();

            if($entity->getUser()->getManager() !== null) {
                $message = \Swift_Message::newInstance('GBS Leave Management')
                        ->setFrom('leaves@gbandsmith.com')
                        ->setTo($entity->getUser()->getManager()->getEmail())
                        ->setBody($this->renderView('GbsLeaveBundle:EmployeeLeave:mail_askvalidation.txt.twig',
                            array('leave' => $entity)));
                $this->get('mailer')->send($message);

                $this->get('session')->getFlashBag()->add('notice', 'Leave push to state ask for validation.');
            }
            else {
                // no manager then validate directly !
                $entity->setState(2);
                $em->persist($entity);
                $em->flush();

                $this->get('session')->getFlashBag()->add('notice', 'Direclty accepted because no manager.');
            }


        }
        else {
            $this->get('session')->getFlashBag()->add('warning',
                'Not possible to ask for validation: not owner or not in draft mode.');
        }
        return $this->redirect($this->generateUrl('leave'));
    }

    public function acceptAction(EmployeeLeave $entity) {
        $this->validate($entity, 'accepted');
        return $this->redirect($this->generateUrl('leave_show', array('id' => $entity->getId())));
    }

    public function refuseAction(EmployeeLeave $entity) {
        $this->validate($entity, 'refused');
        return $this->redirect($this->generateUrl('leave_show', array('id' => $entity->getId())));
    }

    private function validate(EmployeeLeave $entity, $type) {
        if($entity->canValidate($this->get('security.context'))) {
            $em = $this->getDoctrine()->getManager();
            $entity->setState(($type=='accepted'?2:3));
            $entity->setValidationUser($this->get('security.context')->getToken()->getUser());
            $entity->setValidationDate(new \DateTime());
            $em->persist($entity);
            $em->flush();

            $message = \Swift_Message::newInstance('GBS Leave Management')
                    ->setFrom('leaves@gbandsmith.com')
                    ->setTo($entity->getUser()->getEmail())
                    //->setCc('leaves@gbandsmith.com')
                    ->setBody($this->renderView('GbsLeaveBundle:EmployeeLeave:mail_validation.txt.twig',
                        array('leave' => $entity,'answer' => $type)));
            // now add in CC the emails set in the Country page
            if($entity->getUser()->getCountry() !== null) {
                $emails = explode(";", $entity->getUser()->getCountry()->getEmails());
                foreach ($emails as $email) {
                    if($email != "") {
                        $message->addCc($email);
                    }
                }


            }

            $this->get('mailer')->send($message);

            $this->get('session')->getFlashBag()->add('notice', 'Leave #'.$entity->getId().' '.$type.' (mail sent to user).');
        }
        else {
            $this->get('session')->getFlashBag()->add('warning',
                'Not possible to validate.');
        }
    }

    public function acceptAllAction($type) {
        $em = $this->getDoctrine()->getManager();

        $entities = $em->getRepository('GbsLeaveBundle:EmployeeLeave')
            ->createQueryBuilder('l')
            ->join('l.user', 'u')
            ->addSelect('u')
            //->leftJoin('u.manager', 'm')
            ->andWhere('l.state = 1')
            ->andWhere('l.type = :type')
            ->andWhere('u.manager = :user')
            //->andWhere('u.manager = :user OR m.manager = :user')
            ->setParameter('type', $type)
            ->setParameter('user', $this->get('security.context')->getToken()->getUser())
            ->addOrderBy('l.id', 'DESC')
            ->getQuery()
            ->getResult()
            ;

        foreach ($entities as $entity) {
            $this->validate($entity, 'accepted');
        }
        return $this->redirect($this->generateUrl('leave_validation'));

    }
}

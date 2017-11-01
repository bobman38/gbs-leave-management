<?php

namespace Gbs\LeaveBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use Gbs\LeaveBundle\Entity\TrGiven;

/**
 * TR controller.
 *
 */
class TrController extends Controller
{

    /**
     * Save TR to DB
     *
     */
    public function saveTrToDatabaseAction($delta) {
        $em = $this->getDoctrine()->getManager();

       $users = $em->getRepository('GbsLeaveBundle:User')->findBy(
            array('tr' => true),
            array()
            );

       $holidayService = $this->get('gbs_leave.holiday');
       $holidayService->computeFirstLastDayMonth($delta);

       foreach ($users as $user) {
            $trGiven = new TrGiven();
            $trGiven->setMonth($holidayService->month);
            $trGiven->setUser($user);
            $trGiven->setNumber($holidayService->getTRByMonth($user, $delta));
            $em->persist($trGiven);
       }
       $em->flush();

        return $this->redirect($this->generateUrl('user_workingdays'));




        $entities = $em->getRepository('GbsLeaveBundle:User')->findAll();

        return $this->render('GbsLeaveBundle:User:index.html.twig', array(
            'entities' => $entities,
        ));
    }
}

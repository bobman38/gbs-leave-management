<?php

namespace Gbs\LeaveBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('GbsLeaveBundle:Default:index.html.twig');
    }
}

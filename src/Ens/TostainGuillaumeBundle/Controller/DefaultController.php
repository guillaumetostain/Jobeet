<?php

namespace Ens\TostainGuillaumeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('EnsTostainGuillaumeBundle:Default:index.html.twig');
    }
}

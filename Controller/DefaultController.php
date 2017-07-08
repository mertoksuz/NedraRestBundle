<?php

namespace MertOksuz\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DefaultController extends Controller
{
    public function indexAction()
    {
        return $this->render('MertOksuzApiBundle:Default:index.html.twig');
    }
}

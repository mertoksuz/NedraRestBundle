<?php

namespace MertOksuz\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ResourceController extends Controller
{
    public function indexAction($name)
    {
        return $this->render('', array('name' => $name));
    }
}

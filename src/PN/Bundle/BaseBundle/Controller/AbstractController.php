<?php

namespace PN\Bundle\BaseBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

abstract class AbstractController extends Controller
{
    protected function em()
    {
        return $this->getDoctrine()->getManager();
    }
}
<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Dashboard controller.
 *
 * @Route("/")
 */
class DashboardController extends Controller {

    /**
     * Lists all Faq entities.
     *
     * @Route("/", name="dashboard", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/dashboard/index.html.twig');
    }

}

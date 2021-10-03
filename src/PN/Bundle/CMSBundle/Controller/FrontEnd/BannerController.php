<?php

namespace PN\Bundle\CMSBundle\Controller\FrontEnd;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Banner controller.
 *
 * @Route("banner")
 */
class BannerController extends Controller {

    /**
     * Lists all Banner entities.
     *
     * @Route("/{placement}", name="fe_banner", methods={"GET"})
     */
    public function BannerAction($placement) {
        $em = $this->getDoctrine()->getManager();

        $search = new \stdClass;
        $search->ordr = ['dir' => NULL, 'column' => 5];
        $search->placement = $placement;
        $banners = $em->getRepository('CMSBundle:Banner')->filter($search, FALSE, 0, 2);

        return $this->render('cms/frontEnd/banner/banner.html.twig', [
                    'banners' => $banners,
        ]);
    }

}

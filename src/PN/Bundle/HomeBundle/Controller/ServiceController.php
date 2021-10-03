<?php

namespace PN\Bundle\HomeBundle\Controller;

use PN\Bundle\CMSBundle\Entity\Category;
use PN\Bundle\CMSBundle\Entity\Project;
use PN\Bundle\CMSBundle\Entity\Service;
use PN\Bundle\CMSBundle\Services\BannerService;
use PN\ServiceBundle\Lib\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Career controller.
 *
 * @Route("service")
 */
class ServiceController extends Controller {

    /**
     * Lists all Service entities.
     *
     * @Route("/{page}",requirements={"page" = "\d+"}, name="fe_service", methods={"GET"})
     */
    public function indexAction($page = 1) {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(4);

        $search = new \stdClass;
        $search->ordr = ["column" => 1, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;

        $count = $em->getRepository(Service::class)->filter($search, true);
        $paginator = new \PN\Bundle\CMSBundle\Lib\Paginator($count, $page, 24);
        $services = $em->getRepository(Service::class)->filter($search, false, $paginator->getLimitStart(),
            $paginator->getPageLimit());

        return $this->render('home/service/index.html.twig', [
            'seoPage' => $seoPage,
            'services' => $services,
            'paginator' => $paginator->getPagination(),
        ]);
    }

    /**
     * Lists and show all Services entities.
     *
     * @Route("/{slug}", name="fe_service_show", methods={"GET"})
     */
    public function showAction(Request $request, $slug) {
        $em = $this->getDoctrine()->getManager();
        $service = $this->get("fe_seo")->getSlug($request, $slug, new Service());
        if ($service instanceof RedirectResponse) {
            return $service;
        }
        if (!$service) {
            throw $this->createNotFoundException();
        }

        $projectsIdsUsingService = $em->getRepository(Service::class)->getProjectsIdsUsingService($service);
        $projectsUsingService = [];
        if(!empty($projectsIdsUsingService)) {
            $projectsUsingService = $this->getProjectsUsingService($projectsIdsUsingService);
        }

        return $this->render('home/service/show.html.twig', [
            'service' => $service,
            'relatedServices' => $this->getRelatedServices($service),
            'projectUsingTheService' => $projectsUsingService,
        ]);
    }

    private function getRelatedServices(Service $service) {
        $em = $this->getDoctrine()->getManager();
        $search = new \stdClass;
        $search->ordr = ["column" => 0, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->notId = $service->getId();

        return $em->getRepository(Service::class)->filter($search, false, 0, 3);
    }

    private function getProjectsUsingService($ids) {
        $em = $this->getDoctrine()->getManager();
        $search = new \stdClass;
        $search->ordr = ["column" => 0, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->ids = $ids;

        return $em->getRepository(Project::class)->filter($search, false, 0, 3);
    }
}

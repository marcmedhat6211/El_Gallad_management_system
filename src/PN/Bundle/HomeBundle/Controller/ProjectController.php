<?php

namespace PN\Bundle\HomeBundle\Controller;

use PN\Bundle\CMSBundle\Entity\Category;
use PN\Bundle\CMSBundle\Entity\Project;
use PN\Bundle\CMSBundle\Services\BannerService;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\ServiceBundle\Lib\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Career controller.
 *
 * @Route("project")
 */
class ProjectController extends Controller {

    /**
     * Lists all Project entities.
     *
     * @Route("/{page}",requirements={"page" = "\d+"}, name="fe_project", methods={"GET"})
     */
    public function indexAction($page = 1) {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(5);

        $search = new \stdClass;
        $search->ordr = ["column" => 1, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;

        $count = $em->getRepository(Project::class)->filter($search, true);
        $paginator = new \PN\Bundle\CMSBundle\Lib\Paginator($count, $page, 24);
        $projects = $em->getRepository(Project::class)->filter($search, false, $paginator->getLimitStart(),
            $paginator->getPageLimit());

        return $this->render('home/project/index.html.twig', [
            'seoPage' => $seoPage,
            'projects' => $projects,
            'paginator' => $paginator->getPagination(),
        ]);
    }

    /**
     * Lists and show all Projects entities.
     *
     * @Route("/{slug}", name="fe_project_show", methods={"GET", "POST"})
     */
    public function showAction(Request $request, $slug) {
        $project = $this->get("fe_seo")->getSlug($request, $slug, new Project());
        if ($project instanceof RedirectResponse) {
            return $project;
        }
        if (!$project) {
            throw $this->createNotFoundException();
        }
        $em = $this->getDoctrine()->getManager();
        $productIdsUsingProject = $em->getRepository(ProductSearch::class)->getProductIdsUsingProject($project);
        $productsUsingProject = [];
        if(!empty($productIdsUsingProject)) {
            $productsUsingProject = $this->getProductsUsingProject($productIdsUsingProject);
        }

        return $this->render('home/project/show.html.twig', [
            'project' => $project,
            'productsUsingProject' => $productsUsingProject,
        ]);
    }

    private function getProductsUsingProject($ids) {
        $em = $this->getDoctrine()->getManager();
        $search = new \stdClass;
        $search->ordr = ["column" => 0, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->ids = $ids;

        return $em->getRepository(ProductSearch::class)->filter($search, false, 0, 12);
    }
}

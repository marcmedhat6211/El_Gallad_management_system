<?php

namespace PN\Bundle\CMSBundle\Controller\FrontEnd;

use http\Env\Request;
use PN\Bundle\CMSBundle\Entity\Blogger;
use PN\Bundle\CMSBundle\Lib\Paginator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Blogger controller.
 *
 * @Route("blogger")
 */
class BloggerController extends Controller {

    /**
     * Lists all Blogger entities.
     *
     * @Route("/{page}",requirements={"page" = "\d+"}, name="fe_blogger", methods={"GET"})
     */
    public function indexAction($page = 1) {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(3);

        $search = new \stdClass;
        $search->ordr = ["column" => 0, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;

        $count = $em->getRepository(Blogger::class)->filter($search, true);
        $paginator = new Paginator($count, $page, 24);
        $blogs = $em->getRepository(Blogger::class)->filter($search, false, $paginator->getLimitStart(),
            $paginator->getPageLimit());

        return $this->render('cms/frontEnd/blogger/index.html.twig', [
            'seoPage' => $seoPage,
            'blogs' => $blogs,
            'paginator' => $paginator->getPagination(),
        ]);
    }

    /**
     * @Route("/{slug}", name="fe_blogger_show", methods={"GET"})
     */
    public function showAction(\Symfony\Component\HttpFoundation\Request $request, $slug)
    {
        $blog = $this->get("fe_seo")->getSlug($request, $slug, new Blogger());
        if ($blog instanceof RedirectResponse) {
            return $blog;
        }
        if (!$blog) {
            throw $this->createNotFoundException();
        }

        return $this->render('cms/frontEnd/blogger/show.html.twig', [
            'blog' => $blog,
            'relatedBlogs' => $this->getRelatedBlogs($blog),
        ]);
    }

    private function getRelatedBlogs(Blogger $blogger) {
        $em = $this->getDoctrine()->getManager();
        $search = new \stdClass;
        $search->ordr = ["column" => 0, "dir" => "DESC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->notId = $blogger->getId();

        return $em->getRepository(Blogger::class)->filter($search, false, 0, 3);
    }
}

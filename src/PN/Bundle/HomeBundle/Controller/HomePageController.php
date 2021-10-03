<?php

namespace PN\Bundle\HomeBundle\Controller;

use PN\Bundle\CMSBundle\Entity\Banner;
use PN\Bundle\CMSBundle\Entity\Blogger;
use PN\Bundle\CMSBundle\Entity\Partner;
use PN\Bundle\CMSBundle\Entity\Service;
use PN\Bundle\CMSBundle\Entity\Team;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\Bundle\ProductBundle\Services\CartService;
use PN\Utils\MailChimp;
use stdClass;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * HomePage controller.
 *
 * @Route("")
 */
class HomePageController extends Controller
{

    /**
     * @Route("", name="fe_home", methods={"GET"})
     */
    public function indexAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(1);
        $featuredProducts = $this->getFeaturedProducts();

        return $this->render('home/homePage/index.html.twig', [
            "seoPage" => $seoPage,
            "banners" => $this->getBanners(),
            "bodyBanner" => $this->getBodyBanner(),
            "categories" => $this->getCategories(),
            "featuredProducts" => $featuredProducts,
            "blogs" => $this->getBlogs(),
            "partners" => $this->getPartners(),
        ]);
    }

    /**
     * @Route("/about/us", name="fe_about", methods={"GET"})
     */
    public function aboutAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(6);

        return $this->render('home/homePage/about.html.twig', [
            "seoPage" => $seoPage,
            "teamMembers" => $this->getTeamMembers(),
        ]);
    }

    /**
     * @Route("/subscribe", name="fe_subscribe", methods={"POST"})
     */
    public function subscribeAction(Request $request, TranslatorInterface $translator)
    {
        // to get api key: click on your photo in the bottom left -> account -> Extras -> API Key -> grab the api key from the list or just add new one
        // to get the audience id: click on audience in the left bar -> manage audience -> settings -> Audience name and defaults -> and just grab the audience id
        $email = $request->request->get('email');
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $MailChimp = new MailChimp('8aa1065b5210d5dac19f9772c4a5d5b9-us5');
            $MailChimp->call('lists/subscribe', array(
                'id' => 'f7fdf01480',
                'email' => array('email' => $email),
                'double_option' => false,
                'update_existing' => true,
                'replace_interests' => false,
            ));
        } else {
            return $this->json([
                "error" => true,
                "message" => $translator->trans("Please enter a valid email")
            ]);
        }

        return $this->json([
            "error" => false,
            "message" => $translator->trans("Subscribed Successfully")
        ]);
    }

    public function menuAction(Request $request, CartService $cartService): Response
    {
        $cart = $cartService->getCart($request);


        return $this->render('fe/menu.html.twig', [
            "request" => $request,
            "cart" => $cart,
            "services" => $this->getServices(),
        ]);
    }

    public function footerAction(Request $request)
    {

        return $this->render('fe/footer.html.twig', [
            "categories" => $this->getCategories(true),
        ]);
    }

    public function searchAction(Request $request)
    {

        return $this->render('fe/_search_popup.html.twig', [
            "categories" => $this->getCategories(),
            "request" => $request,
        ]);
    }

    // PRIVATE METHODS
    private function getTeamMembers()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;

        return $em->getRepository(Team::class)->filter($search, false);
    }

    private function getServices()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;

        return $em->getRepository(Service::class)->filter($search, false, 0, 4);
    }

    private function getCategories($footer = false)
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;

        $limit = $footer ? 5 : 10;

        return $em->getRepository(Category::class)->filter($search, false, 0, $limit);
    }

    private function getBanners()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 2, "dir" => "ASC"];
        $search->placement = 1;
        $search->publish = 1;

        return $em->getRepository(Banner::class)->filter($search, false, 0, 6);
    }

    private function getBodyBanner() {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 2, "dir" => "ASC"];
        $search->placement = 2;
        $search->publish = 1;

        $banner = $em->getRepository(Banner::class)->filter($search, false, 0, 1);
        return $banner ? $banner[0] : [];
    }

    private function getFeaturedProducts()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;
        $search->featured = 1;

        return $em->getRepository(ProductSearch::class)->filter($search, false, 0, 8);
    }

    private function getBlogs()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 0, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;

        return $em->getRepository(Blogger::class)->filter($search, false, 0, 3);
    }

    private function getPartners()
    {
        $em = $this->getDoctrine()->getManager();
        $search = new stdClass;
        $search->ordr = ["column" => 1, "dir" => "ASC"];
        $search->deleted = 0;
        $search->publish = 1;

        return $em->getRepository(Partner::class)->filter($search, false, 0, 6);
    }
}

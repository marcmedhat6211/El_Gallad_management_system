<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\ECommerceBundle\Entity\Coupon;
use PN\Bundle\ECommerceBundle\Entity\CouponHasProduct;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Form\Filter\ProductFilterType;
use PN\Bundle\ProductBundle\Services\ProductService;
use PN\ServiceBundle\Lib\Paginator;
use PN\ServiceBundle\Utils\Date;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Product controller.
 *
 * @Route("/update-group")
 */
class ProductUpdateGroupController extends AbstractController
{

    private $username = null;

    /**
     * Lists all product entities.
     *
     * @Route("/{page}", requirements={"page" = "\d+"}, name="product_group_update_index", methods={"GET"})
     */
    public function indexAction(Request $request, $page = 1)
    {
        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);

        $em = $this->getDoctrine()->getManager();
        $count = $em->getRepository(Product::class)->filter($search, true);
        $paginator = new Paginator($count, $page, 50);
        $products = $em->getRepository(Product::class)->filter($search, $count = false,
            $paginator->getLimitStart(), $paginator->getPageLimit());

        return $this->render('product/admin/productUpdateGroup/index.html.twig', [
            'search' => $search,
            "filter_form" => $filterForm->createView(),
            'products' => $products,
            'paginator' => $paginator->getPagination(),
        ]);
    }

    /**
     * Lists all Product entities.
     *
     * @Route("/group-update/action", name="product_group_update_action", methods={"GET"})
     */
    public function groupUpdateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $em = $this->getDoctrine()->getManager();
        $session = $request->getSession();

        if ($session->has('productSelected') AND count($session->get('productSelected')) > 0) {
            $productSelected = $session->get('productSelected');
            $search = new \stdClass();
            $search->ids = $productSelected;
            $products = $em->getRepository(Product::class)->filter($search, false);
        } elseif ($request->get('filter') == '1') {
            $filterForm = $this->createForm(ProductFilterType::class);
            $filterForm->handleRequest($request);
            $search = $this->get(ProductService::class)->collectSearchData($filterForm);
            $products = $em->getRepository(Product::class)->filter($search, false);

            $productSelected = array();
            foreach ($products as $product) {
                $productSelected[] = $product->getId();
            }
            $session->set('productSelected', $productSelected);
        } else {
            $this->addFlash('error', 'Please add some products');

            return $this->redirectToRoute('product_group_update_index');
        }


        return $this->render("product/admin/productUpdateGroup/groupUpdate.html.twig", [
            'products' => $products,
        ]);
    }

    /**
     * Lists all Product entities.
     *
     * @Route("/group-update/session-ajax", name="product_group_update_session_ajax", methods={"POST"})
     */
    public function productGroupUpdateSessionAjaxAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $session = $request->getSession();
        $id = $request->request->get('id');
        if ($session->has('productSelected')) {
            $productSelected = $session->get('productSelected');
        } else {
            $productSelected = array();
        }

        if (is_numeric($id)) {
            if (!in_array($id, $productSelected)) { // add product in array
                array_push($productSelected, $id);
            } else { // remove product in array
                $key = array_search($id, $productSelected);
                unset($productSelected[$key]);
            }
        } else {
            $type = $request->request->get('type');
            $ids = json_decode($id);
            foreach ($ids as $id) {
                if (isset($type) AND $type == 'add') {
                    if (!in_array($id, $productSelected)) {
                        array_push($productSelected, $id);
                    }
                } else {
                    if (in_array($id, $productSelected)) {
                        $key = array_search($id, $productSelected);
                        unset($productSelected[$key]);
                    }
                }
            }
        }
        $session->set('productSelected', $productSelected);

        return $this->json($productSelected);
    }

    /**
     * Add a Product Price entity.
     *
     * @Route("/group-update/update", name="product_group_update_action_update", methods={"POST"})
     */
    public function productGroupUpdateAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $session = $request->getSession();
        $em = $this->getDoctrine()->getManager();

        $type = $request->request->get('type');
        $data = $request->request->get('data');


        if (!Validate::not_null($type)) {
            $this->addFlash('error', 'Error in Type');

            return $this->redirectToRoute('product_group_update_action');
        }

        $productSelected = $session->get('productSelected');
        if (count($productSelected) == 0) {
            $this->addFlash('error', 'Please add some products');

            return $this->redirectToRoute('product_group_update_action');
        }

        $entities = array();
        foreach ($productSelected as $value) {
            if (Validate::not_null($value)) {
                $product = $em->getRepository(Product::class)->find($value);
                $entities[] = $product;
            }
        }
        $userName = $this->get('user')->getUserName();
        $n = 0;
        if ($type == 'checkbox') {
            foreach ($entities as $entity) {
                $n++;
                $this->updateCheckboxs($entity, $data);
            }
        } elseif ($type == 'promotion') {
            if (!isset($data['removeDiscount'])) {
                if (isset($data['discount']) AND (!Validate::not_null($data['discount']) OR !is_numeric($data['discount']))) {
                    $this->addFlash('error', 'Please enter the promotion discount');

                    return $this->redirectToRoute('product_group_update_action');
                }
                if (Validate::not_null($data['expiryDate']) AND !Validate::date($data['expiryDate'])) {
                    $this->addFlash('error', 'Please enter a valid promotion expiry date');

                    return $this->redirectToRoute('product_group_update_action');
                }
            }
            $promotionalExpiryDate = null;
            if (isset($data['expiryDate']) AND Validate::not_null($data['expiryDate'])) {
                $promotionalExpiryDate = Date::convertDateFormat($data['expiryDate'], Date::DATE_FORMAT3,
                    Date::DATE_FORMAT2);
                $promotionalExpiryDate = new \DateTime($promotionalExpiryDate);
            }

            foreach ($entities as $entity) {
                $n++;
                $this->updatePromotionPrice($entity, $data, $promotionalExpiryDate);
            }
        } elseif ($type == 'content') {
            foreach ($entities as $entity) {
                $n++;
                $this->updateContent($entity, $data);
            }
        }
        $em->flush();

        $this->addFlash('success', $n . ' Products updated successfully');
        if ($request->request->get('action') == "saveAndNext") {
            return $this->redirectToRoute('product_group_update_action');
        }
        $session->remove('productSelected');

        return $this->redirectToRoute('product_group_update_index');
    }

    private function getUsername()
    {
        if ($this->username == null) {
            return $this->username = $this->get('user')->getUserName();
        }

        return $this->username;
    }

    private function updateCheckboxs(Product $product, $data)
    {
        $em = $this->getDoctrine()->getManager();
        if (isset($data['featured'])) {
            $product->setFeatured(true);
        } elseif (isset($data['notFeatured'])) {
            $product->setFeatured(false);
        }
        if (isset($data['publish'])) {
            $product->setPublish(true);
        } elseif (isset($data['unpublish'])) {
            $product->setPublish(false);
        }

        if (isset($data['premium'])) {
            $product->setPremium(true);
        } elseif (isset($data['notPremium'])) {
            $product->setPremium(false);
        }

        if (isset($data['newArrival'])) {
            $product->setNewArrival(true);
        } elseif (isset($data['notNewArrival'])) {
            $product->setNewArrival(false);
        }
        $product->setModifiedBy($this->getUsername());
        $em->persist($product);
    }

    private function updateCoupon(Product $product, Coupon $coupon)
    {
        $em = $this->getDoctrine()->getManager();

        $checkCouponHasProduct = $em->getRepository('ECommerceBundle:Coupon')->checkCouponHasProduct($coupon->getId(),
            $product->getId());
        if (!$checkCouponHasProduct) {
            $couponHasProduct = new CouponHasProduct();
            $couponHasProduct->setCoupon($coupon);
            $couponHasProduct->setProduct($product);
            $coupon->addCouponHasProduct($couponHasProduct);
            $em->persist($coupon);
        }
    }

    private function updatePromotionPrice(Product $product, $data, \DateTime $promotionalExpiryDate = null)
    {
        $em = $this->getDoctrine()->getManager();

//        foreach ($product->getPrices() as $price) {
//            if (isset($data['removeDiscount'])) {
//                $price->setPromotionalPrice(null);
//                $price->setPromotionalExpiryDate(null);
//            } else {
//                $promotionalPrice = $price->getPrice() - ($price->getPrice() / 100) * $data['discount'];
//                $price->setPromotionalPrice($promotionalPrice);
//                $price->setPromotionalExpiryDate($promotionalExpiryDate);
//            }
//            $em->persist($price);
//        }
        $product->setModifiedBy($this->getUsername());
        $em->persist($product);
    }

    private function updateContent(Product $product, $data)
    {
        $em = $this->getDoctrine()->getManager();

        $content = $product->getPost()->getContent();

        if (!isset($data['disabledDescription'])) {
            $content['description'] = $data['description'];
        }

        if (!isset($data['disabledBrief'])) {
            $content['brief'] = $data['brief'];
        }

        $product->getPost()->setContent($content);
        $product->setModifiedBy($this->getUsername());
        $em->persist($product);
    }

}

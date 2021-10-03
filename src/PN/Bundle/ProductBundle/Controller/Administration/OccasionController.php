<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\ProductBundle\Entity\Occasion;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductHasOccasion;
use PN\Bundle\ProductBundle\Form\Filter\ProductFilterType;
use PN\Bundle\ProductBundle\Form\OccasionType;
use PN\Bundle\ProductBundle\Services\ProductService;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Occasion controller.
 *
 * @Route("/occasion")
 */
class OccasionController extends AbstractController
{

    /**
     * Lists all Occasion entities.
     *
     * @Route("/", name="occasion_index", methods={"GET"})
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        return $this->render("product/admin/occasion/index.html.twig");
    }

    /**
     * Displays a form to create a new Occasion entity.
     *
     * @Route("/new", name="occasion_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $occasion = new Occasion();
        $form = $this->createForm(OccasionType::class, $occasion);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $occasion->setCreator($userName);
            $occasion->setModifiedBy($userName);
            $em->persist($occasion);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');
            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('occasion_manage_product', ["id" => $occasion->getId()]);
            }

            return $this->redirectToRoute('occasion_index');
        }

        return $this->render("product/admin/occasion/new.html.twig", [
            'occasion' => $occasion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="occasion_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $form = $this->createForm(OccasionType::class, $occasion);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            if($occasion->getActive()){
                $em->getRepository(Occasion::class)->clearAllActive();
            }
            $em->persist($occasion);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('occasion_manage_product', ["id" => $occasion->getId()]);
            }

            return $this->redirectToRoute('occasion_index');
        }

        return $this->render("product/admin/occasion/edit.html.twig", [
            'occasion' => $occasion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="occasion_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();

        $occasion->setDeleted(new \DateTime());
        $em->persist($occasion);
        $em->flush();

        $this->addFlash('success', 'Successfully deleted');

        return $this->redirectToRoute('occasion_index');
    }

    /**
     * Active a Occasion entity.
     *
     * @Route("/active/{id}", name="occasion_active", methods={"POST"})
     */
    public function activeAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Occasion::class)->clearAllActive();

        $occasion->setActive(true);
        $em->persist($occasion);
        $em->flush();

        return $this->redirectToRoute('occasion_index');
    }

    /**
     * @Route("/deactivate", name="occasion_deactivate", methods={"POST"})
     */
    public function deactivateAction()
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();
        $em->getRepository(Occasion::class)->clearAllActive();

        return $this->redirectToRoute('occasion_index');
    }

    /**
     * @Route("/data/table", defaults={"_format": "json"}, name="occasion_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");


        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->deleted = 0;

        $count = $em->getRepository(Occasion::class)->filter($search, true);
        $occasions = $em->getRepository(Occasion::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/occasion/datatable.json.twig", [
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "occasions" => $occasions,
        ]);
    }

    /**
     * @Route("/{id}/clone", name="occasion_clone", methods={"GET", "POST"})
     */
    public function cloneAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $em = $this->getDoctrine()->getManager();

        $newEntity = clone $occasion;
        $i = 0;
        do {
            if ($i == 0) {
                $slug = $newEntity->getSeo()->getSlug();
            } else {
                $slug = $newEntity->getSeo()->getSlug().'-'.$i;
            }
            $slugIfExist = $em->getRepository(Seo::class)->findOneBy(array(
                'seoBaseRoute' => $newEntity->getSeo()->getSeoBaseRoute()->getId(),
                'slug' => $slug,
                'deleted' => false,
            ));
            $i++;
        } while ($slugIfExist != null);

        $newEntity->getSeo()->setSlug($slug);
        $form = $this->createForm(OccasionType::class, $newEntity);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {

            $userName = $this->get('user')->getUserName();
            $newEntity->setCreator($userName);
            $newEntity->setModifiedBy($userName);

            foreach ($occasion->getProductHasOccasions() as $productHasOccasion) {
                $newEntity->addProductHasOccasion(clone $productHasOccasion);
            }

            $em->persist($newEntity);
            $em->flush();

            $this->addFlash('success', 'Successfully cloned');

            return $this->redirectToRoute('occasion_manage_product', ["id" => $newEntity->getId()]);
        }


        return $this->render("product/admin/occasion/new.html.twig", [
            'occasion' => $occasion,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Lists all Coupon entities.
     *
     * @Route("/product/{id}", requirements={"id" = "\d+"}, name="occasion_manage_product", methods={"GET"})
     */
    public function manageProductAction(Request $request, Occasion $occasion)
    {
        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);

        return $this->render("product/admin/occasion/manageProduct.html.twig", [
            'occasion' => $occasion,
            "search" => $search,
            "filter_form" => $filterForm->createView(),
        ]);
    }

    /**
     * Lists all product entities.
     *
     * @Route("/product/data/table/{id}", requirements={"id" = "\d+"}, name="occasion_manage_product_datatable", methods={"GET"})
     */
    public function manageProductDatatableAction(Request $request, Occasion $occasion)
    {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");


        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);
        if (Validate::not_null($srch['value'])) {
            $search->string = $srch['value'];
        }
        $search->ordr = $ordr[0];
        $search->deleted = 0;
        $search->currentOccasionId = $occasion->getId();

        $count = $em->getRepository(Product::class)->filter($search, true);
        $products = $em->getRepository(Product::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/occasion/manageProductDatatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "products" => $products,
            )
        );
    }

    /**
     * add or update product tp occasion.
     *
     * @Route("/update-product/{id}", requirements={"id" = "\d+"}, name="occasion_manage_product_update_ajax", methods={"POST"})
     */
    public function addOrUpdateProductToOccasionAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $productId = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();

        if (!is_numeric($productId)) {
            $type = $request->request->get('type');
            $productIds = json_decode($productId);
            $products = $em->getRepository(Product::class)->findBy(["id" => $productIds]);
            foreach ($products as $product) {
                $checkOccasionHasProduct = $em->getRepository(ProductHasOccasion::class)->findOneBy([
                    "product" => $product,
                    "occasion" => $occasion,
                ]);
                if (isset($type) AND $type == 'add') {
                    if (!$checkOccasionHasProduct) {
                        $this->addProductToOccasion($product, $occasion);
                    }
                } else {
                    if ($checkOccasionHasProduct) {
                        $em->remove($checkOccasionHasProduct);
                    }
                }
            }

            $return = 1;
        } else {
            $product = $em->getRepository(Product::class)->find($productId);
            $return = $this->addOrDeleteProductInOccasion($product, $occasion);

        }
        $em->flush();

        return $this->json(['value' => $return]);
    }

    private function addOrDeleteProductInOccasion(Product $product, Occasion $occasion): int
    {
        $em = $this->getDoctrine()->getManager();
        $checkOccasionHasProduct = $em->getRepository(ProductHasOccasion::class)->findOneBy([
            "product" => $product,
            "occasion" => $occasion,
        ]);
        if ($checkOccasionHasProduct) {
            $return = 0;
            $em->remove($checkOccasionHasProduct);

        } else {
            $return = 1;
            $this->addProductToOccasion($product, $occasion);
        }

        return $return;
    }

    private function addProductToOccasion(Product $product, Occasion $occasion)
    {
        $em = $this->getDoctrine()->getManager();
        $productHasOccasion = new ProductHasOccasion();
        $productHasOccasion->setProduct($product);
        $productHasOccasion->setOccasion($occasion);
        $em->persist($productHasOccasion);
    }

    /**
     * Remove all product from occasion entity.
     *
     * @Route("/clear-product/{id}", name="occasion_clear_products", methods={"POST"})
     */
    public function clearProductAction(Request $request, Occasion $occasion)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();
        $em->getRepository(ProductHasOccasion::class)->removeProductByOccasion($occasion);

        return $this->redirectToRoute('occasion_index');
    }

}

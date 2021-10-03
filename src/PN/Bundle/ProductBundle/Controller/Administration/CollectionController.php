<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\ContentBundle\Entity\Post;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\Bundle\ProductBundle\Entity\Collection;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductHasCollection;
use PN\Bundle\ProductBundle\Form\CollectionType;
use PN\Bundle\ProductBundle\Form\Filter\ProductFilterType;
use PN\Bundle\ProductBundle\Services\ProductService;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Collection controller.
 *
 * @Route("/collection")
 */
class CollectionController extends AbstractController
{

    /**
     * Lists all Collection entities.
     *
     * @Route("/", name="collection_index", methods={"GET"})
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        return $this->render("product/admin/collection/index.html.twig");
    }

    /**
     * Displays a form to create a new Collection entity.
     *
     * @Route("/new", name="collection_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $collection = new Collection();
        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $collection->setCreator($userName);
            $collection->setModifiedBy($userName);
            $em->persist($collection);
            $em->flush();

//            $uploadImage = $this->uploadImage($request, $form, $collection);
//            if (!$uploadImage) {
//
//                return $this->redirectToRoute('collection_edit',['id'=>$collection->getId()]);
//            }

            $this->addFlash('success', 'Successfully saved');

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('collection_manage_product', ["id" => $collection->getId()]);
            }

            return $this->redirectToRoute('collection_index');
        }

        return $this->render("product/admin/collection/new.html.twig", [
            'collection' => $collection,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="collection_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Collection $collection)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $form = $this->createForm(CollectionType::class, $collection);
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $collection->setModifiedBy($userName);
            $em->flush();

//            $uploadImage = $this->uploadImage($request, $form, $collection);
//            if (!$uploadImage) {
//
//                return $this->redirectToRoute('collection_edit',['id'=>$collection->getId()]);
//            }

            $this->addFlash('success', 'Successfully saved');

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('collection_manage_product', ["id" => $collection->getId()]);
            }

            return $this->redirectToRoute('collection_index');
        }

        return $this->render("product/admin/collection/edit.html.twig", [
            'collection' => $collection,
            'form' => $form->createView(),
        ]);
    }

//    private function uploadImage(Request $request, Form $form, Collection $entity)
//    {
//        if ($form->has("image")) {
//            $file = $form->get("image")->get('file')->getData();
//            if ($file == null) {
//                return true;
//            }
//
//           return $this->get('pn_media_upload_image')->uploadSingleImage($entity->getPost(), $file, 3, $request);
//        }
//
//        return true;
//    }

    /**
     * @Route("/{id}", name="collection_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Collection $collection)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();

        $collection->setDeleted(new \DateTime());
        $em->persist($collection);
        $em->flush();

        $this->addFlash('success', 'Successfully deleted');

        return $this->redirectToRoute('collection_index');
    }

    /**
     * @Route("/data/table", defaults={"_format": "json"}, name="collection_datatable", methods={"GET"})
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

        $count = $em->getRepository(Collection::class)->filter($search, true);
        $collections = $em->getRepository(Collection::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/collection/datatable.json.twig", [
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "collections" => $collections,
        ]);
    }

    /**
     * @Route("/{id}/clone", name="collection_clone", methods={"GET", "POST"})
     */
    public function cloneAction(Request $request, Collection $collection)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $em = $this->getDoctrine()->getManager();

        $newEntity = clone $collection;
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
        $form = $this->createForm(CollectionType::class, $newEntity);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {

            $userName = $this->get('user')->getUserName();
            $newEntity->setCreator($userName);
            $newEntity->setModifiedBy($userName);

            foreach ($collection->getProductHasCollections() as $productHasCollection) {
                $newEntity->addProductHasCollection(clone $productHasCollection);
            }

            $em->persist($newEntity);
            $em->flush();

            $this->addFlash('success', 'Successfully cloned');

            return $this->redirectToRoute('collection_manage_product', ["id" => $newEntity->getId()]);
        }


        return $this->render("product/admin/collection/new.html.twig", [
            'collection' => $collection,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Lists all Coupon entities.
     *
     * @Route("/product/{id}", requirements={"id" = "\d+"}, name="collection_manage_product", methods={"GET"})
     */
    public function manageProductAction(Request $request, Collection $collection)
    {
        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);

        return $this->render("product/admin/collection/manageProduct.html.twig", [
            'collection' => $collection,
            "search" => $search,
            "filter_form" => $filterForm->createView(),
        ]);
    }

    /**
     * Lists all product entities.
     *
     * @Route("/product/data/table/{id}", requirements={"id" = "\d+"}, name="collection_manage_product_datatable", methods={"GET"})
     */
    public function manageProductDatatableAction(Request $request, Collection $collection)
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
        $search->currentCollectionId = $collection->getId();

        $count = $em->getRepository(Product::class)->filter($search, true);
        $products = $em->getRepository(Product::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/collection/manageProductDatatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "products" => $products,
            )
        );
    }

    /**
     * add or update product tp collection.
     *
     * @Route("/update-product/{id}", requirements={"id" = "\d+"}, name="collection_manage_product_update_ajax", methods={"POST"})
     */
    public function addOrUpdateProductToCollectionAction(Request $request, Collection $collection)
    {

        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $productId = $request->request->get('id');
        $em = $this->getDoctrine()->getManager();

        if (!is_numeric($productId)) {
            $type = $request->request->get('type');
            $productIds = json_decode($productId);
            $products = $em->getRepository(Product::class)->findBy(["id" => $productIds]);
            foreach ($products as $product) {
                $checkCollectionHasProduct = $em->getRepository(ProductHasCollection::class)->findOneBy([
                    "product" => $product,
                    "collection" => $collection,
                ]);
                if (isset($type) AND $type == 'add') {
                    if (!$checkCollectionHasProduct) {
                        $this->addProductToCollection($product, $collection);
                    }
                } else {
                    if ($checkCollectionHasProduct) {
                        $em->remove($checkCollectionHasProduct);
                    }
                }
            }

            $return = 1;
        } else {
            $product = $em->getRepository(Product::class)->find($productId);
            $return = $this->addOrDeleteProductInCollection($product, $collection);

        }
        $em->flush();

        return $this->json(['value' => $return]);
    }

    private function addOrDeleteProductInCollection(Product $product, Collection $collection): int
    {
        $em = $this->getDoctrine()->getManager();
        $checkCollectionHasProduct = $em->getRepository(ProductHasCollection::class)->findOneBy([
            "product" => $product,
            "collection" => $collection,
        ]);
        if ($checkCollectionHasProduct) {
            $return = 0;
            $em->remove($checkCollectionHasProduct);

        } else {
            $return = 1;
            $this->addProductToCollection($product, $collection);
        }

        return $return;
    }

    private function addProductToCollection(Product $product, Collection $collection)
    {
        $em = $this->getDoctrine()->getManager();
        $productHasCollection = new ProductHasCollection();
        $productHasCollection->setProduct($product);
        $productHasCollection->setCollection($collection);
        $em->persist($productHasCollection);
    }

    /**
     * Remove all product from collection entity.
     *
     * @Route("/clear-product/{id}", name="collection_clear_products", methods={"POST"})
     */
    public function clearProductAction(Request $request, Collection $collection)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();
        $em->getRepository(ProductHasCollection::class)->removeProductByCollection($collection);

        return $this->redirectToRoute('collection_index');
    }

}

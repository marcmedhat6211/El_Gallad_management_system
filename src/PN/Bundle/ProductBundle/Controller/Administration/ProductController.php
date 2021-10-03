<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use Doctrine\ORM\EntityRepository;
use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductDetails;
use PN\Bundle\ProductBundle\Entity\ProductInsertCronJobData;
use PN\Bundle\ProductBundle\Entity\ProductSearch;
use PN\Bundle\ProductBundle\Form\Filter\ProductFilterType;
use PN\Bundle\ProductBundle\Form\ProductType;
use PN\Bundle\ProductBundle\Services\AttributeService;
use PN\Bundle\ProductBundle\Services\CategoryService;
use PN\Bundle\ProductBundle\Services\ProductService;
use PN\Bundle\SeoBundle\Entity\Seo;
use PN\Bundle\UserBundle\Entity\User;
use PN\LocaleBundle\Entity\Language;
use PN\ServiceBundle\Lib\Paginator;
use PN\ServiceBundle\Utils\Date;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use function MongoDB\BSON\toRelaxedExtendedJSON;

/**
 * Product controller.
 *
 * @Route("")
 */
class ProductController extends AbstractController
{

    /**
     * Lists all product entities.
     *
     * @Route("/{category}", requirements={"category" = "\d+"}, name="product_index", methods={"GET"})
     */
    public function indexAction(Request $request, Category $category = null)
    {
        $this->denyAccessUnlessGranted([User::ROLE_ADMIN]);
        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($category);

        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);

        return $this->render('product/admin/product/index.html.twig', [
            "category" => $category,
            "search" => $search,
            "filter_form" => $filterForm->createView(),
            'categoryParents' => $categoryParents,
        ]);
    }

    /**
     * Creates a new product entity.
     *
     * @Route("/new/{category}", requirements={"category" = "\d+"}, name="product_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Category $category = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $product = new Product();
        $details = new ProductDetails();
        $currency = $em->getRepository(Currency::class)->find(Currency::EGP);
        $details->setCurrency($currency);
        $product->setDetails($details);
        $product->setCategory($category);
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('attribute_index', ['id' => $product->getId()]);
            }

            $this->addFlash('success', 'Successfully saved');


            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/admin/product/new.html.twig', array(
            'product' => $product,
            'currentCategory' => $product->getCategory(),
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/{id}/edit", requirements={"id" = "\d+"}, name="product_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Product $product)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($product);
            $em->flush();

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('attribute_index', ['id' => $product->getId()]);
            }

            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }

        return $this->render('product/admin/product/edit.html.twig', array(
            'product' => $product,
            'currentCategory' => $product->getCategory(),
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing product entity.
     *
     * @Route("/get-specs-form-ajax", requirements={"id" = "\d+"}, name="product_specs_form_ajax", methods={ "POST"})
     */
    public function getSpecsFormAjaxAction(Request $request)
    {
        $productId = $request->request->get("productId");
        $categoryId = $request->request->get("categoryId");
        if (!Validate::not_null($categoryId)) {
            return $this->json(["error" => 0, "message" => "", "html" => null]);
        }
        $product = null;

        if (Validate::not_null($productId)) {
            $product = $this->em()->getRepository(Product::class)->find($productId);
            if (!$product) {
                return $this->json(["error" => 1, "message" => "Product not found", "html" => null]);
            }
        }
        $category = $this->em()->getRepository(Category::class)->find($categoryId);
        if (!$category) {
            return $this->json(["error" => 1, "message" => "Category not found", "html" => null]);
        }
        $specsForm = $this->get(AttributeService::class)->getSpecsForm($category, $product);
        $html = $this->renderView("product/admin/product/_form_specs.html.twig", [
            "currentCategory" => $category,
            "form" => $specsForm->createView(),
        ]);

        return $this->json(["error" => 0, "message" => "", "html" => $html]);
    }

    /**
     * Deletes a product entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="product_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Product $product)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $this->delete($product);

        return $this->redirectToRoute('product_index');
    }

    private function delete(Product $product)
    {
        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $product->setDeletedBy($userName);
        $product->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($product);
        $em->flush();
    }

    /**
     * @Route("/delete-pdf/{id}", name="product_pdf_delete", methods={"GET"})
     */
    public function deletePDFAction(Request $request, Product $product)
    {
        $document = $product->getDetails()->getTearSheet();
        if (!$document) {
            return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
        }
        $product->getDetails()->setTearSheet(null);
        $this->em()->persist($product);
        $this->em()->flush();

        $this->em()->remove($document);
        $this->em()->flush();

        return $this->redirectToRoute('product_edit', ['id' => $product->getId()]);
    }

    /**
     * Lists all product entities.
     *
     * @Route("/data/table/{category}", requirements={"category" = "\d+"}, defaults={"_format": "json"}, name="product_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request, Category $category = null)
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
        if ($category != null) {
            $search->category = $category->getId();
        }

        $count = $em->getRepository(Product::class)->filter($search, true);
        $products = $em->getRepository(Product::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/product/datatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "products" => $products,
            )
        );
    }

    /**
     * @Route("/clone/{id}", name="product_clone", methods={"GET", "POST"})
     */
    public function cloneAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $newEntity = clone $product;
        $i = 0;
        do {
            if ($i == 0) {
                $slug = $newEntity->getSeo()->getSlug();
            } else {
                $slug = $newEntity->getSeo()->getSlug() . '-' . $i;
            }
            $slugIfExist = $em->getRepository(Seo::class)->findOneBy(array(
                'seoBaseRoute' => $newEntity->getSeo()->getSeoBaseRoute()->getId(),
                'slug' => $slug,
                'deleted' => false,
            ));
            $i++;
        } while ($slugIfExist != null);

        $newEntity->getSeo()->setSlug($slug);
        $form = $this->createForm(ProductType::class, $newEntity);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $newEntity->setCreator($userName);
            $newEntity->setModifiedBy($userName);
            $em->persist($newEntity);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');

//            $images = $product->getPost()->getImages();
//            foreach ($images as $image) {
//                $oldImagePath = $image->getAbsoluteExtension();
//                $galleryTempImage = "/tmp/".$image->getId();
//                copy($oldImagePath, $galleryTempImage);
//                $this->get('pn_media_upload_image')->uploadSingleImageByPath($newEntity->getPost(), $galleryTempImage,
//                    1, $request, $image->getImageType());
//            }


            $document = $product->getDetails()->getTearSheet();
            if ($document) {
                $oldDocumentPath = $document->getAbsoluteExtension();
                $documentTempImage = "/tmp/" . $document->getId();
                copy($oldDocumentPath, $documentTempImage);
                $document = $this->get('pn_media_upload_document')->uploadSingleDocumentByPath($newEntity->getDetails(),
                    $documentTempImage, 101, $request, null, 'tearSheet');
                $em->persist($document);
                $em->flush();
            }

            return $this->redirectToRoute('product_edit', array('id' => $newEntity->getId()));
        }

        return $this->render('product/admin/product/new.html.twig', array(
            'product' => $newEntity,
            'currentCategory' => $newEntity->getCategory(),
            'form' => $form->createView(),
        ));
    }

//    /**
//     * search product ajax.
//     *
//     * @Route("/related/product/ajax", name="related_product_select_ajax", methods={"GET"})
//     */
//    public function searchSelect2Action(Request $request)
//    {
//        $em = $this->getDoctrine()->getManager();
//        $page = ($request->query->has('page')) ? $request->get('page') : 1;
//
//        $search = new \stdClass;
//        $search->admin = true;
//        $search->deleted = 0;
//        $search->publish = 1;
//        $search->string = $request->get('q');
//
//        $count = $em->getRepository(Product::class)->filter($search, true);
//        $paginator = new Paginator($count, $page, 10);
//        $entities = $em->getRepository(Product::class)->filter($search, false, $paginator->getLimitStart(),
//            $paginator->getPageLimit());
//
//        $paginationFlag = false;
//        if (isset($paginator->getPagination()['last']) and $paginator->getPagination()['last'] != $page) {
//            $paginationFlag = true;
//        }
//
//        $returnArray = [
//            'results' => [],
//            'pagination' => $paginationFlag,
//        ];
//
//        foreach ($entities as $entity) {
//            $returnArray['results'][] = [
//                'id' => $entity->getId(),
//            ];
//        }
//
//        return $this->json($returnArray);
//    }

    /**
     * @Route("/upload/csv", requirements={"id" = "\d+"}, name="product_upload_csv", methods={"GET", "POST"})
     */
    public function uploadCSVAction(Request $request)
    {
        $form = $this->createFormBuilder()
            ->add('file', FileType::class, array(
                "label" => "CSV file",
                "required" => true,
                "attr" => array(
                    "class" => "form-control",
                    "accept" => ".csv",
                ),
            ))
            ->getForm();
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        $search = new \stdClass();
        $search->hasError = false;
        $noOfProductInQueue = $em->getRepository('ProductBundle:ProductInsertCronJobData')->filter($search, true);

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('file')->getData();
            $mimeType = $file->getClientMimeType();
            //            $name = $file->getClientOriginalName();
            if (!in_array($mimeType, ['text/csv', 'application/vnd.ms-excel'])) {
                $this->addFlash('error', 'This file type is not acceptable');

                return $this->render("product/admin/product/uploadCSV.html.twig", [
                    'noOfProductInQueue' => $noOfProductInQueue,
                    'form' => $form->createView(),
                ]);
            }

//            TODO:REMOVE THIS
//            $vendor = $form->get('vendor')->getData();
//
////            $handle = fopen($file->getPathname(), 'r');
////            $validate = $this->validateCsv($handle, $vendor);
////            if (!$validate) {
////                return $this->render("product/admin/product/uploadCSV.html.twig", [
////                    'noOfProductInQueue' => $noOfProductInQueue,
////                    'form' => $form->createView(),
////                ]);
////            }
            $handle = fopen($file->getPathname(), 'r');

            $row = 0;
            $successRows = 0;

            while (($data = fgetcsv($handle)) !== false) {
                $row++;
                if ($row == 1) {
                    continue;
                }
                $successRows++;
                $rowAsObject = $this->convertCsvRowToObject($data);
                $productInsertCronJobData = $em->getRepository('ProductBundle:ProductInsertCronJobData')->findOneBy(["sku" => $rowAsObject->sku]);
                if (!$productInsertCronJobData) {
                    $productInsertCronJobData = new ProductInsertCronJobData();
                }

                $productInsertCronJobData->setSku($rowAsObject->sku);
                $productInsertCronJobData->setTitle($rowAsObject->title);
                $productInsertCronJobData->setDescription($rowAsObject->description);
                $productInsertCronJobData->setCategoryId($rowAsObject->categoryId);
//                $productInsertCronJobData->setPrice($rowAsObject->price);
                $productInsertCronJobData->setMaterial($rowAsObject->material);
                $productInsertCronJobData->setWidth($rowAsObject->width);
                $productInsertCronJobData->setHeight($rowAsObject->height);
                $productInsertCronJobData->setDepth($rowAsObject->depth);
                $productInsertCronJobData->setCreator($this->get('user')->getUserName());
                $productInsertCronJobData->setImageUrl($rowAsObject->imageUrl);
                $em->persist($productInsertCronJobData);
            }

            $em->flush();
            if ($successRows > 0) {
                $this->addFlash('success', "$successRows product(s) added in queue");
            }

            return $this->redirectToRoute('product_upload_csv');
        }

        return $this->render("product/admin/product/uploadCSV.html.twig", [
            'noOfProductInQueue' => $noOfProductInQueue,
            'form' => $form->createView(),
        ]);
    }

    private function convertCsvRowToObject($data)
    {

        $convertToHtml = function ($str) {
            $str = nl2br($str);
            $str = trim($str);
            $search = array('&rsquo;', '&nbsp;', '&bull;', "\n", "\t", "\r", "\v", "\e");

            return str_replace($search, '', $str);
        };
        $clearEscapedCharacters = function ($str) {
            $str = trim($str);
            $search = array('&rsquo;', '&nbsp;', '&bull;', "\n", "\t", "\r", "\v", "\e");

            return str_replace($search, '', $str);
        };
        $clearComma = function ($str) {
            $str = trim($str);

            return (float)str_replace(",", '', $str);
        };


        $product = new \stdClass;
        $product->sku = isset($data[0]) ? $clearEscapedCharacters($data[0]) : null;
        $product->title = isset($data[1]) ? $clearEscapedCharacters($data[1]) : null;
        $product->description = isset($data[2]) ? $convertToHtml($data[2]) : null;
        $product->imageUrl = isset($data[3]) ? trim($data[3]) : null;
        $product->categoryId = isset($data[4]) ? trim($data[4]) : null;
//        $product->price = isset($data[5]) ? $clearComma($data[5]) : null;
        $product->material = isset($data[6]) ? trim($data[6]) : null;
        $product->width = (isset($data[7]) and $data[7] != "") ? $clearComma($data[7]) : null;
        $product->height = (isset($data[8]) and $data[8] != "") ? $clearComma($data[8]) : null;
        $product->depth = (isset($data[9]) and $data[9] != "") ? $clearComma($data[9]) : null;

        return $product;
    }

    //TODO: REMOVE VENDOR
    private function validateCsv($handle, Vendor $seller)
    {
        $em = $this->getDoctrine()->getManager();

        $return = true;
        $rowNumber = 0;
        while (($data = fgetcsv($handle)) != false) {
            if ($rowNumber == 0) {
                $rowNumber++;
                continue;
            }
            $rowAsObject = $this->convertCsvRowToObject($data);

            if (!Validate::not_null($rowAsObject->title)) {
                $this->addFlash('error', "Row Number $rowNumber does not contain a title");
                $return = false;
            }
            if (!Validate::not_null($rowAsObject->categoryId)) {
                $this->addFlash('error', "Row Number $rowNumber does not contain a Category ID");
                $return = false;
            } else {
                $checkIsLastChild = $em->getRepository('ProductBundle:Category')->findOneBy([
                    "id" => $rowAsObject->categoryId,
                    "deleted" => null,
                ]);
                if ($checkIsLastChild == null or $checkIsLastChild->getChildren()->count() > 0) {
                    $this->addFlash('error', 'Row Number ' . $rowNumber . ', the category is not correct');
                    $return = false;
                }
            }
            if (!Validate::not_null($rowAsObject->sku)) {
                $this->addFlash('error', "Row Number $rowNumber does not contain a sku");
                $return = false;
            } else {
                $checkSkuIsExist = $em->getRepository(Product::class)->findOneBy(["sku" => $rowAsObject->sku]);
                if ($checkSkuIsExist != null) {
                    $this->addFlash('error', 'Row Number ' . $rowNumber . ', SKU is already exist "' . $rowAsObject->sku . '"');
                    $return = false;
                }
            }
//            if (Validate::not_null($rowAsObject->price) and !is_numeric($rowAsObject->price)) {
//                $this->addFlash('error', 'Row Number '.$rowNumber.', price must be a number');
//                $return = false;
//
//            }
            if (Validate::not_null($rowAsObject->width) and !is_numeric($rowAsObject->width) and !is_float($rowAsObject->width)) {
                $this->addFlash('error', 'Row Number ' . $rowNumber . ', width must be a number');
                $return = false;
            }
            if (Validate::not_null($rowAsObject->height) and !is_numeric($rowAsObject->height) and !is_float($rowAsObject->height)) {
                $this->addFlash('error', 'Row Number ' . $rowNumber . ', height must be a number');
                $return = false;
            }
            if (Validate::not_null($rowAsObject->depth) and !is_numeric($rowAsObject->depth) and !is_float($rowAsObject->depth)) {
                $this->addFlash('error', 'Row Number ' . $rowNumber . ', depth must be a number');
                $return = false;
            }

            $rowNumber++;
        }

        if ($rowNumber <= 1) {
            $this->addFlash('error', "The uploaded file is empty");
            $return = false;
        } elseif ($rowNumber > 501) {
            $this->addFlash('error', "This file exceeds the given capacity, as it has more than 500 rows.");
            $return = false;
        }

        return $return;
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/download/sample", requirements={"id" = "\d+"}, name="product_download_csv_sample", methods={"GET"})
     */
    public function downloadCSVSampleAction(Request $request)
    {
        $list = [
            [
                'SKU',
                'Title',
                'Description',
                'Image URL',
                'Sub category ID',
                'Price',
                'Material',
                'Width',
                'Height',
                'Depth',
            ],
            [
                '67368123-TABLE',
                'Table',
                'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                'https://google.com/image.png',
                '33',
                '1500',
                'Material',
                '2',
                '3',
                '60',
            ],
        ];
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($list as $fields) {
            fputcsv($f, $fields, ",");
        }
        fseek($f, 0);

        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="products-example.csv";');

        fpassthru($f);

        exit;
    }

    /**
     * Deletes a Merchant entity.
     *
     * @Route("/mass-delete", name="product_mass_delete", methods={"POST"})
     */
    public function massDeleteAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $ids = $request->request->get('ids');
        if (!is_array($ids)) {
            return $this->json(['error' => 1, "message" => "Please enter select"]);
        }

        $products = $em->getRepository(Product::class)->findBy(["id" => $ids]);
        foreach ($products as $product) {
            $this->delete($product);
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * @Route("/export-csv", name="product_export_csv", methods={"GET"})
     */
    public function exportCSVAction(Request $request)
    {


        $em = $this->getDoctrine()->getManager();

        $list[] = [
            "#",
            "SKU",
            "Title",
            "Price",
            "Category",
            "Created",
            "Published",
            "Featured",
            "Description",
        ];

        $filterForm = $this->createForm(ProductFilterType::class);
        $filterForm->handleRequest($request);
        $search = $this->get(ProductService::class)->collectSearchData($filterForm);
        $products = $em->getRepository(ProductSearch::class)->filter($search, false);
        foreach ($products as $product) {
            $list = array_merge($list, $this->exportCsvRow($product));
        }
        $f = fopen('php://memory', 'w');
        // loop over the input array
        foreach ($list as $fields) {
            fputcsv($f, $fields, ",");
        }
        fseek($f, 0);

        // tell the browser it's going to be a csv file
        header('Content-Type: application/csv');
        // tell the browser we want to save it instead of displaying it
        header('Content-Disposition: attachment; filename="product-' . date("Y-m-d") . '.csv";');

        fpassthru($f);

        exit;
    }


    private function exportCsvRow(Product $product)
    {
        $description = "";
        $categoryTitle = $product->getCategory()->getTitle();
        $category = $product->getCategory();
        while ($category->getParent() != null) {
            $category = $category->getParent();
            $categoryTitle = $category->getTitle() . " - " . $categoryTitle;
        }

        if (array_key_exists("description", $product->getPost()->getContent())) {
            $description = strip_tags($product->getPost()->getContent()['description']);
        }

        $return[] = [
            $product->getId(),
            $product->getSku() ? $product->getSku() : 'N/A',
            $product->getTitle(),
            $product->minPrice,
            $categoryTitle,
            $product->getCreated()->format(Date::DATE_FORMAT6),
            ($product->getPublish()) ? "Yes" : "No",
            ($product->getFeatured()) ? "Yes" : "No",
            $description,
        ];


        return $return;
    }

    /**
     * @Route("/prepare/bulk-update", name="product_prepare_to_bulk_update", methods={"GET"})
     */
    public function prepareProductToBulkUpdateAction(Request $request)
    {
        $productIds = $request->query->get("ids");
        if (!is_array($productIds) and Validate::not_null($productIds)) {
            $productIds = [$productIds];
        } else {
            if (!is_array($productIds)) {
                $productIds = [];
            }
        }

        $session = $request->getSession();
        $session->set('productSelected', $productIds);

        return $this->redirectToRoute("product_group_update_action");
    }

    /**
     * @Route("/fancy-tree/categories/{id}", defaults={"id" = null}, name="product_category_fancy_tree", methods={"GET"})
     */
    public function getCategoriesFancyTreeAction(Request $request, Product $product = null)
    {
        $categoriesArr = $this->get(CategoryService::class)->getCategoryForFancyTree($product);

        return $this->json($categoriesArr);
    }

}

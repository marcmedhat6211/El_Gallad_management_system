<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Form\CategoryType;
use PN\Bundle\ProductBundle\Services\CategoryService;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("category")
 */
class CategoryController extends AbstractController
{

    private $maximalDepthLevel = 1;

    /**
     * Lists all category entities.
     *
     * @Route("/{parentCategory}", requirements={"parentCategory" = "\d+"}, name="category_index", methods={"GET"})
     */
    public function indexAction(Category $parentCategory = null)
    {
        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($parentCategory);

        return $this->render('product/admin/category/index.html.twig', [
            'parentCategory' => $parentCategory,
            'categoryParents' => $categoryParents,
        ]);
    }

    /**
     * Creates a new category entity.
     *
     * @Route("/new/{parent}", requirements={"parent" = "\d+"}, name="category_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, $parent = null)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        if ($parent != null) {
            $parent = $em->getRepository(Category::class)->find($parent);
        }

        $category = new Category();
        $form = $this->createForm(CategoryType::class, $category);

        if ($parent) {
            $category->setParent($parent);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $depth = $this->getDepth($category);
            $category->setDepth($depth);

            $levelOneCategory = $this->getLevelOne($category);
            $category->setLevelOne($levelOneCategory);

            $parentConcat = $this->getParentConcat($parent);
            $category->setParentConcatIds($parentConcat);

            $em->persist($category);
            $em->flush();


            $category->setConcatIds($category->getId());
            $this->updateParentConcatIds($category, $parent);

            $em->persist($category);
            $em->flush();

//            $uploadImage = $this->uploadImage($request, $form, $category);
            $this->addFlash('success', 'Successfully saved');

            if ($category->getParent()) {
                return $this->redirectToRoute('category_index', ['parentCategory' => $category->getParent()->getId()]);
            }

            return $this->redirectToRoute('category_index');
        }
        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($parent);

        return $this->render('product/admin/category/new.html.twig', array(
            'category' => $category,
            'categoryParents' => $categoryParents,
            'form' => $form->createView(),
        ));
    }


//    private function uploadImage(Request $request, \Symfony\Component\Form\Form $form, Category $entity) {
//        $file = $form->get("image")->get("file")->getData();
//        return $this->get('pn_media_upload_image')->uploadSingleImage($entity, $file, 103, $request);
//    }

    private function getDepth(Category $category): int
    {
        $depth = 1;
        if ($category->getParent() == null) {
            return $depth;
        }

        $parent = $category->getParent();
        while ($parent != null) {
            $parent = $parent->getParent();
            $depth++;
        }

        return $depth;
    }

    private function getLevelOne(Category $category)
    {
        $levelOneCategory = $category->getParent();
        if (!$levelOneCategory) {
            return $category;
        }

        while ($levelOneCategory->getParent()) {
            $levelOneCategory = $levelOneCategory->getParent();
        }

        return $levelOneCategory;
    }

    private function getParentConcat(Category $parent = null)
    {
        if ($parent == null) {
            return null;
        }
        $parentConcat = [];
        $parentCategory = $parent;
        while ($parentCategory) {
            $parentConcat[] = $parentCategory->getId();
            $parentCategory = $parentCategory->getParent();
        }

        return implode(",", $parentConcat);
    }

    private function updateParentConcatIds(Category $category, Category $parent = null)
    {
        if ($parent == null) {
            return false;
        }
        $em = $this->getDoctrine()->getManager();
        $top = $parent;
        while ($top) {
            $concat = $top->getConcatIds();
            $concat .= "," . $category->getId();
            $top->setConcatIds($concat);
            $em->persist($top);
            $top = $top->getParent();
        }
        $em->flush();

        return true;
    }

    /**
     * Displays a form to edit an existing category entity.
     *
     * @Route("/{id}/edit", name="category_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Category $category)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CategoryType::class, $category);


        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $category->setModifiedBy($userName);
            $em->flush();

//            $uploadImage = $this->uploadImage($request, $form, $category);
            $this->addFlash('success', 'Successfully saved');

            if ($request->request->get("action") == "saveAndNext") {
                return $this->redirectToRoute('attribute_index', ["id" => $category->getId()]);
            }

            if ($category->getParent()) {
                return $this->redirectToRoute('category_index', ['parentCategory' => $category->getParent()->getId()]);
            }

            return $this->redirectToRoute('category_index');
        }
        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($category->getParent());

        return $this->render('product/admin/category/edit.html.twig', array(
            'category' => $category,
            'categoryParents' => $categoryParents,
            'form' => $form->createView(),
        ));
    }

    /**
     * Deletes a category entity.
     *
     * @Route("/{id}", requirements={"id" = "\d+"}, name="category_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Category $category)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        if (count($category->getChildren()) > 0) {
            $this->addFlash("error", "You can't delete this item!");
            if ($category->getParent()) {
                return $this->redirectToRoute('category_index', ["parentCategory" => $category->getParent()->getId()]);
            }

            return $this->redirectToRoute('category_index');
        }


        $search = new \stdClass;
        $search->deleted = 0;
        $search->category = $category->getId();
        $count = $em->getRepository('ProductBundle:Product')->filter($search, true);
        if ($count > 0) {
            $this->addFlash("error",
                "You can't remove this category because $count or more products are attached to it");
            if ($category->getParent()) {
                return $this->redirectToRoute('category_index', ["parentCategory" => $category->getParent()->getId()]);
            }

            return $this->redirectToRoute('category_index');
        }
        if ($category->getParent()) {
            $top = $category->getParent();
            while ($top) {
                $concat = $top->getConcatIds();
                $concatArray = explode(',', $concat);
                $concatArray = array_merge(array_diff($concatArray, [$category->getId()]));
                $concatIds = implode(',', $concatArray);
                $top->setConcatIds($concatIds);
                $em->persist($top);

                $top = $top->getParent();
            }
        }

        $userName = $this->get('user')->getUserName();
        $category->setDeletedBy($userName);
        $category->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($category);
        $em->flush();

        if ($category->getParent()) {
            return $this->redirectToRoute('category_index', ["parentCategory" => $category->getParent()->getId()]);
        }

        return $this->redirectToRoute('category_index');
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/data/table/{id}", requirements={"id" = "\d+"}, defaults={"_format": "json"}, name="category_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request, $id = null)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN', 'ROLE_MANAGE_PRODUCTS']);

        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");

        $search = new \stdClass;
        $search->deleted = 0;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->parent = "";
        if ($id) {
            $parentCategory = $em->getRepository(Category::class)->find($id);
            $search->parent = $parentCategory->getId();
        }
        $count = $em->getRepository(Category::class)->filter($search, true);
        $categories = $em->getRepository(Category::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/category/datatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "categories" => $categories,
                "maximalDepthLevel" => $this->maximalDepthLevel,
            )
        );
    }

}

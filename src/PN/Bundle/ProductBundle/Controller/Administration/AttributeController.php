<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\CMSBundle\Lib\Paginator;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\Category;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductHasAttribute;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\Bundle\ProductBundle\Form\AttributeType;
use PN\Bundle\ProductBundle\Form\SubAttributeType;
use PN\Bundle\ProductBundle\Services\CategoryService;
use PN\Bundle\ProductBundle\Services\SubAttributeService;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Attribute controller.
 *
 * @Route("/attribute")
 */
class AttributeController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{

    /**
     * Lists all Attribute entities.
     *
     * @Route("/{id}", name="attribute_index", methods={"GET"})
     */
    public function indexAction(Request $request, Product $product)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);
//        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($category);

        return $this->render("product/admin/attribute/index.html.twig", [
            'product' => $product,
//            'categoryParents' => $categoryParents,
        ]);
    }

    /**
     * Displays a form to create a new Attribute entity.
     *
     * @Route("/new/{id}", name="attribute_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request, Product $product)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $attribute = new Attribute();
        $attribute->setProduct($product);
        $form = $this->createForm(AttributeType::class, $attribute);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attribute);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');
            if (in_array($attribute->getType(), [Attribute::TYPE_DROPDOWN])) {
                return $this->redirectToRoute('attribute_edit', ["id" => $attribute->getId()]);
            }

            return $this->redirectToRoute('attribute_index', ["id" => $product->getId()]);
        }

//        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($attribute->getCategory());

        return $this->render("product/admin/attribute/new.html.twig", [
            'attribute' => $attribute,
//            'categoryParents' => $categoryParents,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit/{page}", requirements={"id"="\d+", "page"="\d+"}, name="attribute_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Attribute $attribute, $page = 1)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);
        $em = $this->getDoctrine()->getManager();

        $isUsed = $em->getRepository(ProductHasAttribute::class)->countByAttribute($attribute);
        $form = $this->createForm(AttributeType::class, $attribute,[
            "disableTypeField" => ($isUsed > 0) ?  true: false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $attribute->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('attribute_edit', ['id' => $attribute->getId()]);
        }


//        $categoryParents = $this->get(CategoryService::class)->parentsByChildId($attribute->getCategory());
        $subAttributeForm = $this->createForm(SubAttributeType::class, new SubAttribute(), [
            "action" => $this->generateUrl("sub_attribute_new", ["id" => $attribute->getId()]),
        ]);

        $subAttributeData = $this->get(SubAttributeService::class)->getEditSubAttributesForm($attribute, $page);

        return $this->render("product/admin/attribute/edit.html.twig", [
            'attribute' => $attribute,
//            'categoryParents' => $categoryParents,
            'form' => $form->createView(),
            'new_sub_attribute_form' => $subAttributeForm->createView(),
            'edit_sub_attribute_form' => $subAttributeData->form->createView(),
            'paginator' => $subAttributeData->paginator->getPagination(),
        ]);
    }



    /**
     * @Route("/{id}", name="attribute_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Attribute $attribute)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $attribute->setDeleted(new \DateTime());
        $attribute->setDeletedBy($userName);
        $em->persist($attribute);
        $em->flush();

        $this->addFlash('success', 'Successfully deleted');

        return $this->redirectToRoute('attribute_index', ["id" => $attribute->getId()]);
    }

    /**
     * @Route("/data/table/{id}", defaults={"_format": "json"}, name="attribute_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request, Product $product)
    {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");


        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->product = $product->getId();
        $search->deleted = 0;

        $count = $em->getRepository(Attribute::class)->filter($search, true);
        $attributes = $em->getRepository(Attribute::class)->filter($search, false, $start, $length);

        return $this->render("product/admin/attribute/datatable.json.twig", [
            "recordsTotal" => $count,
            "recordsFiltered" => $count,
            "attributes" => $attributes,
        ]);
    }

}

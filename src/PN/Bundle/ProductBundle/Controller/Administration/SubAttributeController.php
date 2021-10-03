<?php

namespace PN\Bundle\ProductBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\ProductBundle\Entity\Attribute;
use PN\Bundle\ProductBundle\Entity\ProductHasAttribute;
use PN\Bundle\ProductBundle\Entity\SubAttribute;
use PN\Bundle\ProductBundle\Form\SubAttributeType;
use PN\Bundle\ProductBundle\Services\SubAttributeService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Attribute controller.
 *
 * @Route("/sub-attribute")
 */
class SubAttributeController extends AbstractController
{

    /**
     * Displays a form to create a new SubAttribute entity.
     *
     * @Route("/new/{id}", name="sub_attribute_new", methods={"POST"})
     */
    public function newAction(Request $request, Attribute $attribute)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $subAttribute = new SubAttribute();
        $subAttribute->setAttribute($attribute);
        $form = $this->createForm(SubAttributeType::class, $subAttribute);
        $form->handleRequest($request);


        if ($form->isValid() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($subAttribute);
            $em->flush();
            $subAttributeForm = $this->createForm(SubAttributeType::class, new SubAttribute(), [
                "action" => $this->generateUrl("sub_attribute_new", ["id" => $attribute->getId()]),
            ]);

            $subAttributeData = $this->get(SubAttributeService::class)->getEditSubAttributesForm($attribute, 1);
            $html = $this->renderView("product/admin/attribute/subAttribute/_edit_sub_attributes_form.html.twig", [
                'edit_sub_attribute_form' => $subAttributeData->form->createView(),
                'paginator' => $subAttributeData->paginator->getPagination(),
                'new_sub_attribute_form' => $subAttributeForm->createView(),
            ]);

            return $this->json(["error" => 0, "message" => "Successfully saved", "html" => $html]);
        }

        return $this->json(["error" => 1, "message" => "Error"]);
    }

    /**
     * @Route("/{id}/edit/{page}", requirements={"id"="\d+", "page"="\d+"}, name="sub_attribute_edit", methods={"POST"})
     */
    public function editAction(Request $request, Attribute $attribute, $page)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $subAttributeData = $this->get(SubAttributeService::class)->getEditSubAttributesForm($attribute, $page);
        $form = $subAttributeData->form;
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('attribute_edit', ['id' => $attribute->getId(), "page" => $page]);
        }

        return $this->redirectToRoute('attribute_edit', ['id' => $attribute->getId(), "page" => $page]);
    }

    /**
     * @Route("/{id}/delete", name="sub_attribute_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, SubAttribute $subAttribute)
    {
        $this->denyAccessUnlessGranted(['ROLE_ADMIN']);

        $em = $this->getDoctrine()->getManager();
        $noOfUsage = $em->getRepository(ProductHasAttribute::class)->countBySubAttribute($subAttribute);
        if ($noOfUsage > 0) {
            return $this->json([
                "error" => 1,
                "message" => "You can't delete this item because it is already used in products",
            ]);
        }


        $userName = $this->get('user')->getUserName();
        $subAttribute->setDeleted(new \DateTime());
        $subAttribute->setDeletedBy($userName);
        $em->persist($subAttribute);
        $em->flush();

        return $this->json(["error" => 0, "message" => "Successfully deleted"]);
    }
}

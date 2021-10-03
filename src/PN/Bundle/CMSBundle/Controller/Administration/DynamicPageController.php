<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\DynamicPage;
use PN\Bundle\CMSBundle\Form\DynamicPageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dynamicpage controller.
 *
 * @Route("dynamicpage")
 */
class DynamicPageController extends Controller {

    /**
     * Lists all dynamicPage entities.
     *
     * @Route("/", name="dynamicpage_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/dynamicPage/index.html.twig');
    }

    /**
     * Creates a new dynamicPage entity.
     *
     * @Route("/new", name="dynamicpage_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $dynamicPage = new DynamicPage();
        $form = $this->createForm(DynamicPageType::class, $dynamicPage);
        $formOptions = $form->get('post')->get('brief')->getConfig()->getOptions();
        $formOptions['required'] = false;

        $form->get('post')->add('brief', TextareaType::class, $formOptions);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $dynamicPage->setCreator($userName);
            $dynamicPage->setModifiedBy($userName);
            $em->persist($dynamicPage);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('post_set_images', array(
                        'id' => $dynamicPage->getPost()->getId(),
                            )
            );
        }

        return $this->render('cms/admin/dynamicPage/new.html.twig', array(
                    'dynamicPage' => $dynamicPage,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing dynamicPage entity.
     *
     * @Route("/{id}/edit", name="dynamicpage_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, DynamicPage $dynamicPage) {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $editForm = $this->createForm(DynamicPageType::class, $dynamicPage);
        $formOptions = $editForm->get('post')->get('brief')->getConfig()->getOptions();
        $formOptions['required'] = false;

        $editForm->get('post')->add('brief', TextareaType::class, $formOptions);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $this->get('user')->getUserName();
            $dynamicPage->setModifiedBy($userName);

            $em->flush();
            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('dynamicpage_edit', array('id' => $dynamicPage->getId()));
        }

        return $this->render('cms/admin/dynamicPage/edit.html.twig', array(
                    'dynamicPage' => $dynamicPage,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a dynamicPage entity.
     *
     * @Route("/{id}", name="dynamicpage_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, DynamicPage $dynamicPage) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $cantDeleted = [1, 2];
        $em = $this->getDoctrine()->getManager();
        if (in_array($dynamicPage->getId(), $cantDeleted)) {
            $this->addFlash('error', 'Can not remove this dynamic page');
            return $this->redirectToRoute('dynamicpage_index');
        }
        $em->remove($dynamicPage);
        $em->flush();

        return $this->redirectToRoute('dynamicpage_index');
    }

    /**
     * Lists all dynamicPage entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="dynamicpage_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");


        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];

        $count = $em->getRepository('CMSBundle:DynamicPage')->filter($search, TRUE);
        $dynamicPages = $em->getRepository('CMSBundle:DynamicPage')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/dynamicPage/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "dynamicPages" => $dynamicPages,
                        )
        );
    }

}

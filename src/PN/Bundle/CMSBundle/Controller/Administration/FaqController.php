<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Faq;
use PN\Bundle\CMSBundle\Form\FaqType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Faq controller.
 *
 * @Route("faq")
 */
class FaqController extends Controller {

    /**
     * Lists all faq entities.
     *
     * @Route("/", name="faq_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/faq/index.html.twig');
    }

    /**
     * Creates a new faq entity.
     *
     * @Route("/new", name="faq_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $faq = new Faq();
        $form = $this->createForm(FaqType::class, $faq);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $faq->setCreator($userName);
            $faq->setModifiedBy($userName);
            $em->persist($faq);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('faq_index');
        }

        return $this->render('cms/admin/faq/new.html.twig', array(
                    'faq' => $faq,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing faq entity.
     *
     * @Route("/{id}/edit", name="faq_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Faq $faq) {
        $editForm = $this->createForm(FaqType::class, $faq);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $this->get('user')->getUserName();
            $faq->setModifiedBy($userName);
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('faq_edit', array('id' => $faq->getId()));
        }

        return $this->render('cms/admin/faq/edit.html.twig', array(
                    'faq' => $faq,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a faq entity.
     *
     * @Route("/{id}", name="faq_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Faq $faq) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($faq);
        $em->flush();

        return $this->redirectToRoute('faq_index');
    }

    /**
     * Lists all faq entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="faq_datatable", methods={"GET"})
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

        $count = $em->getRepository('CMSBundle:Faq')->filter($search, TRUE);
        $faqs = $em->getRepository('CMSBundle:Faq')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/faq/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "faqs" => $faqs,
                        )
        );
    }

}

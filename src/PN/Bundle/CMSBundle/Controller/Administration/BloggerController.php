<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Blogger;
use PN\Bundle\CMSBundle\Entity\BloggerTag;
use PN\Bundle\CMSBundle\Form\BloggerTagType;
use PN\Bundle\CMSBundle\Form\BloggerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Blogger controller.
 *
 * @Route("blogger")
 */
class BloggerController extends Controller {

    /**
     * Lists all blogger entities.
     *
     * @Route("/", name="blogger_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/blogger/index.html.twig');
    }

    /**
     * Creates a new blogger entity.
     *
     * @Route("/new", name="blogger_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $blogger = new Blogger();
        $form = $this->createForm(BloggerType::class, $blogger);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $blogger->setCreator($userName);
            $blogger->setModifiedBy($userName);

            $em->persist($blogger);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('post_set_images', array(
                        'id' => $blogger->getPost()->getId(),
                            )
            );
        }

        return $this->render('cms/admin/blogger/new.html.twig', array(
                    'blogger' => $blogger,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing blogger entity.
     *
     * @Route("/{id}/edit", name="blogger_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Blogger $blogger) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $editForm = $this->createForm(BloggerType::class, $blogger);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $this->get('user')->getUserName();
            $blogger->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('blogger_edit', array('id' => $blogger->getId()));
        }
        return $this->render('cms/admin/blogger/edit.html.twig', array(
                    'blogger' => $blogger,
                    'edit_form' => $editForm->createView(),
        ));
    }

    /**

      /**
     * Deletes a blogger entity.
     *
     * @Route("/{id}", name="blogger_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Blogger $blogger) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $blogger->setDeletedBy($userName);
        $blogger->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($blogger);
        $em->flush();

        return $this->redirectToRoute('blogger_index');
    }

    /**
     * Lists all Blogger entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="blogger_datatable", methods={"GET"})
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
        $search->deleted = 0;

        $count = $em->getRepository('CMSBundle:Blogger')->filter($search, TRUE);
        $bloggers = $em->getRepository('CMSBundle:Blogger')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/blogger/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "bloggers" => $bloggers,
                        )
        );
    }

}

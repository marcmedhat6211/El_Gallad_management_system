<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Partner;
use PN\Bundle\CMSBundle\Form\PartnerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Partner controller.
 *
 * @Route("client")
 */
class PartnerController extends Controller {

    /**
     * Lists all partner entities.
     *
     * @Route("/", name="partner_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/partner/index.html.twig');
    }

    /**
     * Creates a new partner entity.
     *
     * @Route("/new", name="partner_new" ,methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $userName = $this->get('user')->getUserName();

            $partner->setCreator($userName);
            $partner->setModifiedBy($userName);
            $em->persist($partner);
            $em->flush();

            $uploadImage = $this->uploadImage($request, $form, $partner);
            if ($uploadImage != false) {
                $this->addFlash('success', 'Successfully saved');
                return $this->redirectToRoute('partner_index');
            }

            return $this->redirectToRoute('partner_edit', ["id" => $partner->getId()]);
        }


        return $this->render('cms/admin/partner/new.html.twig', array(
                    'partner' => $partner,
                    'form' => $form->createView(),
        ));
    }

    private function uploadImage(Request $request, \Symfony\Component\Form\Form $form, Partner $entity) {
        $file = $form->get("image")->get("file")->getData();
        return $this->get('pn_media_upload_image')->uploadSingleImage($entity, $file, 101, $request);
    }

    /**
     * Displays a form to edit an existing partner entity.
     *
     * @Route("/{id}/edit", name="partner_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Partner $partner) {
        $editForm = $this->createForm(PartnerType::class, $partner);
        $editForm->handleRequest($request);


        if ($editForm->isSubmitted() && $editForm->isValid()) {

            $userName = $this->get('user')->getUserName();
            $partner->setModifiedBy($userName);
            $this->getDoctrine()->getManager()->flush();

            $uploadImage = $this->uploadImage($request, $editForm, $partner);
            if ($uploadImage != false) {
                $this->addFlash('success', 'Successfully updated');
                return $this->redirectToRoute('partner_edit', array('id' => $partner->getId()));
            }


            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('partner_edit', array('id' => $partner->getId()));
        }

        return $this->render('cms/admin/partner/edit.html.twig', array(
                    'partner' => $partner,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a partner entity.
     *
     * @Route("/{id}", name="partner_delete" ,methods={"DELETE"})
     */
    public function deleteAction(Request $request, Partner $partner) {


        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $partner->setDeletedBy($userName);
        $partner->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($partner);
        $em->flush();

        return $this->redirectToRoute('partner_index');
    }

    /**
     * Lists all Partner entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="partner_datatable" ,methods={"GET"} )
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


        $count = $em->getRepository('CMSBundle:Partner')->filter($search, TRUE);
        $partners = $em->getRepository('CMSBundle:Partner')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/partner/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "partners" => $partners,
                        )
        );
    }

}

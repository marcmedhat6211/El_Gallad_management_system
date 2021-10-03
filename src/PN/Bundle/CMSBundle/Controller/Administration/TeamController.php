<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Team;
use PN\Bundle\CMSBundle\Form\TeamType;
use PN\Bundle\MediaBundle\Entity\Image;
use PN\MediaBundle\Form\SingleImageType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Team controller.
 *
 * @Route("team")
 */
class TeamController extends Controller {

    /**
     * Lists all Team entities.
     *
     * @Route("/", name="team_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/team/index.html.twig');
    }

    /**
     * Creates a new Team entity.
     *
     * @Route("/new", name="team_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $team = new Team();
        $form = $this->createForm(TeamType::class, $team);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $team->setCreator($userName);
            $team->setModifiedBy($userName);
            $em->persist($team);
            $em->flush();

            $uploadImage = $this->uploadImage($request, $form, $team);
            if ($uploadImage) {
                $this->addFlash('success', 'Successfully saved');
                return $this->redirectToRoute('team_index');
            }

            $this->addFlash('success', 'Successfully saved');
            return $this->redirectToRoute('team_index');
        }

        return $this->render('cms/admin/team/new.html.twig', array(
                    'team' => $team,
                    'form' => $form->createView(),
        ));
    }

    private function uploadImage(Request $request, \Symfony\Component\Form\Form $form, Team $entity) {
        $file = $form->get("image")->get("file")->getData();
        return $this->get('pn_media_upload_image')->uploadSingleImage($entity, $file, 102, $request);
    }

    /**
     * Displays a form to edit an existing Team entity.
     *
     * @Route("/{id}/edit", name="team_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Team $team) {
        $editForm = $this->createForm(TeamType::class, $team);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $this->get('user')->getUserName();
            $team->setModifiedBy($userName);

            $this->getDoctrine()->getManager()->flush();

            $uploadImage = $this->uploadImage($request, $editForm, $team);
            if ($uploadImage != false) {
                $this->addFlash('success', 'Successfully updated');
                return $this->redirectToRoute('team_edit', array('id' => $team->getId()));
            }
        }

        return $this->render('cms/admin/team/edit.html.twig', array(
                    'team' => $team,
                    'form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a Team entity.
     *
     * @Route("/{id}", name="team_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Team $team) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();
        $image = $team->getImage();
        if ($image) {
            $image->removeUpload();
        }

        $userName = $this->get('user')->getUserName();
        $team->setDeletedBy($userName);
        $team->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($team);
        $em->flush();

        return $this->redirectToRoute('team_index');
    }

    /**
     * Lists all Team entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="team_datatable", methods={"GET"})
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


        $count = $em->getRepository('CMSBundle:Team')->filter($search, TRUE);
        $teams = $em->getRepository('CMSBundle:Team')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/team/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "teams" => $teams,
                        )
        );
    }

}

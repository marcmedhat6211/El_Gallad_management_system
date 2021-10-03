<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Banner;
use PN\Bundle\CMSBundle\Form\BannerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Banner controller.
 *
 * @Route("banner")
 */
class BannerController extends Controller {

    /**
     * Lists all banner entities.
     *
     * @Route("/", name="banner_index", methods={"GET"})
     */
    public function indexAction() {
        return $this->render('cms/admin/banner/index.html.twig');
    }

    /**
     * Creates a new banner entity.
     *
     * @Route("/new", name="banner_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        $banner = new Banner();
        $form = $this->createForm(BannerType::class, $banner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $userName = $this->get('user')->getUserName();
            $banner->setCreator($userName);
            $banner->setModifiedBy($userName);
            $em->persist($banner);
            $em->flush();

            $uploadImage = $this->uploadImage($request, $form, $banner);
            if ($uploadImage != false) {
                $this->addFlash('success', 'Successfully saved');
                return $this->redirectToRoute('banner_index');
            }
            return $this->redirectToRoute('banner_edit', ["id" => $banner->getId()]);
        }

        return $this->render('cms/admin/banner/new.html.twig', array(
            'banner' => $banner,
            'form' => $form->createView(),
        ));
    }

    private function uploadImage(Request $request, \Symfony\Component\Form\Form $form, Banner $banner) {
        $placementDimensions = Banner::$placementDimensions[$banner->getPlacement()];
        $width = $placementDimensions['width'];
        $height = $placementDimensions['height'];

        $file = $form->get("image")->get("file")->getData();
        if ($file == null) {
            return true;
        }
        list($currentWidth, $currentHeight) = getimagesize($file->getRealPath());

        if ($width != null and $currentWidth != $width) {
            $this->addFlash("error", "This image dimensions are wrong, please upload one with the right dimensions");
            return false;
        }
        if ($height != null and $currentHeight != $height) {
            $this->addFlash("error", "This image dimensions are wrong, please upload one with the right dimensions");
            return false;
        }

        $this->get('pn_media_upload_image')->uploadSingleImage($banner, $file, 100, $request);
        return true;
    }

    /**
     * Displays a form to edit an existing banner entity.
     *
     * @Route("/{id}/edit", name="banner_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Banner $banner) {
        $editForm = $this->createForm(BannerType::class, $banner);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $userName = $this->get('user')->getUserName();
            $banner->setModifiedBy($userName);

            $this->getDoctrine()->getManager()->flush();

            $uploadImage = $this->uploadImage($request, $editForm, $banner);
            if ($uploadImage != false) {
                $this->addFlash('success', 'Successfully saved');
                return $this->redirectToRoute('banner_edit', array('id' => $banner->getId()));
            }
        }

        return $this->render('cms/admin/banner/edit.html.twig', array(
            'banner' => $banner,
            'edit_form' => $editForm->createView(),
        ));
    }

    /**
     * Deletes a banner entity.
     *
     * @Route("/{id}", name="banner_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Banner $banner) {
        $em = $this->getDoctrine()->getManager();
        $em->remove($banner);
        $em->flush();


        return $this->redirectToRoute('banner_index');
    }

    /**
     * Lists all Banner entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="banner_datatable", methods={"GET"})
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

        $count = $em->getRepository('CMSBundle:Banner')->filter($search, TRUE);
        $banners = $em->getRepository('CMSBundle:Banner')->filter($search, FALSE, $start, $length);
        $placements = Banner::$placements;

        return $this->render("cms/admin/banner/datatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "banners" => $banners,
                "placements" => $placements
            )
        );
    }

}

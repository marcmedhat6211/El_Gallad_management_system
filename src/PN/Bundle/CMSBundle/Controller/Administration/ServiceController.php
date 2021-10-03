<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Service;
use PN\Bundle\CMSBundle\Form\ServiceType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Service controller.
 *
 * @Route("service")
 */
class ServiceController extends Controller {

    /**
     * Lists all service entities.
     *
     * @Route("/", name="service_index",methods={"GET"})
     */
    public function indexAction() {

        return $this->render('cms/admin/service/index.html.twig');
    }

    /**
     * Creates a new service entity.
     *
     * @Route("/new", name="service_new",methods={"GET","POST"})
     */
    public function newAction(Request $request) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $service = new Service();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $service->setCreator($userName);
            $service->setModifiedBy($userName);

            $em->persist($service);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('post_set_images', ['id' => $service->getPost()->getId()]);
        }
        return $this->render('cms/admin/service/new.html.twig', array(
                    'service' => $service,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing service entity.
     *
     * @Route("/{id}/edit", name="service_edit",methods={"GET", "POST"})
     */
    public function editAction(Request $request, Service $service) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ServiceType::class, $service);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $service->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('service_edit', array('id' => $service->getId()));
        }
        return $this->render('cms/admin/service/edit.html.twig', array(
                    'service' => $service,
                    'form' => $form->createView(),
        ));
    }

    /**
     *
     * /**
     * Deletes a service entity.
     *
     * @Route("/{id}", name="service_delete",methods={"DELETE"})
     */
    public function deleteAction(Request $request, Service $service) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $service->setDeletedBy($userName);
        $service->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($service);
        $em->flush();

        return $this->redirectToRoute('service_index');
    }

    /**
     * Lists all Service entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="service_datatable",methods={"GET"})
     */
    public function dataTableAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");


        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->deleted = 0;

        $count = $em->getRepository('CMSBundle:Service')->filter($search, TRUE);
        $services = $em->getRepository('CMSBundle:Service')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/service/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "services" => $services,
                        )
        );
    }

}

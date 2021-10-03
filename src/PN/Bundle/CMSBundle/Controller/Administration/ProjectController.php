<?php

namespace PN\Bundle\CMSBundle\Controller\Administration;

use PN\Bundle\CMSBundle\Entity\Project;
use PN\Bundle\CMSBundle\Form\ProjectType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Project controller.
 *
 * @Route("project")
 */
class ProjectController extends Controller
{

    /**
     * Lists all project entities.
     *
     * @Route("/", name="project_index",methods={"GET"})
     */
    public function indexAction()
    {

        return $this->render('cms/admin/project/index.html.twig');
    }

    /**
     * Creates a new project entity.
     *
     * @Route("/new", name="project_new",methods={"GET","POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $project = new Project();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $project->setCreator($userName);
            $project->setModifiedBy($userName);

            $em->persist($project);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('post_set_images', array(
                    'id' => $project->getPost()->getId(),
                )
            );
        }
        return $this->render('cms/admin/project/new.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing project entity.
     *
     * @Route("/{id}/edit", name="project_edit",methods={"GET", "POST"})
     */
    public function editAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(ProjectType::class, $project);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $project->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('project_edit', array('id' => $project->getId()));
        }
        return $this->render('cms/admin/project/edit.html.twig', array(
            'project' => $project,
            'form' => $form->createView(),
        ));
    }

    /**
     *
     * /**
     * Deletes a project entity.
     *
     * @Route("/{id}", name="project_delete",methods={"DELETE"})
     */
    public function deleteAction(Request $request, Project $project)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $project->setDeletedBy($userName);
        $project->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($project);
        $em->flush();

        return $this->redirectToRoute('project_index');
    }

    /**
     * Lists all Project entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="project_datatable",methods={"GET"})
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");


        $search = new \stdClass;
        $search->string = $srch['value'];
        $search->deleted = 0;

        $count = $em->getRepository('CMSBundle:Project')->filter($search, TRUE);
        $projects = $em->getRepository('CMSBundle:Project')->filter($search, FALSE, $start, $length);

        return $this->render("cms/admin/project/datatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "projects" => $projects,
            )
        );
    }

}

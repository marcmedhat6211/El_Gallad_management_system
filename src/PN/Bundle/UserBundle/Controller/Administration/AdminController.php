<?php

namespace PN\Bundle\UserBundle\Controller\Administration;

use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\UserBundle\Entity\User;
use PN\Bundle\UserBundle\Form\UserType;
use PN\Bundle\UserBundle\Services\UserService;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin")
 */
class AdminController extends AbstractController
{

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/", name="admin_index", methods={"GET"})
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('user/admin/admin/index.html.twig');
    }

    /**
     * Creates a new user entity.
     *
     * @Route("/new", name="admin_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $user = new User();
        $user->setEnabled(true);
        $form = $this->getFormType($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $username = $this->get(UserService::class)->getUserName();
            $user->setCreator($username);
            $user->setModifiedBy($username);
            $userManager->updateUser($user);

            $this->addFlash('success', 'Successfully saved');
            return $this->redirectToRoute('admin_index');
        }

        return $this->render('user/admin/admin/new.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing admin entity.
     *
     * @Route("/{id}/edit", name="admin_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, User $user)
    {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userManager = $this->get('fos_user.user_manager');

        $form = $this->getFormType($user);
        $form->setData($user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $username = $this->get(UserService::class)->getUserName();
            $user->setModifiedBy($username);
            $userManager->updateUser($user);
            $this->addFlash('success', 'Successfully updated');

            return $this->redirectToRoute('admin_index');
        }

        return $this->render('user/admin/admin/edit.html.twig', array(
            'user' => $user,
            'form' => $form->createView(),
        ));
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="admin_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");

        $search = new \stdClass;
        $search->deleted = 0;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->role = 'ROLE_ADMIN';

        $count = $em->getRepository(User::class)->filter($search, true);
        $admins = $em->getRepository(User::class)->filter($search, false, $start, $length);

        return $this->render("user/admin/admin/datatable.json.twig", array(
                "recordsTotal" => $count,
                "recordsFiltered" => $count,
                "admins" => $admins,
            )
        );
    }

    private function getFormType(User $user)
    {
        $form = $this->createForm(UserType::class, $user);
        $form->add('role', ChoiceType::class, [
            'choices' => [
                'Admin' => User::ROLE_ADMIN,
            ],
            'choices_as_values' => true,
            "attr" => ["class" => "select-search"],
        ]);

        return $form;
    }
}

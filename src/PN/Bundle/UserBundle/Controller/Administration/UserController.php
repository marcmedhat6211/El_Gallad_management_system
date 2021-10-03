<?php

namespace PN\Bundle\UserBundle\Controller\Administration;

use PN\Bundle\UserBundle\Form\UserType;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use PN\Bundle\UserBundle\Entity\User;

/**
 * User controller.
 *
 * @Route("/")
 */
class UserController extends Controller {

    /**
     * Displays a form to create a new User entity.
     *
     * @Route("/", name="user_index", methods={"GET"})
     */
    public function indexAction() {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        return $this->render('user/admin/user/index.html.twig');
    }

    /**
     * Show user.
     *
     * @Route("/{id}/show", name="user_show", methods={"GET"})
     */
    public function showAction(User $user) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');
        $em = $this->getDoctrine()->getManager();

        return $this->render('user/admin/user/show.html.twig', [
                    'user' => $user,
        ]);
    }

    /**
     * Displays a form to edit an existing user entity.
     *
     * @Route("/{id}/edit", name="user_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, User $user) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $userManager = $this->get('fos_user.user_manager');

        $form = $this->createForm(UserType::class, $user);
        $form->remove("role");
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $user->setModifiedBy($userName);
            $userManager->updateUser($user);

            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('user_edit', array('id' => $user->getId()));
        }

        return $this->render('user/admin/user/edit.html.twig', array(
                    'user' => $user,
                    'edit_form' => $form->createView(),
        ));
    }

    /**
     * Deletes a Supplier entity.
     *
     * @Route("/change-state/{id}", name="user_change_state", methods={"POST"})
     */
    public function changeStateAction(Request $request, User $user) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();

        $userManager = $this->get('fos_user.user_manager');

        $user->setEnabled(!$user->isEnabled());

        $userManager->updateUser($user);
        $em->flush();

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Deletes a Merchant entity.
     *
     * @Route("/delete/{id}", name="user_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, User $user) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();


        $rand = substr(md5(microtime()), rand(0, 26), 5);
        $user->setEmail($user->getEmail() . '-del-' . $rand);
        $user->setEnabled(FALSE);
        $userName = $this->get('user')->getUserName();
        $user->setDeletedBy($userName);
        $user->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($user);
        $em->flush();
        if ($user->getId() == $this->getUser()->getId()) {
            $this->get('security.token_storage')->setToken(null);
            $request->getSession()->invalidate();
        }

        return $this->redirect($request->headers->get('referer'));
    }

    /**
     * Displays a form to edit an existing Person entity.
     *
     * @Route("/{id}/details", name="user_details", methods={"GET"})
     * @Template()
     */
    public function userDetailsAction(User $user) {

        return array(
            'entity' => $user,
        );
    }

    /**
     * Deletes a Supplier entity.
     *
     * @Route("/login-as/{id}", requirements={"id" = "\d+"}, name="user_login_as_user", methods={"GET"})
     */
    public function loginAsUserAction(Request $request, User $user) {

        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        if (!$user->isEnabled()) {
            $request->getSession()->getFlashBag()->add('error', "this user is blocked, so you can't login with this account");
            return $this->redirect($request->headers->get('referer'));
        }

        $securityContext = $this->get('security.token_storage');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());

        $securityContext->setToken($token);

        $session = $this->get('session');
        $session->set('_security_' . 'main', serialize($token));

        if ($this->get('security.authorization_checker')->isGranted('ROLE_SUPER_ADMIN')) {
            return $this->redirectToRoute('fos_user_profile_show');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_ADMIN') or $this->get('security.authorization_checker')->isGranted('ROLE_IMAGE_GALLERY')) {
            return $this->redirectToRoute('dashboard');
        } elseif ($this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            return $this->redirectToRoute('fe_home');
        }
        return $this->redirectToRoute('fe_home');
    }

    /**
     * Lists all Category entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="user_datatable", methods={"GET"})
     */
    public function dataTableAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $srch = $request->query->get("search");
        $start = $request->query->get("start");
        $length = $request->query->get("length");
        $ordr = $request->query->get("order");

        $search = new \stdClass;
        $search->deleted = 0;
        $search->string = $srch['value'];
        $search->ordr = $ordr[0];
        $search->role = 'ROLE_USER';

        $count = $em->getRepository('UserBundle:User')->filter($search, TRUE);
        $users = $em->getRepository('UserBundle:User')->filter($search, FALSE, $start, $length);

        return $this->render("user/admin/user/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "users" => $users,
                        )
        );
    }

}

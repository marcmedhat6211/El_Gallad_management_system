<?php

namespace PN\Bundle\CurrencyBundle\Controller\Administration;

use PN\Bundle\CurrencyBundle\Entity\Currency;
use PN\Bundle\CurrencyBundle\Form\CurrencyType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("")
 */
class CurrencyController extends Controller {

    /**
     * Lists all currency entities.
     *
     * @Route("/", name="currency_index", methods={"GET"})
     */
    public function indexAction() {

        return $this->render('currency/admin/currency/index.html.twig');
    }

    /**
     * Creates a new currency entity.
     *
     * @Route("/new", name="currency_new", methods={"GET", "POST"})
     */
    public function newAction(Request $request) {
        throw $this->createNotFoundException();
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $currency = new Currency();
        $form = $this->createForm(CurrencyType::class, $currency);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $currency->setCreator($userName);
            $currency->setModifiedBy($userName);

            $em->persist($currency);
            $em->flush();

            $this->addFlash('success', 'Successfully saved');

            return $this->redirectToRoute('currency_index');
        }
        return $this->render('currency/admin/currency/new.html.twig', array(
                    'currency' => $currency,
                    'form' => $form->createView(),
        ));
    }

    /**
     * Displays a form to edit an existing currency entity.
     *
     * @Route("/{id}/edit", name="currency_edit", methods={"GET", "POST"})
     */
    public function editAction(Request $request, Currency $currency) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(CurrencyType::class, $currency);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $userName = $this->get('user')->getUserName();
            $currency->setModifiedBy($userName);
            $em->flush();

            $this->addFlash('success', 'Successfully updated');
            return $this->redirectToRoute('currency_edit', array('id' => $currency->getId()));
        }
        return $this->render('currency/admin/currency/edit.html.twig', array(
                    'currency' => $currency,
                    'form' => $form->createView(),
        ));
    }

    /**

      /**
     * Deletes a currency entity.
     *
     * @Route("/{id}", name="currency_delete", methods={"DELETE"})
     */
    public function deleteAction(Request $request, Currency $currency) {
        $this->denyAccessUnlessGranted('ROLE_ADMIN');

        $em = $this->getDoctrine()->getManager();
        $userName = $this->get('user')->getUserName();
        $currency->setDeletedBy($userName);
        $currency->setDeleted(new \DateTime(date('Y-m-d H:i:s')));
        $em->persist($currency);
        $em->flush();

        return $this->redirectToRoute('currency_index');
    }

    /**
     * Lists all Currency entities.
     *
     * @Route("/data/table", defaults={"_format": "json"}, name="currency_datatable", methods={"GET"})
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

        $count = $em->getRepository(Currency::class)->filter($search, TRUE);
        $currencies = $em->getRepository(Currency::class)->filter($search, FALSE, $start, $length);

        return $this->render("currency/admin/currency/datatable.json.twig", array(
                    "recordsTotal" => $count,
                    "recordsFiltered" => $count,
                    "currencies" => $currencies,
                        )
        );
    }

}

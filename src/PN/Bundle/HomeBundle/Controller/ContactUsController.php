<?php

namespace PN\Bundle\HomeBundle\Controller;

use PN\Bundle\CMSBundle\Services\BannerService;
use PN\Bundle\HomeBundle\Form\ContactUsType;
use PN\ServiceBundle\Lib\Mailer;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use PN\ServiceBundle\Utils\Validate;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * contactus controller.
 *
 * @Route("contact")
 */
class ContactUsController extends Controller {

    /**
     * Contactus form.
     *
     * @Route("", name="fe_contact", methods={"GET", "POST"})
     */
    public function contactAction(Request $request, TranslatorInterface $translator) {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(7);

        // Create the form according to the FormType created previously.
        // And give the proper parameters
        $form = $this->createForm(ContactUsType::class, null, array(
            'method' => 'POST',
        ));

        // Refill the fields in case the form is not valid.
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Send mail
            $data = $form->getData();
            $this->sendEmail($data);


            $this->addFlash('success', $translator->trans('Your message is successfully sent'));

            return $this->redirectToRoute('fe_contact');
        }
        $banner = $this->get(BannerService::class)->getOneBanner(7);
        return $this->render('home/contact/index.html.twig', [
            'seoPage' => $seoPage,
            'banner' => $banner,
            'contact_form' => $form->createView(),
        ]);
    }

    private function sendEmail($data) {
        $message = \PN\ServiceBundle\Lib\Mailer::newInstance()
            ->setSubject(\AppKernel::websiteTitle . ' contact us from ' . $data['name'])
            ->setFrom(array(\AppKernel::fromEmail => \AppKernel::websiteTitle))
            ->setTo(\AppKernel::adminEmail)
            ->setBody(
                $this->renderView(
                    'home/contact/adminEmail.html.twig', [
                        'data' => $data,
                    ]
                ), 'text/html');
        $message->send();
    }

}

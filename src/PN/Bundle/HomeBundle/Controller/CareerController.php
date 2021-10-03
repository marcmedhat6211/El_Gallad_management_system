<?php

namespace PN\Bundle\HomeBundle\Controller;

use PN\Bundle\CMSBundle\Services\BannerService;
use PN\Bundle\HomeBundle\Form\CareerType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Career controller.
 *
 * @Route("career")
 */
class CareerController extends Controller {

    /**
     * career form.
     *
     * @Route("", name="fe_career", methods={"GET", "POST"})
     */
    public function careerAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $seoPage = $em->getRepository('PNSeoBundle:SeoPage')->find(7);

        // Create the form according to the FormType created previously.
        // And give the proper parameters
        $form = $this->createForm(CareerType::class, null, array(
            'method' => 'POST',
        ));

        // Refill the fields in case the form is not valid.
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Send mail
            $data = $form->getData();
            $this->sendEmail($data);


            $this->addFlash('success', 'Your application is successfully sent');

            return $this->redirectToRoute('fe_career');
        }

        $banner = $this->get(BannerService::class)->getOneBanner(8);

        return $this->render('home/career/index.html.twig', [
            'seoPage' => $seoPage,
            'banner' => $banner,
            'career_form' => $form->createView(),
        ]);
    }

    private function sendEmail($data) {
        $message = \Swift_Message::newInstance()
            ->setSubject(\AppKernel::websiteTitle . ' - new resume from ' . $data['firstName'])
            ->setFrom(array(\AppKernel::fromEmail => \AppKernel::websiteTitle))
            ->setTo("hr@c-reality-eg.com")
            ->setBody(
                $this->renderView(
                    'home/career/adminEmail.html.twig', [
                        'data' => $data,
                    ]
                ), 'text/html');
        if ($data['resume'] != null) {
            $message->attach(\Swift_Attachment::newInstance($data['resume']->getPathname(), $data['resume']->getClientOriginalName(), $data['resume']->getMimeType()));
        }
        $this->get('mailer')->send($message);

    }

}

<?php

namespace PN\Bundle\ProductBundle\Controller\FrontEnd;

use Dompdf\Dompdf;
use Dompdf\Options;
use PN\Bundle\BaseBundle\Controller\AbstractController;
use PN\Bundle\ProductBundle\Services\CartService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Product controller.
 *
 * @Route("/")
 */
class CartController extends AbstractController
{
    /**
     * @Route("/add-to-cart/{type}", name="fe_cart_add_ajax", methods={"POST"})
     */
    public function addToCartAction(
        Request $request,
        TranslatorInterface $translator,
        CartService $cartService,
        $type
    ): Response {
        if (!in_array($type, ["add", "update"])) {
            throw new \Exception('Invalid $type parameter');
        }

        $productUuid = $request->request->get("uuid");
        $qty = $request->request->get("qty");

        $product = $cartService->getProductByUuid($productUuid);

        if (!$product) {
            return $this->json(["error" => true, "message" => $translator->trans("Invalid Product")]);
        }

        $response = new JsonResponse();

        $cookieCartItems = $cartService->addProductInCart($request, $response, $productUuid, $qty, $type);

        $cart = $cartService->getCart($request, $cookieCartItems);
        if (!$cart) {
            return $this->json(["error" => true, "message" => $translator->trans("An error occurred")]);
        }

        $cartItemsObj = [];
        foreach ($cart->getCartItems() as $cartItem) {
            $cartItemsObj[] = $cartService->getCartItemInArray($cartItem);
        }

        $json = [
            "error" => false,
            "message" => $translator->trans("Added to Cart Successfully"),
            "cartItems" => $cartItemsObj,
            "noOfItem" => $cart->getNoOfItems(),
            "grandTotal" => $cart->getGrandTotal(),
        ];
        $response->setData($json);

        return $response;
    }

    /**
     * @Route("/remove-from-cart", name="fe_cart_remove_ajax", methods={"POST"})
     */
    public function removeFromCartAction(
        Request $request,
        TranslatorInterface $translator,
        CartService $cartService
    ): Response {
        $productUuid = $request->request->get("productUuid");
        $response = new JsonResponse();
        dump($cartService->getCartObjectFromCookie($request));

        $cookieCartItems = $cartService->removeItemFromCartCookie($request, $response, $productUuid);
        dump($cookieCartItems);
        $cart = $cartService->getCart($request, $cookieCartItems);
dump($cart);
        $json = [
            "error" => false,
            "message" => $translator->trans("Item removed"),
            "noOfItem" => $cart->getNoOfItems(),
            "grandTotal" => $cart->getGrandTotal(),
        ];
        $response->setData($json);

        return $response;
    }


    /**
     * @Route("/download", name="fe_cart_download_pdf", methods={"GET"})
     */
    public function downloadCartPDFAction(Request $request, CartService $cartService)
    {
        $cart = $cartService->getCart($request);

        // Retrieve the HTML generated in our twig file
        $html = $this->renderView("fe/quotation/quotation.html.twig", [
            "cart" => $cart,
        ]);
        $footer = $this->renderView("fe/quotation/_footer.html.twig");
        $bg = $this->renderView("fe/quotation/_bg.html.twig");
//        $instuctionsList =

        $cartService->pdf($html, $footer, $bg);
    }
}
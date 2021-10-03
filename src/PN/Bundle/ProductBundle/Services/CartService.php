<?php

namespace PN\Bundle\ProductBundle\Services;

use Doctrine\ORM\EntityManagerInterface;
use PN\Bundle\ProductBundle\DTO\Cart;
use PN\Bundle\ProductBundle\DTO\CartItem;
use PN\Bundle\ProductBundle\Entity\Product;
use PN\Bundle\ProductBundle\Entity\ProductPrice;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class CartService
{

    protected $em;
    private $container;
    private $request;
    private $assets;
    private $cookieName = "cart";
    private $tcpdf;
    private $router;

    public function __construct(
        ContainerInterface $container,
        RequestStack $requestStack,
        EntityManagerInterface $em,
        TCPDFController $tcpdf,
        UrlGeneratorInterface $router
    ) {
        $this->container = $container;
        $this->em = $em;
        $this->assets = $container->get('assets.packages');
        $this->request = $requestStack->getCurrentRequest();
        $this->tcpdf = $tcpdf;
        $this->router = $router;
    }

    /**
     * @throws \Exception
     */
    public function addProductInCart(
        Request $request,
        Response $response,
        $uuid,
        $qty,
        string $updateType = "add"
    ): array {
        if (!in_array($updateType, ["add", "update"])) {
            throw new \Exception('Invalid $updateType variable');
        }
        $cartItems = $this->getCartObjectFromCookie($request);

        $newQty = $qty;
        if (array_key_exists($uuid, $cartItems) and $updateType == 'add') {
            $newQty += $cartItems[$uuid];
        }

        $cartItems[$uuid] = $newQty;

        $cookie = new Cookie($this->cookieName, json_encode($cartItems));
        $response->headers->setCookie($cookie);

        return $cartItems;
    }

    public function getCart(Request $request, array $cookieCartItems = null): Cart
    {
        if (!is_array($cookieCartItems)) {
            $cookieCartItems = $this->getCartObjectFromCookie($request);
        }
        $cart = new Cart();
        foreach ($cookieCartItems as $productUuid => $qty) {
            $product = $this->getProductByUuid($productUuid);
            $unitPrice = $this->getProductPriceByQuantity($product, $qty);

            $cartItem = new CartItem();
            $cartItem->setProduct($product);
            $cartItem->setQty($qty);
            $cartItem->setUnitPrice($unitPrice);
            $cart->addCartItem($cartItem);
        }

        return $cart;
    }

    public function getCartObjectFromCookie(Request $request): array
    {
        $cartItems = [];

        if ($request->cookies->has($this->cookieName)) {
            $cartItems = (array)json_decode($request->cookies->get($this->cookieName));
        }

        return $cartItems;
    }

    public function getProductByUuid($uuid): ?Product
    {
        $search = new \stdClass();
        $search->deleted = 0;
        $search->publish = 1;
        $search->uuid = $uuid;
        $product = $this->em->getRepository(Product::class)->filter($search, false, 0, 1);

        return $product ? $product[0] : null;
    }

    public function getProductPriceByQuantity(Product $product, $qty): float
    {
        $search = new \stdClass();
        $search->deleted = 0;
        $search->qty = $qty;
        $search->product = $product->getId();
        $productPrice = $this->em->getRepository(ProductPrice::class)->filter($search, false, 0, 1);

        return $productPrice ? $productPrice[0]->getSellPrice() : $product->minPrice;
    }


    public function getCartItemInArray(CartItem $cartItem): array
    {
        $object = $cartItem->getObj();

        $product = $cartItem->getProduct();

        $mainImage = "images/placeholders/placeholder-sm.jpg";
        if ($product->getPost()->getMainImage()) {
            $mainImage = $product->getPost()->getMainImage()->getAssetPathThumb();
        }

        $imageUrl = $this->request->getSchemeAndHttpHost().$this->assets->getUrl($mainImage);

        $productUrl = $this->router->generate("fe_product_show", ['slug' => $product->getSeo()->getSlug()],
            UrlGeneratorInterface::ABSOLUTE_URL);
        $updateUrl = $this->router->generate("fe_cart_add_ajax", ['type' => 'update']);
        $removeItemUrl = $this->router->generate("fe_cart_remove_ajax");
        dump($removeItemUrl);

        $category = $product->getCategory();
        $categoryObj = [
            "title" => $category->getTitle(),
        ];
        $productObj = [
            "uuid" => $product->getUuid(),
            "category" => $categoryObj,
            "title" => $product->getTitle(),
            "imageUrl" => $imageUrl,
            "productUrl" => $productUrl,
        ];

        $object['product'] = $productObj;
        $object['updateUrl'] = $updateUrl;
        $object['removeItemUrl'] = $removeItemUrl;

        return $object;
    }

    public function getCartItemByProductUuid($cartItems, $uuid): CartItem
    {
        $currentCartItem = new CartItem();
        foreach ($cartItems as $cartItem) {
            if ($cartItem->getProduct()->getUuid() == $uuid) {
                $currentCartItem = $cartItem;
            }
        }

        return $currentCartItem;
    }

    public function removeItemFromCartCookie(Request $request, Response $response, $uuid): array
    {
        $cartObject = $this->getCartObjectFromCookie($request);
        unset($cartObject[$uuid]);

        $newCookie = new Cookie($this->getCookieName(), json_encode($cartObject));
        $response->headers->setCookie($newCookie);

        return $cartObject;
    }

    public function pdf($html, $footer, $bg)
    {
        $myPdf = new \TCPDF();
        dump($myPdf);
        $pdf = $this->tcpdf->create('vertical', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator('PerfectNeeds');
        $pdf->SetAuthor('seats.com');
        $pdf->SetTitle("Quotation");
        $pdf->SetSubject("Quotation");

        $pdf->SetPrintHeader(false);

        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        //        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        //        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        $pdf->SetAutoPageBreak(true, 0);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
//        $pdf->SetAutoPageBreak(false);
//         $pdf->setRTL(true);
        // $pdf->setFontSubsetting(true);
        // $pdf->SetFont('dejavusans', '', 14, '', true);
        $pdf->SetAlpha(0.5);
        $pdf->AddPage();
        $pdf->writeHTMLCell(0, 0, '', '', $html, 0, 1, 0, true, '', true);
        $pdf->writeHTMLCell(0, 0, '', '-30', $footer, 0, 0, 0, true, '', true);
        $pdf->writeHTMLCell(0, 0, '', '20', $bg, 0, 0, 0, true, '', true);



//         return $pdf->Output("quotation.pdf", 'I');// Display in the browser
        return $pdf->Output("quotation.pdf", 'D'); // Download
    }

    public function getCookieName()
    {
        return $this->cookieName;
    }
}

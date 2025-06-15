<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
use App\Service\PurchaseService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/product')]
class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index', methods: ['GET'])]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/{id}', name: 'app_product_show', methods: ['GET'])]
    public function show(Product $product): Response
    {
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/{id}/acheter', name: 'app_product_acheter', methods: ['POST'])]
    #[IsGranted('ROLE_USER')]
    public function acheter(Product $product, PurchaseService $purchaseService, Request $request): Response
    {
        if ($this->isCsrfTokenValid('acheter'.$product->getId(), $request->request->get('_token'))) {
            try {
                $purchaseService->purchaseProduct($this->getUser(), $product);
                $this->addFlash('success', 'Produit acheté avec succès !');
            } catch (\Exception $e) {
                $this->addFlash('error', $e->getMessage());
            }
        }

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }
}
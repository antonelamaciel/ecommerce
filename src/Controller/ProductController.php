<?php

namespace App\Controller;

use App\Form\SearchType;
use App\Repository\ProductRepository;
use App\Model\Search;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ProductController extends AbstractController
{
    #[Route('/products', name: 'product')]
    public function index(ProductRepository $repository, Request $request): Response
    {
       
        // Si recherche exécutée, $products contiendra les résultats filtrés
        $search = new Search();
        $form = $this->createForm(SearchType::class, $search);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $products = $repository->findWithSearch($search);
        } else {
            $products = $repository->findAll();
        }

        
        return $this->renderForm('product/index.html.twig', [
            'products' => $products,
            'form' => $form,
        ]);
    }

    #[Route('/products/{slug}', name: 'product_show')]
    public function show(ProductRepository $repository, \App\Repository\CarrierRepository $carrierRepository, string $slug): Response
    {
        $product = $repository->findOneBySlug($slug);
        $carriers = $carrierRepository->findAll();

        if (!$product) {
            return $this->redirectToRoute('product');
        }

        $relatedProducts = $repository->findBy(
            ['category' => $product->getCategory()],
            ['id' => 'DESC'],
            8
        );

        $relatedProducts = array_filter($relatedProducts, function($p) use ($product) {
            return $p->getId() !== $product->getId();
        });
        $relatedProducts = array_slice($relatedProducts, 0, 7);

        // Fallback to random products if there are less than 7
        if (count($relatedProducts) < 7) {
            $allProducts = $repository->findAll();
            $allProducts = array_filter($allProducts, function($p) use ($product, $relatedProducts) {
                if ($p->getId() === $product->getId()) return false;
                foreach ($relatedProducts as $rp) {
                    if ($rp->getId() === $p->getId()) return false;
                }
                return true;
            });
            
            shuffle($allProducts);
            $needed = 7 - count($relatedProducts);
            $randomProducts = array_slice($allProducts, 0, $needed);
            $relatedProducts = array_merge($relatedProducts, $randomProducts);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'carriers' => $carriers,
            'relatedProducts' => $relatedProducts
        ]);
    }
}



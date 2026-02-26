<?php

namespace App\Controller;

use App\Repository\HeadersRepository;
use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function index(ProductRepository $productRepository, HeadersRepository $headersRepository): Response
    {
        $products = $productRepository->findByIsInHome(1);
        $allProducts = $productRepository->findAll();
        $headers = $headersRepository->findAll();
        return $this->render('home/index.html.twig', [
            'carousel' => true,  //Le caroussel ne s'affiche que sur la page d'accueil (voir base.twig)
            'top_products' => $products,
            'all_products' => $allProducts,
            'headers' => $headers
        ]);
    }

    #[Route('a-propos', name: 'about')]
    public function about(\App\Repository\AboutRepository $repository): Response
    {
        $contents = $repository->findBy(['isPublished' => true], ['priority' => 'ASC']);

        return $this->render('home/about.html.twig', [
            'contents' => $contents
        ]);
    }

    #[Route('/envios-y-devoluciones', name: 'shipping_returns')]
    public function shippingReturns(\App\Repository\ShippingReturnRepository $repository): Response
    {
        $contents = $repository->findBy(['isPublished' => true]);

        return $this->render('home/shipping_returns.html.twig', [
            'contents' => $contents
        ]);
    }

    #[Route('/preguntas-frecuentes', name: 'faq')]
    public function faq(\App\Repository\FAQRepository $faqRepository): Response
    {
        $faqs = $faqRepository->findBy(['isPublished' => true]);
        
        return $this->render('home/faq.html.twig', [
            'faqs' => $faqs
        ]);
    }
}

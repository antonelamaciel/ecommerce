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
            $products = $repository->findBy([], ['id' => 'DESC'], 24);
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

        // Fallback to recent products if category is thin
        if (count($relatedProducts) < 7) {
            $excludeIds = [$product->getId()];
            foreach($relatedProducts as $rp) $excludeIds[] = $rp->getId();
            
            $fallbacks = $repository->createQueryBuilder('p')
                ->andWhere('p.id NOT IN (:ids)')
                ->setParameter('ids', $excludeIds)
                ->setMaxResults(7 - count($relatedProducts))
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getResult();
            
            $relatedProducts = array_merge($relatedProducts, $fallbacks);
        }

        return $this->render('product/show.html.twig', [
            'product' => $product,
            'carriers' => $carriers,
            'relatedProducts' => array_slice($relatedProducts, 0, 7)
        ]);
    }

    #[Route('/search-ajax', name: 'search_ajax', methods: ['GET'])]
    public function searchAjax(ProductRepository $repository, Request $request): Response
    {
        $query = $request->query->get('q', '');
        
        if (mb_strlen($query) < 2) {
            return $this->json([]);
        }
        
        $search = new Search();
        $search->setString($query);
        $products = $repository->findWithSearch($search);
        $products = array_slice($products, 0, 8); // Limit to 8 items for AJAX
        
        $data = [];
        $domain = $request->getSchemeAndHttpHost();
        
        foreach ($products as $product) {
            $discount = $product->getMaxDiscount();
            $finalPrice = $discount > 0 ? $product->getPrice() * (1 - $discount / 100) : $product->getPrice();

            $data[] = [
                'name' => $product->getName(),
                'slug' => $product->getSlug(),
                'price' => number_format($finalPrice / 100, 2, ',', '.'),
                'oldPrice' => $discount > 0 ? number_format($product->getPrice() / 100, 2, ',', '.') : null,
                'image' => $product->getImage() ? $domain . '/uploads/' . $product->getImage() : null,
            ];
        }
        
        return $this->json($data);
    }
}



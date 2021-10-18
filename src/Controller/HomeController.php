<?php

namespace App\Controller;

use App\Entity\Product;
use App\Entity\SearchProduct;
use App\Form\SearchProductType;
use App\Repository\ProductRepository;
use App\Repository\HomeSliderRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class HomeController extends AbstractController
{
    /**
     * @Route("/home", name="home")
     */
    public function index(ProductRepository $repoProduct,
                            HomeSliderRepository $repoHomeSlider): Response //ProductRepository $repoProduct pour recupere les produit de la BDD en affichage
    {
        $products = $repoProduct->findAll(); // pour tout afficher
        
        $homeSlider = $repoHomeSlider->findBy(['isDisplay'=>true]);
        $productBestSeller = $repoProduct->findByIsBestSeller(1); // pour recup les validé
        $productSpecialOffer = $repoProduct->findByIsSpecialOffer(1);
        $productNewArrival = $repoProduct->findByIsNewArrival(1);
        $productFeatured = $repoProduct->findByIsFeatured(1);

      
        return $this->render('home/index.html.twig', [
            'controller_name' => 'HomeController',
            'products' => $products,
            'productBestSeller' => $productBestSeller,
            'productSpecialOffer'=> $productSpecialOffer,
            'productNewArrival' => $productNewArrival,
            'productFeatured' => $productFeatured,
            'homeSlider' => $homeSlider,
            
        ]);
    }
    /**
     * @Route("/product/{slug}", name="product_details") //slug pour identifier les produits
     */
    public function show(?Product $product): Response{
        if (!$product) {
            return $this->redirectToRoute("home");
        }
        return $this->render("home/single_product.html.twig",[
            'product'=> $product
        ]);
    }
    /**
     * @Route("/shop", name="shop")
     */
    public function shop(ProductRepository $repoProduct, Request $request): Response
    {
        $products = $repoProduct->findAll();

        $search = new SearchProduct();
        $form = $this->createForm(SearchProductType::class, $search);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
           $products = $repoProduct->findWithSearch($search);
            

        }
        return $this->render('home/shop.html.twig', [
        'products' => $products,
        'search' => $form->createView()
        ]);
    }
}
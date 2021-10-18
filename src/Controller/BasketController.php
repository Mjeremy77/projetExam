<?php
namespace App\Controller;

use App\Entity\Product;
use App\Services\BasketServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BasketController extends AbstractController
{   
    private $basketServices;

    public function __construct(BasketServices $basketServices)
    {
       $this->basketServices = $basketServices;
    }
    /**
     * @Route("/basket", name="basket")
     */
    public function index(): Response
    {
        
        $basket = $this->basketServices->getFullBasket();
        if (!isset($basket['products'])){
          return $this->redirectToRoute("home");
        }
        return $this->render('basket/index.html.twig', [
            'basket'=> $basket
            
        ]);
    }
    /**
     * @Route("/basket/add/{id}", name="basket_add")
     */
    public function addToBasket($id): Response{
       // dump($this->basketServices);
        $this->basketServices->addToBasket($id);
        return $this->redirectToRoute("basket");
        
    }
    /**
     * @Route("/basket/delete/{id}", name="basket_delete")
     */
    public function deleteFromBasket($id): Response{
        $this->basketServices->deleteFromBasket($id);
        return $this->redirectToRoute("basket");
    }
    /**
     * @Route("/basket/delete-all/{id}", name="basket_delete_all")
     */
    public function deleteAllToBasket($id): Response
    {
       $this->basketServices->deleteAllToBasket($id);
        return $this->redirectToRoute("basket");
    }
}
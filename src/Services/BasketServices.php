<?php
namespace App\Services;


use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class BasketServices
{

    private $session;
    private $repoProduct;
    private $tva = 0.2;
    

    public function __construct(SessionInterface $session, ProductRepository $repoProduct)
    {
        $this->session = $session;
        $this->repoProduct = $repoProduct;
    }
    public function addToBasket($id)
    {
        $basket = $this->getBasket();
        if (isset($basket[$id])) 
        { 
            $basket[$id]++;//produit deja dans le panier
        }    
        else
        {
            $basket[$id] = 1; //produit pas dans le panier
        }
        $this->updateBasket($basket);
    }
    public function deleteFromBasket($id)
    { 
        $basket = $this->getBasket();

        if (isset($basket[$id])) 
        {
            //produit deja dans le panier
            if($basket[$id] > 1)
            {
                //produit existe plus d'une fois
                $basket[$id]--;
            }else
            {
                unset($basket[$id]);
            }
            $this->updateBasket($basket);
        }

    }
    public function deleteAllToBasket($id)
    {
        $basket = $this->getBasket();

        if (isset($basket[$id])) 
        {
            //produit deja dans le panier
            unset($basket[$id]);
            $this->updateBasket($basket);
        }
    }
    public function deleteBasket()
    {
        $this->updateBasket([]);
    }
    public function updateBasket($basket)
    {
        $this->session->set('basket',$basket); //mise à jour du panier en prenant tout
        $this->session->set('basketData', $this->getFullBasket()); //incremanté les donnée du panier mis a jour dans la "session"
    }
    public function getBasket()
    {
        return $this->session->get('basket',[]);//recupere le contenu du panier
    }
    //methode pour recuperer les elements dans le panier
    public function getFullBasket()
    {
        $basket = $this->getBasket();
        $fullBasket = [];
        $quantity_basket = 0;
        $subTotal = 0;
        foreach($basket as $id =>$quantity)
        {
            $product = $this->repoProduct->find($id);
            if($product) 
            {
                //produit recupere avec succes
                $fullBasket['products'][]=
                [
                    "quantity" =>$quantity,
                    "product" =>$product
                ];
                $quantity_basket += $quantity;
                $subTotal += $quantity * $product->getPrice()/100;
            }else
            {
                //id incorrect
                $this->deleteFromBasket($id);
            }
        }
        $fullBasket['data'] = [
            "quantity_basket" => $quantity_basket,
            "subTotalHT" =>$subTotal,
            "Taxe" =>round($subTotal*$this->tva,2),
            "subTotalTTC" => round(($subTotal + ($subTotal*$this->tva)),2)
        ];
        return $fullBasket;
    }

}
?>
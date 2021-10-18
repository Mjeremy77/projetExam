<?php

namespace App\Controller;

use App\Form\CheckoutType;
use App\Services\BasketServices;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class CheckoutController extends AbstractController
{   
    private $basketServices;
    private $session;

    public function __construct(BasketServices $basketServices, SessionInterface $session)
    {
        $this->basketServices = $basketServices;
        $this->session = $session;
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function index(Request $request): Response
    {   
        $user = $this->getUser(); //recuperer utilisateur
        $basket = $this->basketServices->getFullBasket();

        if(!isset($basket['products'])){ //verifier le contenu du panier
            return $this->redirectToRoute("home");
        }
        if (!$user ->getAddresses()->getValues()) { //verification si adresse
          $this->addFlash('checkout_message', 'Merci de créer un compte pour continuer');
            return $this->redirectToRoute("address_new");
        }
        if($this->session->get('chekout_data')){
            return $this->redirectToRoute('checkout_confirm');
        }
        $form = $this->createForm(CheckoutType::class,null,['user' =>$user]);
        $form->handleRequest($request);


        return $this->render('checkout/index.html.twig' , [
            'basket' => $basket,
            'checkout' =>$form->createView()
        ]);
    }
    /**
     * @Route("/checkout/confirm", name="checkout_confirm")
     */
    public function confirm(Request $request): Response
    {
        $user = $this->getUser();
        $basket = $this->basketServices->getFullBasket();

        if (!isset($basket['products'])) { //verifier le contenu du panier
            return $this->redirectToRoute("home");
        }
        if (!$user->getAddresses()->getValues()) { //verification si adresse
            $this->addFlash('checkout_message', 'Merci de créer un compte pour continuer');
            return $this->redirectToRoute("address_new");
        }

        $form = $this->createForm(CheckoutType::class, null, ['user' => $user]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() || $this->session->get('checkout_data')){
            //recuprer les information dans le checkout
            if($this->session->get('checkout_data')){
                $data=$this->session->get('checkout_data');
            }else{
                $data = $form->getData();
                $this->session->set('checkout_data',$data);
            }
            
            $address = $data['address'];
            $carrier = $data['carrier'];
            $informations = $data['informations'];
            
            //transmission des données
            return $this->render('checkout/confirm.html.twig' , [
            'basket' => $basket,
            'address' => $address,
            'carrier' => $carrier,
            'informations' => $informations,
            'checkout' => $form->createView()
        ]);
        }
        return $this->redirectToRoute('checkout');
    }

    /**
     * @Route("/checkout/edit", name="checkout_edit")
     */
    public function checkoutEdit():Response{
        $this->session->set('checkout_data',[]);
        return $this->redirectToRoute("checkout");
    }
}

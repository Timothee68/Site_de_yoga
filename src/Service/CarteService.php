<?php

namespace App\Service;


use App\Repository\ProductSheetRepository;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

Class CarteService {

    protected $session;

    public function __construct(SessionInterface $session)
    {
        $this->session = $session;
    }


    public function addProduit(int $id)
    {
        $panier = $this->session->get("panier", [] );
    
        $panier[$id]+= 1 ;
       
        $this->session->set('panier' , $panier);
    }

    
    public function removeProduit(int $id)
    {
        $panier = $this->session->get("panier", [] );
        if( $panier[$id] == 1){
            if(!empty($panier[$id])){
                unset($panier[$id]);
            }
            $this->session->set('panier' , $panier);
        }else{
            $panier[$id]-= 1 ;
        }
       
        $this->session->set('panier' , $panier);
    }

    public function add(int $id){

        // dans la session regarde si il y a une donne nommée "panier et si il n'y a rien en deuxième paramêtre on dit tout simplement que l'on veux un tableau 
        $panier = $this->session->get("panier" , [] );
        // le panier auras une cléf nommée $id qu'on met a 1;
        // on fait une condition pour ajouter plusieurs élément au panier
        if(!empty($panier[$id])){
            $panier[$id] ++ ;
        }else{
            $panier[$id] = 1 ;
        }
        // on remplace par les infos qu'on récupère en session a chauqe rajout 
        $this->session->set('panier' , $panier);
    }


    public function delete(int $id)
    {
        $panier = $this->session->get("panier", [] );
        if(!empty($panier[$id])){
            unset($panier[$id]);
        }
        $this->session->set('panier' , $panier);
    }

    public function deleteAll() 
    {
        $panier = $this->session->get("panier", [] );
        foreach( $panier as $id => $quantity)
        {
            if(!empty($panier[$id])){
                unset($panier[$id]);
            }
            $this->session->set('panier' , $panier);
        }
    }

}
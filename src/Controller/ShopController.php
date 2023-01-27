<?php

namespace App\Controller;

use Date;
use DateTime;
use Dompdf\Dompdf;
use Stripe\Stripe;
use Dompdf\Options;
use App\Entity\User;
use App\Entity\Command;
use App\Entity\Contain;
use App\Form\CommandType;
use App\Entity\ProductSheet;
use App\Service\CartService;
use Stripe\Checkout\Session;
use App\Service\CarteService;
use App\Entity\AdressDelivery;
use App\Form\AdressDeliveryType;
use App\Repository\CommandRepository;
use Doctrine\Persistence\ManagerRegistry;
use phpDocumentor\Reflection\Types\Null_;
use App\Repository\ProductSheetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGenerator;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class ShopController extends AbstractController
{
    protected $cartService;

    public function __construct(CarteService $cartService)

    {
        $this->cartService = $cartService;

    }

    /**
     * Fonction pour afficher les porduit de vente
     * @Route("/shop", name="app_shop")
     */
    public function index(ProductSheetRepository $productSheetRepository): Response
    {
        $produits = $productSheetRepository->findAll();

        return $this->render('shop/index.html.twig', [
            'produits' => $produits,
        ]);
    }

    /**
     * Fonction pour ajouter des produits en session
     * @Route("/shop/panierAdd/{id}", name="add_shop")
     */
    public function add( $id ) : Response
    {
            $this->cartService->add($id);
            return $this->redirectToRoute('app_shop');
    }

    /**
     * Fonction afficher le panier des produits en session
     * @Route("/shop/panier", name="app_panier")
     */
    public function panier(SessionInterface $session , ProductSheetRepository $productSheetRepository , ? Command $command) : Response
    {
        $panier = $session->get("panier", [] );
        if ($command) {
            $idUserAdress = $command->getUserOrderCommand();
        }else{
            $idUserAdress = null;
        }
        $panierWithData = [] ;
        foreach( $panier as $id => $quantity)
        {
            $panierWithData[] = [
                'product' => $productSheetRepository->find($id),
                'quantity' => $quantity,
            ];   
        }
        
        $total = 0;
        $qt = 0;
        foreach($panierWithData as $item ){
            $totalItem =  $item['product']->getPrice() * $item['quantity'];
            $item['quantity'];
            $qt += $item['quantity'];
            $total += $totalItem;
        }

        return $this->render('shop/panier.html.twig', [
            'items' => $panierWithData,
            'total' => $total,
            'qt' => $qt,
            'idClient' => $idUserAdress ,
        ]);
    }

    /**
     * Fonction pour enlever 1 de la quantité d'un produit déjà dans le panier en session
     * @Route("/shop/panierRemove/{id}", name="remove_panier")
     */
    public function removeProduit($id, CarteService $cartService, ProductSheetRepository $productSheetRepository) : Response
    {
        $this->cartService->removeProduit($id);

        return $this->redirectToRoute('app_panier');
    }

    /**
     * Fonction pour ajouter 1 de la quantité d'un produit déjà dans le panier en session
     * @Route("/shop/panierAddProduit/{id}", name="add_panier")
     */
    public function addProduit($id, CarteService $cartService, ProductSheetRepository $productSheetRepository) : Response
    {
        $this->cartService->addProduit($id);

        return $this->redirectToRoute('app_panier');
    }

    /**
     * fonction pour ajouter et/ou modifier un video
     * @Route("/shop/adressDelivery/add/{id_user}", name="add_adressDelivery")
     * @Route("/shop/adressDelivery/{id_user}/edit/{id}" , name="edit_adressDelivery" )
     * @ParamConverter("user", options={"id" = "id_user"})
     * @ParamConverter("AdressDelivery", options={"id" = "id"})
     */
    public function addAdressDelivery(ManagerRegistry $doctrine , AdressDelivery $adressDelivery = null ,User $user =null, Request $request):Response
    {
        $userId =  $request->get("id_user");
        $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $userId]);
        $new=false;  
        if(!$adressDelivery){
            $new= true;
            $adressDelivery =new AdressDelivery();
        }
        // crée le formulaire de type Blog 
        $form = $this->createForm(AdressDeliveryType::class , $adressDelivery );
        $form->handleRequest($request);
        // si envoye et sanitise avec les filter etc protection faille xss puis on execute le tout 
        if($form->isSubmitted() && $form->isValid())
        {
       

            $adressDelivery = $form->getData();
            $adressDelivery->setHaveAdressDelivery($user);         
            $entityManager = $doctrine->getManager();
            // hydrate et protection faille sql 
            $entityManager->persist($adressDelivery);
            $entityManager->flush();
            if($new){
                $message = " à été ajouté avec succès";
            }else{
                $message = " à été mis a jour avec succès";
            }
            $this->addFlash("success" , "Adresse de livraison".$message);
            return $this->redirectToRoute('app_payChek', ['id' => $user->getId()]);
        }
        return $this->render('shop/AdressDeliveryForm.html.twig', [
            'formAdressDelivery' =>  $form->createView(),
            'option' => $new,
            "edit" => $adressDelivery->getId(),
        ]);
    }

    /**
     * Fonction pour afficher le résumer de l'achat en cours avec toutes les informations nécéssaires
     * @Route("/shop/payChek/{id}", name="app_payChek")
     * @ParamConverter("user", options={"id" = "id_user"})
     */
    public function payChek(ManagerRegistry $doctrine,Request $request,?AdressDelivery $adressDelivery, SessionInterface $session, ?User $user,?Command $command,?Contain $contain, ProductSheetRepository $productSheetRepository, CommandRepository $commandRepository) : Response
    {
        // if($_POST){

        // }
        // gestion total et qtt
        $panier = $session->get("panier", [] );
        $panierWithData = [] ;
        foreach( $panier as $id => $quantity)
        {
            $panierWithData[] = [
                'product' => $productSheetRepository->find($id),
                'quantity' => $quantity,
            ];      
        }
        
        $total = 0;
        $qt = 0;
        foreach($panierWithData as $item ){
            $totalItem =  $item['product']->getPrice() * $item['quantity'];
            $item['quantity'];
            $qt += $item['quantity'];
            $total += $totalItem;
        }

        // récuperation infos client
        $user = $this->getUser();
       
            // création formulaire d'addresse facturation
            $command = new Command();
            $form = $this->createForm(CommandType::class , $command );
            $form->handleRequest($request);
            if($form->isSubmitted() && $form->isValid())
            {
                $command = $form->getData();     
                $command->setPay(false);
                $command->setUserOrderCommand($user);
                $command->setTotalAmount($total);  
                // $commandRepository->add($command, true);
                $entityManager = $doctrine->getManager();
                $entityManager->persist($command);
                $entityManager->flush();
    
                 $session->set("command" , $command->getId());
                 $commandId = $session->get("command");
    
                 
                 foreach($panierWithData as $item){
                    $contain = new Contain();
                   
                    $itmeId = $item['product']->getId();
                    $product = $productSheetRepository->findOneBy( ['id' => $itmeId]);     
                    $commandId = $doctrine->getRepository(Command::class)->findOneBy(['id' => $command->getId()]);
    
                    $contain->setCommandContain($commandId);
                    $contain->setQuantity($item['quantity']);
                    $contain->setProductContain( $product);
    
                    $entityManager = $doctrine->getManager();
                    $entityManager->persist($contain);
                    $entityManager->flush();
                }
    
                $this->addFlash("success" , "Votre Commande à été enregistrer avec succès");
                return $this->redirectToRoute('app_payChek', ['id' => $user->getId()]);
            }

            $command = $commandRepository->findByCommandeNonPayer($user->getId());
            $adressDeliverys = $doctrine->getRepository(AdressDelivery::class)->findBy(['haveAdressDelivery' => $user]);
    
            if ($command ) {
                $userInfo = true;
            }else{
                $userInfo = false ;     
            }
            if ($adressDeliverys ) {
                $idAdress = true;
            }else{
                $idAdress = false ;    
            }
            
        

        $adressDelivery  = new AdressDelivery();
        $formAdressDelivery = $this->createForm(AdressDeliveryType::class , $adressDelivery );
        $formAdressDelivery->handleRequest($request);

        if($formAdressDelivery->isSubmitted() && $formAdressDelivery->isValid())
        {
            $date = new \DateTime();
            $adressDelivery = $formAdressDelivery->getData();     
            $adressDelivery->setHaveAdressDelivery($user);
            $adressDelivery->setDateCreation($date);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($adressDelivery);
            $entityManager->flush();

            $this->addFlash("success" , "Votre adresse à été crée avec succès");
            return $this->redirectToRoute('app_payChek', ['id' => $user->getId()]);
        }


        return $this->render('shop/payChek.html.twig', [
            'formCommand' =>  $form->createView(),
            'formAdressDelivery' =>  $formAdressDelivery->createView(),
            "idUser" =>  $userInfo ?? null,
            "idAdress" => $idAdress ?? null,
            "command" => $command,
            "adressDeliverys" => $adressDeliverys ?? null,
            "qt" => $qt,
            "total" => $total,
        ]);
    }


    /**
     * Fonction pour afficher le résumer de l'achat en cours avec toutes les informations nécéssaires
     * @Route("/shop/payment/{id}", name="app_payment")
     * @ParamConverter("Command", options={"id" = "id"})
    */
    public function payment($stripeSK , CommandRepository $commandRepository , SessionInterface $session, Request $request )
    {
        $user = $this->getUser();
        $commande = $commandRepository->findByCommandeNonPayer($user->getId());
        $paniers = $commande->getContains();
        $line_items = [];

        
        foreach ($paniers as $panier) {

            $line_items[] = [
                'price_data' => [
                    'currency' => 'eur',
                    'product_data' => [
                        'name' => $panier->getProductContain()->getName(),
                    ],
                    'unit_amount' => $panier->getProductContain()->getPrice() * 100,
                ],
                'quantity' => $panier->getQuantity(),
            ];
        }

        Stripe::setApiKey($stripeSK);
        $panier = $session->get("panier");
        $sessions = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [
                [
                    $line_items,
                ],
            ],
            'mode' => 'payment',
            'success_url' => $this->generateUrl('app_success', [], UrlGenerator::ABSOLUTE_URL),
            'cancel_url' => $this->generateUrl('app_cancel', [], UrlGenerator::ABSOLUTE_URL),
        ]);

        return $this->redirect($sessions->url ,303 );
    }
    
    /**
     * @Route("/shop/success", name="app_success")
    */
    public function successUrl(CommandRepository $commandRepository, ManagerRegistry $doctrine, CarteService $carteService, ProductSheetRepository $productSheetRepository): Response
    {
        $user = $this->getUser();
        $command = $commandRepository->findByCommandeNonPayer($user->getId());
        $paniers = $command->getContains();
        // Si l'utilisateur n'a pas de commande on le redirige
        // if ( $command->getContains() === null  || $user != $command->getContains() ) {
        //     return $this->redirectToRoute('app_home');
        // }
        foreach($paniers as $panier){
            // a chaque produit on récuprer son id on prend son stocvk initial on récupère la quantité commander et on le soustrait au stock
            $produit = $panier->getProductContain();
            $productSheet = $productSheetRepository->findOneBy(['id' => $produit]);
            
            $qttInit = $productSheet->getStock();
            $qttCommannd = $panier->getQuantity();
            $qttAfter = $qttInit - $qttCommannd;
            // on set le nouveau stock en bdd
            $produitQtt = $productSheet->setStock($qttAfter);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($produitQtt);
            $entityManager->flush();
        } 

        //Si le paiement est passer on change la valeurs du champs payer de la commande
        $command->setPay(true);
        $entityManager = $doctrine->getManager();
        $entityManager->persist($command);
        $entityManager->flush();
        $carteService->deleteAll();

        return $this->render('shop/success.html.twig', [
            "command" => $command,
        ]);
    }


   /**
     * @Route("/shop/cancel", name="app_cancel")
    */
    public function cancelUrl(): Response
    {
        return $this->render('shop/cancel.html.twig', []);
    }

    /**
     * @Route("/facture/{id}", name="facture")
     */
    public function telechargerFacture(Command $command)
    {
        $options = new Options();
        $options->set('isHtml5ParserEnabled', true);
        $options->set('isRemoteEnabled', true);
        $options->set('defaultFont', 'courier');

        $dompdf = new Dompdf($options);

        $html = $this->renderView('shop/facturePdf.html.twig', [
            'sitename' => 'Facture pdf',
            'command' => $command,
        ]);

        $dompdf->loadHtml($html);

        $dompdf->setPaper('A4', 'portrait');

        $dompdf->render();

        $dompdf->stream('Facture n°'.$command->getId(), [
            'Attachment' => true,
        ]);

        exit(0);
    }
    
    /**
     * @Route("/shop/command/{id_user}/edit/{id_command}", name="edit_adress")
     * @ParamConverter("command", options={"id" = "id_command"})
     * @ParamConverter("user", options={"id" = "id_user"})
    */
    public function editCommand(ManagerRegistry $doctrine,Request $request, SessionInterface $session, ?User $user,?Command $command, ProductSheetRepository $productSheetRepository) : Response
    {
        if(!$command){
            $command =new Command();
        }

        // gestion total et qtt
        $panier = $session->get("panier", [] );
        $panierWithData = [] ;
        foreach( $panier as $id => $quantity)
        {
            $panierWithData[] = [
                'product' => $productSheetRepository->find($id),
                'quantity' => $quantity,
            ];      
        }
        $total = 0;
        $qt = 0;
        foreach($panierWithData as $item ){
            $totalItem =  $item['product']->getPrice() * $item['quantity'];
            $item['quantity'];
            $qt += $item['quantity'];
            $total += $totalItem;
        }
        // création formulaire d'addresse facturation
        $form = $this->createForm(CommandType::class , $command );
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $command = $form->getData();     
            $command->setPay(false);
            $command->setUserOrderCommand($user);
            $command->setTotalAmount($total);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($command);
            $entityManager->flush();
            $this->addFlash("success" , "Votre adresse à été crée avec succès");
            return $this->redirectToRoute('app_payChek', ['id' => $user->getId()]);
        }

        return $this->render('shop/editCommand.html.twig', [
            'formCommand' =>  $form->createView(),
        ]);
    }

       
    /**
     * Fonction pour supprimer une adress de livraison
     * @Route("/shop/DeleteAdressDelivery/{id}/{id_user}", name="delete_adressDelivery")
     * @ParamConverter("user", options={"id" = "id_user"})
     */
    public function deleteAdressDelivery(ManagerRegistry $doctrine, User $user, AdressDelivery $adressDelivery, Request $request) :Response
    {
        $userId =  $request->get("id_user");
        $user = $doctrine->getRepository(User::class)->findOneBy(['id' => $userId]);
        $entityManager = $doctrine->getManager();
        $entityManager->remove($adressDelivery);
        $entityManager->flush();
        $this->addFlash("success" , "l'adresse de livraison à bien été supprimé");
        return $this->redirectToRoute('app_payChek', ['id' => $user->getId()]);
    }


    /**
     * Fonction pour enlever 1 produit entièrement dans le panier en session
     * @Route("/shop/panier/DeleteProduit/{id}", name="delete_panier")
     */
    public function delete($id, CarteService $carteService)
    {
        $carteService->delete($id);

        return $this->redirectToRoute('app_panier');
    }

    /**
     * Fonction pour supprimer tout les produits du panier en session
     * @Route("/shop/panier/DeleteAllProduit", name="delete_All_panier")
     */
    public function deleteAll(CarteService $carteService)
    {
        $carteService->deleteAll();

        return $this->redirectToRoute('app_shop');
    }
}

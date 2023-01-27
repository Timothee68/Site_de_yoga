<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\User;
use App\Entity\Reply;
use App\Entity\Command;
use App\Entity\Contact;
use App\Form\ContactType;
use App\Form\UserFormType;
use App\Entity\Reservation;
use App\Entity\AdressDelivery;
use App\Form\PasswordFormType;
use Symfony\Component\Mime\Address;
use App\Repository\CommandRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Csrf\TokenStorage\TokenStorageInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{
    /**
     * @Route("/user", name="app_user")
     */
    public function index(): Response
    {
        return $this->render('user/index.html.twig', [
        ]);
    }

    /**
     * @Route("/presentation", name="app_presentation")
     */
    public function presentation(): Response
    {
        return $this->render('user/presentation.html.twig', [
        ]);
    }

    /**
     * @Route("/command/user", name="app_command_user")
     */
    public function commande(CommandRepository $commandRepository): Response
    {
        $user = $this->getUser();
        $commands = $commandRepository->findBy(["UserOrderCommand" => $user->getId()]);
        return $this->render('user/commande.html.twig', [
            "commands" => $commands,
        ]);
    }


    /**
    * @Route("/user/edit/{id}", name="edit_register")
    */
    public function editRegister(Request $request, User $user, EntityManagerInterface $entityManager): Response
    {
        if(!$user){
            $user = new User();
        }else{
            $user = $this->getUser();
            $form = $this->createForm(UserFormType::class, $user);
            $form->handleRequest($request);
    
            if ($form->isSubmitted() && $form->isValid()) {
                $imgFile = $form->get('img')->getData();
                if ($imgFile) {
                    $newFilename = 'img/imported/'.uniqid().'.'.$imgFile->guessExtension();
                    try {
                        $imgFile->move(
                            $this->getParameter('img_directory'),
                            $newFilename
                        );
                    } catch (FileException $e) {
                        // ... handle exception if something happens during file upload
                    }
                    $user->setImg($newFilename);
                }
                $entityManager->persist($user);
                $entityManager->flush();
                $this->addFlash('success', 'Ton Compte a bien été modifié !');
                return $this->redirectToRoute('app_user' ,['id' => $user->getId()] ); 
            }
            return $this->render('user/editProfil.html.twig', [
                'registrationForm' => $form->createView(),
                'edit' => $user->getId(),
            ]);
        }
    }


    /**
    * @Route("/user/editPassword/{id}", name="edit_password")
    */
    public function editPassword(Request $request, UserPasswordHasherInterface $userPasswordHasher, User $user, EntityManagerInterface $entityManager): Response
    {
        if(!$user){
            $user = new User();
        }else{
            $user = $this->getUser();
            $form = $this->createForm(PasswordFormType::class, $user);
            $form->handleRequest($request);

            if ($form->isSubmitted() && $form->isValid()) {
                // encode the plain password
                $user->setPassword(
                $userPasswordHasher->hashPassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                );

                $entityManager->persist($user);
                $entityManager->flush();

                // do anything else you need here, like send an email
                $this->addFlash('success', 'Ton Mot de passe à bien été modifié !');
                return $this->redirectToRoute('app_user' ,['id' => $user->getId()] ); 
            }
            return $this->render('user/editPassword.html.twig', [
                'registrationForm' => $form->createView(),
                'edit' => $user->getId(),
            ]);
        }    
    }
   
    /**
    * @Route("/contact", name="app_contact")
    */
    public function Contact(ManagerRegistry $doctrine , Contact $contact = null ,MailerInterface $mailer, Request $request): Response
    {
        $contact =new Contact();

        $form = $this->createForm(ContactType::class , $contact );
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid())
        {
            $contact = $form->getData();           
            $entityManager = $doctrine->getManager();
            $entityManager->persist($contact);
            $entityManager->flush();

            $email = $contact->getEmail();
            $message = $contact->getMessageContent();

            $emails = (new TemplatedEmail())
            ->from(new Address( $email))
            ->to('Om-nada-braham@exemple.com')
            ->subject('Mail contact Site Om nada Braham')
            ->text($contact->getName()." ".$contact->getFirstName()." vous a4à envoyer un message depuis votre sit web le contenue du message etant : ". $message )
            ;
            $mailer->send($emails);

            $this->addFlash("success" ,"Message envoyez avec succès");

            return $this->redirectToRoute('app_contact'); 
        }
        return $this->render('contact/index.html.twig', [
            'formContact' =>  $form->createView(),
          
        ]);
    }
        /**
     * @Route("/profil/{id}", name="app_delete")
     */
    public function delete(ManagerRegistry $doctrine, User $user,Request $request, Session $session) :Response
    {
        $user = $this->getUser();
        $reservationUserArray = $doctrine->getRepository(Reservation::class)->findBy(['user' => $user]);
        $adressDeliveryUserArray = $doctrine->getRepository(AdressDelivery::class)->findBy(['haveAdressDelivery' => $user]);
        $postsUserArray = $doctrine->getRepository(Post::class)->findBy(['user' => $user]);
        $commandUserArray = $doctrine->getRepository(Command::class)->findBy(['UserOrderCommand' => $user]);
        $replyRecipientArray = $doctrine->getRepository(Reply::class)->findBy(['recipient' => $user]);
        
        foreach ($reservationUserArray as $reservation) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($reservation);
            $entityManager->flush();
        }
        foreach ($adressDeliveryUserArray as $adresseDelivery) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($adresseDelivery);
            $entityManager->flush();
        }
        foreach ($postsUserArray as $post) {
            $entityManager = $doctrine->getManager();
            $entityManager->remove($post);
            $entityManager->flush();
        }
        foreach ($commandUserArray as $command) {
            $entityManager = $doctrine->getManager();
            $command->setUserOrderCommand(NULL);
            $entityManager->persist($command);
            $entityManager->flush();
        }
        foreach ($replyRecipientArray as $reply) {
            $entityManager = $doctrine->getManager();
            $reply->setSender(NULL);
            $entityManager->persist($reply);
            $entityManager->flush();
        }

        $entityManager = $doctrine->getManager();
        $entityManager->remove($user);
        $entityManager->flush();
       
        
        $session = new Session();
        $session->invalidate();

        $this->addFlash("success" , "Compte à été supprimé avec succès");
        return $this->redirectToRoute("app_home");
    }
}
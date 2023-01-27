<?php

namespace App\Controller;

use App\Entity\Blog;
use App\Entity\Post;
use App\Entity\User;
use App\Entity\Reply;
use App\Entity\Video;
use App\Form\PostType;
use App\Entity\Benefit;
use App\Form\ReplyType;
use App\Entity\ImgCollectionBenefit;
use App\Entity\ProductSheet;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ImgCollectionBenefitRepository;
use App\Repository\PostRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

class HomeController extends AbstractController
{

    /**
     * @Route("/home", name="app_home")
     */
    public function index(ManagerRegistry $doctrine, Post $post = null, Request $request): Response 
    {
        $nbPost = 4;
        $post =new Post();
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $post = $form->getData();
            $post->setUser($this->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash("success" , "le commentaires à été ajouté/Modifié avec succès");

            return $this->redirectToRoute('app_home'); 
        }

            $blogs = $doctrine->getRepository(Blog::class)->findBy([] , ["publicationDate" => "DESC"] , 4);
            $videos = $doctrine->getRepository(Video::class)->findBy([] , ["publication_date" => "DESC"] , 4);
            $benefits = $doctrine->getRepository(Benefit::class)->findBy([] , ['title' => 'DESC']);
            $posts = $doctrine->getRepository(Post::class)->findBy([] , ['creationDate' => 'DESC'] , $nbPost);
            $productSheet = $doctrine->getRepository(ProductSheet::class)->findBy([] , ['price' => 'ASC'] , 4);
            $page = 1;

            return $this->render('home/index.html.twig', [
                'benefits' => $benefits,
                'posts' => $posts,
                'blogs' => $blogs,
                'videos' => $videos,
                'formAddPost' =>  $form->createView(),
                'edit' => 'null',
                'page' => $page,
                "nbPost" => $nbPost,
                "produits" => $productSheet,
            ]);
        }

    /**
    * @Route("/api/home/confirm" , name ="app_confirm")
    */
    public function confirm( ){

        return $this->render('registration/redirectEmailConfirm.html.twig');
    }
    
    /**
    * @Route("/api/home" , name ="api_home")
    */
    public function showMessageApi( PostRepository $postRepository)
    {
        $json = file_get_contents("php://input");
        $datas = json_decode($json);

        $page = (int)$datas->page;
        $limit = (int)$datas->limit;
        // sa défini le objet deja sortie 
        $offset = ($page - 1) * $limit ;

        $posts = $postRepository->findBy([],[],$limit,$offset);
        foreach($posts as $post)
        {
            $template = $this->render("home/_postUser.html.twig",[
                "post" => $postRepository->findOneById($post->getId()),
            ]);
           
            $result["post"][] = $template;
        }
        
        $result["page"] = $page;
        return $this->json($result);
    } 

    /**
     * @Route("/home/editPost/{id}", name="edit_post")
     */
    public function editPost(ManagerRegistry $doctrine, Post $post = null, Request $request): Response 
    {
        if(!$post){
            $post =new Post();
        }
        $form = $this->createForm(PostType::class,$post);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid())
        {
            $post = $form->getData();
            $post->setUser($this->getUser());
            $entityManager = $doctrine->getManager();
            $entityManager->persist($post);
            $entityManager->flush();

            $this->addFlash("success" , "le commentaires à été ajouté/Modifié avec succès");

            return $this->redirectToRoute('app_home'); 
        }

            $blogs = $doctrine->getRepository(Blog::class)->findBy([] , ["publicationDate" => "DESC"] , 4);
            $benefits = $doctrine->getRepository(Benefit::class)->findBy([] , ['title' => 'DESC']);
            $posts = $doctrine->getRepository(Post::class)->findBy([] , ['creationDate' => 'DESC']);
            return $this->render('home/index.html.twig', [
                'benefits' => $benefits,
                'posts' => $posts,
                'blogs' => $blogs,
                'formAddPost' =>  $form->createView(),
                'edit' => 'edit',
            ]);
        }

    /**
     * fonction pour afficher le com du livre d'or
     * @Route("/admin/home/reply", name="app_reply")
     */
    public function showReply(ManagerRegistry $doctrine, Post $post=null, Request $request): Response 
    {
        $posts = $doctrine->getRepository(Post::class)->findBy([] , ['creationDate' => 'DESC']);
        $replys = $doctrine->getRepository(Reply::class)->findAll();
        return $this->render('management/reply.html.twig', [
            'posts' => $posts,
            "replys" => $replys,   
        ]);
    }     

    /**
     * fonction pour répondre le com du livre d'or
     * @Route("/admin/home/reply/{id}", name="add_reply")
     */
    public function replyPost(ManagerRegistry $doctrine, Post $post , Reply $reply=null, Request $request): Response 
    {

        $nbPost = 4;
        $page = 1;
        $reply =new Reply();
        $formReply = $this->createForm(ReplyType::class,$reply);
        $formReply->handleRequest($request);
        if($formReply->isSubmitted() && $formReply->isValid())
        {
            $reply = $formReply->getData();
            $reply->setSender($this->getUser());
            $reply->setRecipient($post);
            $entityManager = $doctrine->getManager();
            $entityManager->persist($reply);
            $entityManager->flush();

            $this->addFlash("success" , "le commentaires à été ajouté/Modifié avec succès");
            return $this->redirectToRoute('app_reply'); 
        }
        return $this->render('management/addReply.html.twig', [
            'formAddReply' =>  $formReply->createView(),
            'post' => $post,
            'page' => $page,
            "nbPost" => $nbPost,
        ]);
    }     


   

    /**
     * @Route("/home/mentionLegal", name="app_mention")
     */       
    public function detailMention(): Response
    {
        return $this->render('home/mentionLegal.html.twig');
    }

    /**
     * @Route("/home/benefit/detail/{id}", name="detail_benefit")
     */
    public function detailBenefit(Benefit $benefit , ImgCollectionBenefitRepository $img): Response
    {
        // requête DQL pour trouver les imgs par l'id du benefit 
        $images = $img->findById($benefit->getId());
        return $this->render('home/detailbenefit.html.twig', [
            'benefit' => $benefit,
            'images' => $images,
        ]);
    }

    /**
     * fonction pour afficher le detail d'un blog
    * @Route("/home/blog/detail/{id}", name="detail_blog")
    */
    public function detailBlog(Blog $blog) : Response
    {
        return $this->render('blog/detail.html.twig', [
            'blog' => $blog,
        ]);
    }

    /**
     * fonction pour afficher le detail d'un blog
    * @Route("/home/video/", name="video")
    */
    public function video(ManagerRegistry $doctrine):Response
    {
        $videos = $doctrine->getRepository(Video::class)->findBy([] , ["publication_date" => "DESC"]);
        return $this->render('video/index.html.twig', [
            'videos' => $videos,
        ]);
    }

    
    /**
    * @Route("/home/delete_post/{id}", name="delete_post")
    */
    public function deleteblog(ManagerRegistry $doctrine, Post $post) :Response
    {
        $entityManager = $doctrine->getManager();
        $entityManager->remove($post);
        $entityManager->flush();
        $this->addFlash("success" , "le message à été supprimé avec succès");
        return $this->redirectToRoute("app_home");
    }
}

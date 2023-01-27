<?php

namespace App\Controller\Admin;

use App\Entity\Blog;
use App\Entity\Benefit;
use App\Entity\Category;
use App\Entity\Command;
use App\Entity\Input;
use App\Entity\MailNewsletter;
use App\Entity\ProductSheet;
use App\Entity\Reply;
use App\Entity\Reservation;
use App\Entity\Session;
use App\Entity\Stage;
use App\Entity\User;
use App\Entity\Video;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;

class DashboardController extends AbstractDashboardController
{
    /**
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        return parent::index();
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new ()
            ->setTitle('<img src="img/logo/logo.jpg" style="width:20%;" > Om Nada Brahma')
            ->setFaviconPath('favicon.ico');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-home');

        yield MenuItem::section('Gestion des préstations');
        yield MenuItem::linkToCrud('Categories', 'fa fa-user', Category::class);
        yield MenuItem::linkToCrud('Préstations', 'fa fa-user', Benefit::class);
        yield MenuItem::linkToCrud('Bénéfices liées', 'fa fa-user', Input::class);

        yield MenuItem::section('Gestion des sessions et stages');
        yield MenuItem::linkToCrud('Session', 'fa fa-user', Session::class);
        yield MenuItem::linkToCrud('Stage', 'fa fa-user', Stage::class);
        yield MenuItem::linkToCrud('Réservations', 'fa fa-user', Reservation::class);

        yield MenuItem::section('Commentaires');
        yield MenuItem::linkToCrud('Réponse commentaire', 'fa fa-user', Reply::class);
        yield MenuItem::linkToCrud('Utilisateurs', 'fa fa-user', User::class);

        yield MenuItem::section('Blog et newsletter');
        yield MenuItem::linkToCrud('Blog', 'fa fa-user', Blog::class);
        yield MenuItem::linkToCrud('Vidéo', 'fa fa-user', Video::class);
        yield MenuItem::linkToCrud('Prépa Newsletter', 'fa fa-user', MailNewsletter::class);

        yield MenuItem::section('shop');
        yield MenuItem::linkToCrud('Produits', 'fa fa-user', ProductSheet::class);
        yield MenuItem::linkToCrud('Facture', 'fa fa-user', Command::class);
    }
}

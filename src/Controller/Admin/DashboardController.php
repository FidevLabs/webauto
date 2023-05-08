<?php

namespace App\Controller\Admin;

use App\Controller\BusinessController;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\{MenuItem, Crud};
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\{Payment, StepsRequest, Category, State, User, ClientMessage, Agency, Address};

class DashboardController extends AbstractDashboardController
{
    public function __construct( private AdminUrlGenerator $adminUrlGenerator) {

    }

    #[Route('/{_locale}/admin', name: 'admin', requirements: ['_locale' => 'fr'])]
    public function index(): Response
    {    
            if (in_array('ROLE_SUPER_ADMIN', $this->getUser()->getRoles()))  {
                $url = $this->adminUrlGenerator
                            ->setRoute('app_dashboard')
                            ->generateUrl();
            }

            $url = $this->adminUrlGenerator
                          ->setController(StepsRequestCrudController::class)
                          ->generateUrl();

                        
            return $this->redirect($url);        

    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('<img src="/picto/webauto_logo.png" alt="Logo" />');
    }

    public function configureCrud(): Crud
    {
        return Crud::new()

                ->setPaginatorPageSize(15)
                // the number of pages to display on each side of the current page
                // e.g. if num pages = 35, current page = 7 and you set ->setPaginatorRangeSize(4)
                // the paginator displays: [Previous]  1 ... 3  4  5  6  [7]  8  9  10  11 ... 35  [Next]
                // set this number to 0 to display a simple "< Previous | Next >" pager
                ->setPaginatorRangeSize(7);

    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToRoute('Tableau de bord', 'fa fa-dashboard text-danger', 'app_dashboard')->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::subMenu('Demandes', 'fa fa-paperclip text-info')->setSubItems([
            MenuItem::linkToCrud('Liste', 'fa fa-list', StepsRequest::class),            
            MenuItem::linkToRoute('Gestion', 'fa fa-sliders', 'app_steps')->setPermission('ROLE_SUPER_ADMIN'),
        ]);

        yield MenuItem::linkToCrud('Les agences', 'fa fa-handshake-o text-warning', Agency::class)->setPermission('ROLE_ADMIN');

        //yield MenuItem::linkToRoute('Statistiques', 'fa fa-bar-chart', 'app_business')->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::linkToCrud('Liste des états', 'fa fa-eye text-dark', State::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::linkToCrud('Catégorie', 'fa fa-tag', Category::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::linkToCrud('Comptes', 'fa fa-users text-success', User::class)->setPermission('ROLE_SUPER_ADMIN');
        
        yield MenuItem::linkToCrud('Moyen de paiement', 'fa fa-money-bill', Payment::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::subMenu('Paramètres', 'fa fa-cogs text-dark')->setSubItems([
            MenuItem::linkToCrud('Message aux clients', 'fa fa-envelope', ClientMessage::class),
            MenuItem::linkToCrud('Email d\'agence', 'fa fa-address-card', Address::class)
        ])->setPermission('ROLE_SUPER_ADMIN');
        



        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

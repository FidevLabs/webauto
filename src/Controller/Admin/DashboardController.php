<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use Symfony\UX\Chartjs\Model\Chart;
use App\Entity\{StepsRequest, Category, State, User, ClientMessage, Agency, Address};

class DashboardController extends AbstractDashboardController
{
    public function __construct( private AdminUrlGenerator $adminUrlGenerator) {

    }

    #[Route('/{_locale}/admin', name: 'admin', requirements: ['_locale' => 'fr'])]
    public function index(): Response
    {
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

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Tableau de bord', 'fa fa-dashboard');

        yield MenuItem::linkToCrud('Catégorie de demande', 'fa fa-list', Category::class)->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::linkToCrud('Liste des demandes', 'fa fa-tasks', StepsRequest::class);

        yield MenuItem::linkToCrud('Les agences', 'fa fa-handshake-o', Agency::class)->setPermission('ROLE_ADMIN');

        yield MenuItem::linkToRoute('Statistiques', 'fa fa-bar-chart', 'app_business')->setPermission('ROLE_SUPER_ADMIN');

        yield MenuItem::subMenu('Paramètres', 'fa fa-gear')->setSubItems([
                MenuItem::linkToCrud('Liste des états', 'fa fa-eye', State::class)->setPermission('ROLE_SUPER_ADMIN'),
                MenuItem::linkToCrud('Comptes', 'fa fa-users', User::class)->setPermission('ROLE_SUPER_ADMIN'),
                MenuItem::linkToCrud('Message client', 'fa fa-message', ClientMessage::class)->setPermission('ROLE_SUPER_ADMIN'),
                MenuItem::linkToRoute('Demandes client', 'fa fa-list', 'app_steps'),
                MenuItem::linkToCrud('Adresse email', 'fa fa-address-card', Address::class)->setPermission('ROLE_SUPER_ADMIN')
        ])->setPermission('ROLE_ADMIN');


        // yield MenuItem::linkToCrud('The Label', 'fas fa-list', EntityClass::class);
    }
}

<?php

namespace App\Controller\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Task;
use App\Entity\User;
use App\Entity\Evils;

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
        return Dashboard::new()
            ->setTitle('Api Plateform');
    }

    // Add Menu Items for sidebar to access CRUD 
    public function configureMenuItems(): iterable
    {
        yield MenuItem::linktoDashboard('Dashboard', 'fa fa-home');
        yield MenuItem::linkToCrud('Missions', 'fas fa-tags', Task::class);
        yield MenuItem::linkToCrud(
            !in_array('ROLE_ADMIN', $this->getUser()->getRoles()) ? 'Modifier mon profil ' : 'Utilisateurs et super héros',
        'fas fa-tags', User::class);
        yield MenuItem::linkToCrud('Méchants', 'fas fa-tags', Evils::class)->setPermission('ROLE_ADMIN');
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Carrier;
use App\Entity\Category;
use App\Entity\Headers;
use App\Entity\Order;
use App\Entity\Personalize;
use App\Entity\Product;
use App\Entity\User;
use App\Entity\FAQ;
use App\Entity\ShippingReturn;
use App\Entity\About;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractDashboardController
{
    /** 
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        // redirect to some CRUD controller
        $routeBuilder = $this->get(AdminUrlGenerator::class);

        return $this->redirect($routeBuilder->setController(OrderCrudController::class)->generateUrl());
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('Administrador de Mi Ecommerce');

    }

public function configureMenuItems(): iterable
{
    yield MenuItem::linkToDashboard('Panel de Control', 'fa fa-home');

    yield MenuItem::section('Ventas');
    yield MenuItem::linkToCrud('Pedidos', 'fas fa-shopping-cart', Order::class);

    yield MenuItem::section('Catálogo');
    yield MenuItem::linkToCrud('Productos', 'fas fa-tag', Product::class);
    yield MenuItem::linkToCrud('Categorías', 'fas fa-list', Category::class);

    yield MenuItem::section('Clientes');
    yield MenuItem::linkToCrud('Usuarios', 'fas fa-user', User::class);

    yield MenuItem::section('Envíos');
    yield MenuItem::linkToCrud('Transportistas', 'fas fa-truck', Carrier::class);
    yield MenuItem::linkToCrud('Envíos y Devoluciones', 'fas fa-box-open', ShippingReturn::class);

    yield MenuItem::section('Contenido y Configuración');
    yield MenuItem::linkToCrud('Empresa', 'fas fa-building', Personalize::class);
    yield MenuItem::linkToCrud('Banners', 'fas fa-image', Headers::class);
    yield MenuItem::linkToCrud('Preguntas Frecuentes', 'fas fa-question-circle', FAQ::class);
    yield MenuItem::linkToCrud('Nosotros', 'fas fa-info-circle', About::class);
}
}

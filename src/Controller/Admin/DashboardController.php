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
            ->setTitle('<b>MONKAI</b> <small>Admin</small>')
            ->renderContentMaximized();
    }

    public function configureAssets(): \EasyCorp\Bundle\EasyAdminBundle\Config\Assets
    {
        return parent::configureAssets()
            ->addCssFile('assets/css/admin-minimal.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Inicio', 'fa fa-th-large');
        yield MenuItem::linkToUrl('Ver Web', 'fas fa-external-link-alt', '/');

        yield MenuItem::subMenu('Ventas', 'fas fa-shopping-basket')->setSubItems([
            MenuItem::linkToCrud('Pedidos', 'fas fa-clipboard-list', Order::class),
            MenuItem::linkToCrud('Clientes', 'fas fa-users', User::class),
        ]);

        yield MenuItem::subMenu('Catálogo', 'fas fa-tags')->setSubItems([
            MenuItem::linkToCrud('Productos', 'fas fa-box', Product::class),
            MenuItem::linkToCrud('Categorías', 'fas fa-folder-open', Category::class),
        ]);

        yield MenuItem::subMenu('Logística', 'fas fa-truck-moving')->setSubItems([
            MenuItem::linkToCrud('Envíos', 'fas fa-truck', Carrier::class),
            MenuItem::linkToCrud('Devoluciones', 'fas fa-undo-alt', ShippingReturn::class),
        ]);

        yield MenuItem::subMenu('Diseño', 'fas fa-palette')->setSubItems([
            MenuItem::linkToCrud('Empresa', 'fas fa-store-alt', Personalize::class),
            MenuItem::linkToCrud('Banners', 'fas fa-images', Headers::class),
        ]);

        yield MenuItem::subMenu('Soporte', 'fas fa-comment-dots')->setSubItems([
            MenuItem::linkToCrud('FAQs', 'fas fa-question-circle', FAQ::class),
            MenuItem::linkToCrud('Nosotros', 'fas fa-id-card', About::class),
        ]);
    }
}

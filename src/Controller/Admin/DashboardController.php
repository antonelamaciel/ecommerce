<?php

namespace App\Controller\Admin;

use App\Entity\Bundle;
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
use App\Entity\Supplier;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGenerator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Controller\Admin\NotificationCrudController;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractDashboardController
{
    /** 
     * @Route("/admin", name="admin")
     */
    public function index(): Response
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager();
        
        return $this->render('admin/dashboard.html.twig', [
            'total_orders' => $em->getRepository(Order::class)->count([]),
            'total_products' => $em->getRepository(Product::class)->count([]),
            'total_users' => $em->getRepository(User::class)->count([]),
            'paid_orders' => $em->getRepository(Order::class)->count(['state' => 1]),
            'pending_orders' => $em->getRepository(Order::class)->count(['state' => 0]),
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle(' <span class="ms-2 fw-bold">MONKAI</span> <small>Admin</small> <a href="/" class="btn-back-admin"><i class="fas fa-store text-white"></i> <span class="d-none d-sm-inline text-white">Volver a la tienda</span></a>')
            ->renderContentMaximized();
    }

    public function configureAssets(): \EasyCorp\Bundle\EasyAdminBundle\Config\Assets
    {
        return parent::configureAssets()
            ->addCssFile('https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css')
            ->addCssFile('assets/css/admin-minimal.css')
            ->addCssFile('assets/css/admin-premium.css')
            ->addJsFile('assets/js/admin-custom.js');
    }

    public function configureMenuItems(): iterable
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager();
        $notificationCount = $em->getRepository(Order::class)->count(['isRead' => false]);

        yield MenuItem::linkToDashboard('Inicio', 'fa fa-th-large');
        yield MenuItem::linkToUrl('Ver Web', 'fas fa-external-link-alt', '/');

        // Sección de Notificaciones
        if ($notificationCount > 0) {
            yield MenuItem::linkToCrud('Notificaciones', 'fas fa-bell', Order::class)
                ->setController(NotificationCrudController::class)
                ->setBadge($notificationCount, 'danger');
        } else {
            yield MenuItem::linkToCrud('Notificaciones', 'fas fa-bell', Order::class)
                ->setController(NotificationCrudController::class);
        }

        yield MenuItem::subMenu('Ventas', 'fas fa-shopping-basket')->setSubItems([
            MenuItem::linkToCrud('Pedidos', 'fas fa-clipboard-list', Order::class),
            MenuItem::linkToCrud('Clientes', 'fas fa-users', User::class),
        ]);

        yield MenuItem::subMenu('Catálogo', 'fas fa-tags')->setSubItems([
            MenuItem::linkToCrud('Productos', 'fas fa-box', Product::class),
            MenuItem::linkToCrud('Categorías', 'fas fa-folder-open', Category::class),
            MenuItem::linkToCrud('Proveedores', 'fas fa-truck-loading', Supplier::class),
            MenuItem::linkToCrud('Promociones', 'fas fa-fire', Bundle::class),
        ]);

        yield MenuItem::subMenu('Logística', 'fas fa-truck-moving')->setSubItems([
            MenuItem::linkToCrud('Transportistas', 'fas fa-truck', Carrier::class),
            MenuItem::linkToCrud('Envíos y Devoluciones', 'fas fa-undo-alt', ShippingReturn::class),
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

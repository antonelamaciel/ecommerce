<?php

namespace App\Controller\Admin;

use App\Entity\CashMovement;
use App\Entity\Order;
use App\Entity\OrderDetails;
use App\Entity\Product;
use App\Repository\OrderRepository;
use App\Repository\ProductRepository;
use App\Repository\CashMovementRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractDashboardController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/stats", name="admin_stats")
     */
    public function stats(Request $request): Response

    {
        $month = $request->query->get('month', date('m'));
        $year = $request->query->get('year', date('Y'));

        // 1. Sales Chart (Daily/Monthly)
        $salesData = $this->getSalesChartData($month, $year);

        // 2. Cash Movement Box
        $cashCalculated = $this->getCashCalculated();

        // 3. Top 10 Products
        $topProducts = $this->getTopProducts($month, $year);

        // 4. Least Sold Products
        $leastSoldProducts = $this->getLeastSoldProducts($month, $year);

        // 5. Stock Alerts
        $stockAlerts = $this->getStockAlerts();

        // 6. Weekly Sales
        $weeklySales = $this->getWeeklySales();

        return $this->render('admin/stats.html.twig', [
            'month' => $month,
            'year' => $year,
            'salesData' => $salesData,
            'cashCalculated' => $cashCalculated,
            'topProducts' => $topProducts,
            'leastSoldProducts' => $leastSoldProducts,
            'stockAlerts' => $stockAlerts,
            'weeklySales' => $weeklySales,
        ]);
    }

    private function getSalesChartData($month, $year): array
    {
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));
        $labels = range(1, $daysInMonth);
        $data = array_fill(0, $daysInMonth, 0);


        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-01 00:00:00");
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-$daysInMonth 23:59:59");

        $orders = $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->andWhere('o.state IN (:states)')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('states', [1, 2, 3]) // Paid, In Prep, Shipped
            ->getQuery()
            ->getResult();

        $maxMoney = 1000;
        foreach ($orders as $order) {
            $day = (int)$order->getCreatedAt()->format('j');
            $data[$day - 1] += ($order->getTotal() / 100);
            if ($data[$day - 1] > $maxMoney) {
                $maxMoney = $data[$day - 1];
            }
        }

        return [
            'labels' => $labels,
            'data' => $data,
            'maxMoney' => $maxMoney
        ];
    }

    private function getCashCalculated(): float
    {
        $totalCents = $this->entityManager->getRepository(CashMovement::class)->getTotalBalance();
        return (float) ($totalCents / 100);
    }

    private function getTopProducts($month, $year): array
    {
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));
        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-01 00:00:00");
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-$daysInMonth 23:59:59");

        return $this->entityManager->getRepository(OrderDetails::class)->createQueryBuilder('od')
            ->select('COALESCE(p.name, od.product) as name, MAX(pRef.image) as image, SUM(od.quantity) as totalSold')
            ->leftJoin('od.productObject', 'p')
            ->join('od.bindedOrder', 'o')
            // Find image from the whole catalog based on name match
            ->leftJoin('App\Entity\Product', 'pRef', 'WITH', 'pRef.name = COALESCE(p.name, od.product)')
            ->where('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->andWhere('o.state IN (:states)')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('states', [1, 2, 3])
            ->groupBy('name')
            ->orderBy('totalSold', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();
    }

    private function getLeastSoldProducts($month, $year): array
    {
        $daysInMonth = (int)date('t', strtotime("$year-$month-01"));
        $startDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-01 00:00:00");
        $endDate = \DateTime::createFromFormat('Y-m-d H:i:s', "$year-$month-$daysInMonth 23:59:59");

        return $this->entityManager->getRepository(OrderDetails::class)->createQueryBuilder('od')
            ->select('COALESCE(p.name, od.product) as name, SUM(od.quantity) as totalSold')
            ->leftJoin('od.productObject', 'p')
            ->join('od.bindedOrder', 'o')
            ->where('o.createdAt >= :start')
            ->andWhere('o.createdAt <= :end')
            ->andWhere('o.state IN (:states)')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->setParameter('states', [1, 2, 3])
            ->groupBy('name')
            ->orderBy('totalSold', 'ASC')
            ->setMaxResults(8)
            ->getQuery()
            ->getResult();
    }


    private function getStockAlerts(): array
    {
        return $this->entityManager->getRepository(Product::class)->createQueryBuilder('p')
            ->where('p.stock <= :lowStock')
            ->setParameter('lowStock', 5)
            ->orderBy('p.stock', 'ASC')
            ->getQuery()
            ->getResult();
    }

    private function getWeeklySales(): array
    {
        // Monday to Sunday of the current week
        $monday = new \DateTime('monday this week 00:00:00');
        $sunday = new \DateTime('sunday this week 23:59:59');

        $orders = $this->entityManager->getRepository(Order::class)->createQueryBuilder('o')
            ->where('o.createdAt BETWEEN :start AND :end')
            ->andWhere('o.state IN (:states)')
            ->setParameter('start', $monday)
            ->setParameter('end', $sunday)
            ->setParameter('states', [1, 2, 3])
            ->getQuery()
            ->getResult();

        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        $data = array_fill(0, 7, 0);

        foreach ($orders as $order) {
            // N is 1 (Monday) to 7 (Sunday)
            $dayIndex = (int)$order->getCreatedAt()->format('N') - 1;
            $data[$dayIndex] += ($order->getTotal() / 100);
        }

        return [
            'labels' => $days,
            'data' => $data
        ];
    }
}

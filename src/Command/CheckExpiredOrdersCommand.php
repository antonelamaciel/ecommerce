<?php

namespace App\Command;

use App\Entity\Order;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:check-expired-orders',
    description: 'Cancels expired cash/transfer orders and returns stock.',
)]
class CheckExpiredOrdersCommand extends Command
{
    private $entityManager;
    private $orderRepository;

    public function __construct(EntityManagerInterface $entityManager, OrderRepository $orderRepository)
    {
        $this->entityManager = $entityManager;
        $this->orderRepository = $orderRepository;

        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $now = new \DateTime();

        // Get orders that are NOT "Paid" (1), "In Preparation" (2), "Shipped" (3), or "Cancelled" (5)
        $orders = $this->orderRepository->createQueryBuilder('o')
            ->where('o.state NOT IN (:finalStates)')
            ->setParameter('finalStates', [1, 2, 3, 5])
            ->getQuery()
            ->getResult();

        $cancelledCount = 0;

        foreach ($orders as $order) {
            $createdAt = $order->getCreatedAt();
            $paymentMethod = $order->getPaymentMethod();
            $shouldCancel = false;

            if ($paymentMethod === 'cash') {
                // 72 business hours = 3 business days
                $limit = $this->addBusinessDays(clone $createdAt, 3);
                if ($now > $limit) {
                    $shouldCancel = true;
                }
            } elseif ($paymentMethod === 'transfer') {
                // 6 business days
                $limit = $this->addBusinessDays(clone $createdAt, 6);
                if ($now > $limit) {
                    $shouldCancel = true;
                }
            }

            if ($shouldCancel) {
                $order->setState(5); // Cancelado

                // Return stock
                foreach ($order->getOrderDetails() as $detail) {
                    $product = $detail->getProductObject();
                    if ($product && $product->getStock() !== null) {
                        $product->setStock($product->getStock() + $detail->getQuantity());
                    }
                }
                $cancelledCount++;
            }
        }

        if ($cancelledCount > 0) {
            $this->entityManager->flush();
            $io->success(sprintf('Cancelled %d expired orders and returned their stock.', $cancelledCount));
        } else {
            $io->info('No expired orders found.');
        }

        return Command::SUCCESS;
    }

    /**
     * Adds N business days (Mon-Fri) to a date.
     */
    private function addBusinessDays(\DateTime $date, int $days): \DateTime
    {
        $count = 0;
        while ($count < $days) {
            $date->modify('+1 day');
            if ($date->format('N') < 6) { // 1=Mon, 5=Fri
                $count++;
            }
        }
        return $date;
    }
}

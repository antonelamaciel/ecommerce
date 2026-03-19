<?php

namespace App\EventSubscriber;

use App\Entity\Order;
use App\Entity\CashMovement;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManagerInterface;

class OrderPaidSubscriber implements EventSubscriberInterface
{
    public function getSubscribedEvents(): array
    {
        return [
            Events::postUpdate,
            Events::postPersist,
        ];
    }

    public function postPersist(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof Order && $entity->getState() == 1) {
            $this->createCashMovement($entity, $args->getObjectManager());
        }
    }

    public function postUpdate(LifecycleEventArgs $args): void
    {
        $entity = $args->getObject();

        if (!$entity instanceof Order) {
            return;
        }

        $entityManager = $args->getObjectManager();
        $unitOfWork = $entityManager->getUnitOfWork();
        $changeSet = $unitOfWork->getEntityChangeSet($entity);

        // Check if 'state' was changed and is now 1 (Paid)
        if (isset($changeSet['state'])) {
            [$oldState, $newState] = $changeSet['state'];

            if ($newState == 1 && $oldState != 1) {
                $this->createCashMovement($entity, $entityManager);
            }
        }
    }

    private function createCashMovement(Order $order, EntityManagerInterface $em): void
    {
        // Check if movement already exists for this order
        $existing = $em->getRepository(CashMovement::class)->findOneBy(['orderReference' => $order->getReference()]);
        if ($existing) {
            return;
        }

        $movement = new CashMovement();
        $movement->setType(CashMovement::TYPE_INGRESS);
        $movement->setReason(CashMovement::REASON_SALE);
        $movement->setAmount($order->getTotal());
        $movement->setOrderReference($order->getReference());
        $movement->setDate(new \DateTime());

        $em->persist($movement);
        // We don't flush here because we are in postUpdate, a flush might be risky if not careful, 
        // but since we are adding a NEW entity, we should flush it eventually.
        // Actually for postUpdate, if we want to save new entities, we might need a separate flush.
        // But be careful with infinite loops.
        $em->flush(); 
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\CashMovement;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\KeyValueStore;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class CashMovementCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return CashMovement::class;
    }

    public function configureResponseParameters(KeyValueStore $responseParameters): KeyValueStore
    {
        /** @var EntityManagerInterface $em */
        $em = $this->container->get('doctrine')->getManager();
        $totalBalance = $em->getRepository(CashMovement::class)->getTotalBalance();

        $responseParameters->set('total_balance', $totalBalance);

        return $responseParameters;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Movimiento de Caja')
            ->setEntityLabelInPlural('Movimientos de caja')
            ->setDefaultSort(['date' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        $id = IdField::new('id')->hideOnForm();
        $type = ChoiceField::new('type', 'Tipo')
                ->setChoices([
                    'Ingreso' => CashMovement::TYPE_INGRESS,
                    'Egreso' => CashMovement::TYPE_EGRESS,
                ])
                ->setRequired(true);
        $reason = ChoiceField::new('reason', 'Motivo')
                ->setChoices([
                    'Venta' => CashMovement::REASON_SALE,
                    'Compra mercadería' => CashMovement::REASON_PURCHASE,
                    'Envío' => CashMovement::REASON_SHIPPING,
                    'Ingreso propio (de mi cuenta)' => CashMovement::REASON_OWN_INGRESS,
                    'Retiro' => CashMovement::REASON_WITHDRAWAL,
                ])
                ->setRequired(true);
        
        $amountIndex = MoneyField::new('amount', 'Monto')
            ->setCurrency('ARS')
            ->setStoredAsCents(false)
            ->formatValue(function ($value, $entity) {
                $rawAmount = $entity->getAmount() ?? 0.0;
                $prefix = $entity->getType() === CashMovement::TYPE_INGRESS ? '+' : '-';
                $color = $entity->getType() === CashMovement::TYPE_INGRESS ? 'text-success' : 'text-danger';
                return sprintf('<span class="%s fw-bold">%s $ %s</span>', $color, $prefix, number_format($rawAmount, 2, ',', '.'));
            })
            ->onlyOnIndex();

        $amountForm = MoneyField::new('amount', 'Monto')
                ->setCurrency('ARS')
                ->setStoredAsCents(false)
                ->setRequired(true)
                ->hideOnIndex();

        $date = DateTimeField::new('date', 'Fecha')
                ->setRequired(true);

        return [$id, $type, $reason, $amountIndex, $amountForm, $date];
    }
}

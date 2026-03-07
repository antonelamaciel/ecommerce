<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class UserCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            EmailField::new('email', 'Correo del Usuario'),
            TextField::new('firstname', 'Nombre del usuario'),
            TextField::new('lastname', 'Apellido del usuario'),
            ArrayField::new('roles', 'Rol')->hideOnIndex()->hideonform(),
        ];
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Cliente/Usuario')
            ->setEntityLabelInPlural('Clientes/Usuarios')
            ->overrideTemplate('crud/index', 'admin/sales/clients.html.twig')
        ;
    }
}

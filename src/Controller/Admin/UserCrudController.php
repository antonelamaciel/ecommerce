<?php

namespace App\Controller\Admin;

use App\Entity\User;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\ArrayField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Doctrine\ORM\EntityManagerInterface;

class UserCrudController extends AbstractCrudController
{
    private UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public static function getEntityFqcn(): string
    {
        return User::class;
    }

    public function configureActions(Actions $actions): Actions 
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL);
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm()->hideOnIndex(),
            EmailField::new('email', 'Correo del Usuario')->setColumns(4),
            TextField::new('firstname', 'Nombre del usuario')->setColumns(4),
            TextField::new('lastname', 'Apellido del usuario')->setColumns(4),
            TextField::new('password', 'Contraseña')
                ->setFormType(RepeatedType::class)
                ->setFormTypeOptions([
                    'type' => PasswordType::class,
                    'first_options'  => [
                        'label'    => 'Contraseña',
                        'row_attr' => ['style' => 'max-width: 320px;'],
                        'attr'     => ['style' => 'max-width: 320px;'],
                    ],
                    'second_options' => [
                        'label'    => 'Repetir Contraseña',
                        'row_attr' => ['style' => 'max-width: 320px;'],
                        'attr'     => ['style' => 'max-width: 320px;'],
                    ],
                    'mapped' => false,
                ])
                ->setRequired($pageName === Crud::PAGE_NEW)
                ->onlyOnForms()
                ->setColumns(4),
            ChoiceField::new('roles', 'Roles del usuario')
                ->setChoices([
                    'Usuario comun' => 'ROLE_USER',
                    'Administrador' => 'ROLE_ADMIN',
                ])
                ->allowMultipleChoices()
                ->renderExpanded()
                ->setColumns(4)
                ->onlyOnForms(),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->hashPassword($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function hashPassword(User $user): void
    {
        $password = $this->getContext()->getRequest()->request->all('User')['password']['first'] ?? null;
        if ($password) {
            $user->setPassword($this->passwordHasher->hashPassword($user, $password));
        }
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

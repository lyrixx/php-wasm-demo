<?php

namespace App\Controller\Admin;

use App\Entity\Customer;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CountryField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\EmailField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\CountryFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;

final class CustomerCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Customer::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Customer')
            ->setEntityLabelInPlural('Customers')
            ->setSearchFields(['name', 'email'])
            ->setDefaultSort(['joinedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(CountryFilter::new('country', 'Country'))
            ->add(BooleanFilter::new('vip', 'VIP'))
            ->add(DateTimeFilter::new('joinedAt', 'Joined'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield Field::new('initials', '')
            ->setVirtual(true)
            ->setSortable(false)
            ->setTemplatePath('admin/field/initials.html.twig')
            ->onlyOnIndex();
        yield TextField::new('name', 'Name');
        yield EmailField::new('email', 'Email');
        yield CountryField::new('country', 'Country');
        yield BooleanField::new('vip', 'VIP');
        yield DateField::new('joinedAt', 'Joined')->hideOnForm();
        yield AssociationField::new('orders', 'Orders')->hideOnForm();
    }
}

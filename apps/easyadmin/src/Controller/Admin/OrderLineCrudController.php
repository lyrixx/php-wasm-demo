<?php

namespace App\Controller\Admin;

use App\Entity\OrderLine;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;

/**
 * Only the form of a line, in the form of an order.
 */
final class OrderLineCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return OrderLine::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('album', 'Album')->autocomplete()->setColumns(7);
        yield IntegerField::new('quantity', 'Quantity')->setColumns(2);
        yield MoneyField::new('unitPriceInCents', 'Unit price')->setCurrency('EUR')->setColumns(3);
    }
}

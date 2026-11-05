<?php

namespace App\Controller\Admin;

use App\Entity\Track;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

/**
 * Only the form of a track, in the form of an album.
 */
final class TrackCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Track::class;
    }

    public function configureFields(string $pageName): iterable
    {
        yield IntegerField::new('position', '#')->setColumns(2);
        yield TextField::new('title', 'Title')->setColumns(7);
        yield IntegerField::new('durationInSeconds', 'Seconds')->setColumns(3);
    }
}

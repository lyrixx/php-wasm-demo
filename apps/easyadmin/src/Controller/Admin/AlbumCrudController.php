<?php

namespace App\Controller\Admin;

use App\Entity\Album;
use App\Entity\Format;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;
use EasyCorp\Bundle\EasyAdminBundle\Field\FormField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Translation\TranslatableMessage;

final class AlbumCrudController extends AbstractCrudController
{
    private const RESTOCKED_COPIES = 10;

    public function __construct(
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Album::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Album')
            ->setEntityLabelInPlural('Albums')
            ->setSearchFields(['title', 'artist.name', 'genre.name', 'tracks.title'])
            ->setDefaultSort(['id' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $restock = Action::new('restock', 'Restock', 'fa fa-truck-ramp-box')
            ->linkToCrudAction('restock')
            ->renderAsForm()
            ->displayIf(static fn (Album $album): bool => $album->isLowStock());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $restock)
            ->add(Crud::PAGE_DETAIL, $restock)
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(EntityFilter::new('genre', 'Genre'))
            ->add(EntityFilter::new('artist', 'Artist'))
            ->add(ChoiceFilter::new('format', 'Format')->setTranslatableChoices(Format::cases()))
            ->add(NumericFilter::new('releaseYear', 'Year'))
            ->add(NumericFilter::new('stock', 'Stock'))
            ->add(BooleanFilter::new('featured', 'Featured'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield FormField::addTab('Album', 'fa fa-compact-disc');
        yield Field::new('cover', 'Cover')
            ->setVirtual(true)
            ->setSortable(false)
            ->setTemplatePath('admin/field/cover.html.twig')
            ->hideOnForm();
        yield TextField::new('title', 'Title')->setColumns(8);
        yield AssociationField::new('artist', 'Artist')->autocomplete()->setColumns(4);
        yield AssociationField::new('genre', 'Genre')->setColumns(4);
        yield IntegerField::new('releaseYear', 'Year')->setNumberFormat('%d')->setColumns(2);
        yield ChoiceField::new('format', 'Format')->renderAsBadges()->setColumns(2);
        yield MoneyField::new('priceInCents', 'Price')->setCurrency('EUR')->setColumns(2);
        yield IntegerField::new('stock', 'Stock')
            ->setTemplatePath('admin/field/stock.html.twig')
            ->setColumns(2);
        yield BooleanField::new('featured', 'Featured');
        yield TextEditorField::new('description', 'Description')->hideOnIndex();

        yield FormField::addTab('Tracks', 'fa fa-list-ol');
        yield CollectionField::new('tracks', 'Tracks')
            ->useEntryCrudForm(TrackCrudController::class)
            ->setEntryIsComplex()
            ->hideOnIndex();
    }

    #[AdminRoute('/{entityId}/restock', 'restock', options: ['methods' => ['POST']])]
    public function restock(AdminContext $context): RedirectResponse
    {
        /** @var Album $album */
        $album = $context->getEntity()->getInstance();
        $album->restock(self::RESTOCKED_COPIES);
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('%copies% copies of %album% are on their way.', ['%copies%' => self::RESTOCKED_COPIES, '%album%' => $album->getTitle()]));

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }
}

<?php

namespace App\Controller\Admin;

use App\Entity\Review;
use App\Repository\ReviewRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\BooleanField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IntegerField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\BooleanFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\NumericFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

final class ReviewCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly ReviewRepository $reviews,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Review::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Review')
            ->setEntityLabelInPlural('Reviews')
            ->setSearchFields(['comment', 'album.title', 'customer.name'])
            ->setDefaultSort(['writtenAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        return $actions
            ->addBatchAction(Action::new('approveSelected', 'Approve', 'fa fa-check')->linkToCrudAction('approveSelected'))
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(BooleanFilter::new('approved', 'Approved'))
            ->add(NumericFilter::new('rating', 'Rating'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield AssociationField::new('album', 'Album')->autocomplete();
        yield AssociationField::new('customer', 'Customer')->autocomplete();
        yield IntegerField::new('rating', 'Rating')->setTemplatePath('admin/field/rating.html.twig');
        yield TextareaField::new('comment', 'Comment')->setMaxLength(90);
        yield BooleanField::new('approved', 'Approved');
        yield DateField::new('writtenAt', 'Written')->hideOnForm();
    }

    #[AdminRoute('/batch-approve', 'batch_approve', options: ['methods' => ['POST']])]
    public function approveSelected(BatchActionDto $batchAction): Response
    {
        if (!$this->isCsrfTokenValid(\sprintf('ea-batch-action-%s-%s', $batchAction->getName(), self::getEntityFqcn()), $batchAction->getCsrfToken())) {
            throw $this->createAccessDeniedException();
        }

        $reviews = $this->reviews->findBy(['id' => $batchAction->getEntityIds()]);
        foreach ($reviews as $review) {
            $review->approve();
        }
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('%count% reviews are approved.', ['%count%' => \count($reviews)]));

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }
}

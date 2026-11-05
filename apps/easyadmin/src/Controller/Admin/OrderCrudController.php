<?php

namespace App\Controller\Admin;

use App\Entity\Order;
use App\Entity\OrderStatus;
use App\Repository\OrderRepository;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminRoute;
use EasyCorp\Bundle\EasyAdminBundle\Config\Action;
use EasyCorp\Bundle\EasyAdminBundle\Config\Actions;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Filters;
use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Dto\BatchActionDto;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ChoiceField;
use EasyCorp\Bundle\EasyAdminBundle\Field\CollectionField;
use EasyCorp\Bundle\EasyAdminBundle\Field\DateTimeField;
use EasyCorp\Bundle\EasyAdminBundle\Field\MoneyField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use EasyCorp\Bundle\EasyAdminBundle\Filter\ChoiceFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\DateTimeFilter;
use EasyCorp\Bundle\EasyAdminBundle\Filter\EntityFilter;
use EasyCorp\Bundle\EasyAdminBundle\Router\AdminUrlGeneratorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\TranslatableMessage;

final class OrderCrudController extends AbstractCrudController
{
    public function __construct(
        private readonly AdminUrlGeneratorInterface $adminUrlGenerator,
        private readonly EntityManagerInterface $entityManager,
        private readonly OrderRepository $orders,
    ) {
    }

    public static function getEntityFqcn(): string
    {
        return Order::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Order')
            ->setEntityLabelInPlural('Orders')
            ->setSearchFields(['reference', 'customer.name', 'customer.email'])
            ->setDefaultSort(['placedAt' => 'DESC']);
    }

    public function configureActions(Actions $actions): Actions
    {
        $ship = Action::new('ship', 'Ship', 'fa fa-truck-fast')
            ->linkToCrudAction('ship')
            ->renderAsForm()
            ->displayIf(static fn (Order $order): bool => $order->canShip());

        return $actions
            ->add(Crud::PAGE_INDEX, Action::DETAIL)
            ->add(Crud::PAGE_INDEX, $ship)
            ->add(Crud::PAGE_DETAIL, $ship)
            ->addBatchAction(Action::new('shipSelected', 'Ship', 'fa fa-truck-fast')->linkToCrudAction('shipSelected'))
            ->setPermission(Action::DELETE, 'ROLE_ADMIN');
    }

    public function configureFilters(Filters $filters): Filters
    {
        return $filters
            ->add(ChoiceFilter::new('status', 'Status')->setTranslatableChoices(OrderStatus::cases()))
            ->add(EntityFilter::new('customer', 'Customer'))
            ->add(DateTimeFilter::new('placedAt', 'Placed'));
    }

    public function configureFields(string $pageName): iterable
    {
        yield TextField::new('reference', 'Reference')->hideOnForm();
        yield AssociationField::new('customer', 'Customer')->autocomplete();
        yield ChoiceField::new('status', 'Status')->renderAsBadges(self::badges());
        yield DateTimeField::new('placedAt', 'Placed')->hideOnForm();
        yield MoneyField::new('totalInCents', 'Total')
            ->setCurrency('EUR')
            ->setSortable(false)
            ->hideOnForm();
        yield CollectionField::new('lines', 'Lines')
            ->useEntryCrudForm(OrderLineCrudController::class)
            ->setEntryIsComplex()
            ->hideOnIndex();
    }

    #[AdminRoute('/{entityId}/ship', 'ship', options: ['methods' => ['POST']])]
    public function ship(AdminContext $context): RedirectResponse
    {
        /** @var Order $order */
        $order = $context->getEntity()->getInstance();
        $order->ship();
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('The order %reference% is on its way.', ['%reference%' => $order->getReference()]));

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    #[AdminRoute('/batch-ship', 'batch_ship', options: ['methods' => ['POST']])]
    public function shipSelected(BatchActionDto $batchAction): Response
    {
        if (!$this->isCsrfTokenValid(\sprintf('ea-batch-action-%s-%s', $batchAction->getName(), self::getEntityFqcn()), $batchAction->getCsrfToken())) {
            throw $this->createAccessDeniedException();
        }

        $shipped = 0;
        foreach ($this->orders->findBy(['id' => $batchAction->getEntityIds()]) as $order) {
            if ($order->canShip()) {
                $order->ship();
                ++$shipped;
            }
        }
        $this->entityManager->flush();

        $this->addFlash('success', new TranslatableMessage('%count% orders are on their way, the others are not paid or already shipped.', ['%count%' => $shipped]));

        return $this->redirect($this->adminUrlGenerator->setController(self::class)->setAction(Action::INDEX)->generateUrl());
    }

    /**
     * @return array<string, string> the color of each badge
     */
    private static function badges(): array
    {
        $badges = [];
        foreach (OrderStatus::cases() as $status) {
            $badges[$status->name] = $status->badge();
        }

        return $badges;
    }
}

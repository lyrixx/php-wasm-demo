<?php

namespace App\Controller\Admin;

use App\Repository\AlbumRepository;
use App\Repository\OrderRepository;
use App\Repository\ReviewRepository;
use EasyCorp\Bundle\EasyAdminBundle\Attribute\AdminDashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\Assets;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Config\Dashboard;
use EasyCorp\Bundle\EasyAdminBundle\Config\MenuItem;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractDashboardController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Kernel;

// Keep the requirement in sync with enabled_locales in config/packages/framework.yaml
#[AdminDashboard(routePath: '/admin/{_locale}', routeName: 'admin', routeOptions: ['defaults' => ['_locale' => 'en'], 'requirements' => ['_locale' => 'en|fr']])]
final class DashboardController extends AbstractDashboardController
{
    private const RECENT_DAYS = 30;
    private const WEEKS_OF_THE_CHART = 12;

    public function __construct(
        private readonly AlbumRepository $albums,
        private readonly OrderRepository $orders,
        private readonly ReviewRepository $reviews,
    ) {
    }

    public function index(): Response
    {
        $now = new \DateTimeImmutable();
        $recent = $now->modify(\sprintf('-%d days', self::RECENT_DAYS));
        $previous = $recent->modify(\sprintf('-%d days', self::RECENT_DAYS));

        $revenueInCents = $this->orders->revenueInCentsSince($recent);
        $ordersCount = $this->orders->countSince($recent);
        $weeks = $this->orders->weeklyRevenue($now, self::WEEKS_OF_THE_CHART);

        return $this->render('admin/dashboard.html.twig', [
            'average_basket_in_cents' => 0 === $ordersCount ? 0 : intdiv($revenueInCents, $ordersCount),
            'best_sellers' => $this->albums->findBestSellers($recent, 5),
            'best_week_in_cents' => max(1, ...array_column($weeks, 'revenueInCents')),
            'genres' => $this->orders->revenueByGenreSince($recent),
            'latest_orders' => $this->orders->findLatest(6),
            'low_on_stock' => $this->albums->findLowOnStock(5),
            'orders_count' => $ordersCount,
            'php_version' => \PHP_VERSION,
            'previous_revenue_in_cents' => $this->orders->revenueInCentsSince($previous) - $revenueInCents,
            'recent_days' => self::RECENT_DAYS,
            'revenue_in_cents' => $revenueInCents,
            'symfony_version' => Kernel::VERSION,
            'weeks' => $weeks,
        ]);
    }

    public function configureDashboard(): Dashboard
    {
        return Dashboard::new()
            ->setTitle('🐘 Wax Elephant')
            ->setFaviconPath('data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22%3E%3Ctext y=%22.9em%22 font-size=%2290%22%3E🐘%3C/text%3E%3C/svg%3E')
            ->setLocales(['en' => 'English', 'fr' => 'Français'])
            ->renderContentMaximized();
    }

    public function configureCrud(): Crud
    {
        return Crud::new()->setPaginatorPageSize(15);
    }

    public function configureAssets(): Assets
    {
        return Assets::new()->addCssFile('styles/admin.css');
    }

    public function configureMenuItems(): iterable
    {
        yield MenuItem::linkToDashboard('Dashboard', 'fa fa-gauge-high');

        yield MenuItem::section('Catalog');
        yield MenuItem::linkTo(AlbumCrudController::class, 'Albums', 'fa fa-compact-disc')
            ->setBadge($this->albums->countLowOnStock() ?: null, 'warning');
        yield MenuItem::linkTo(ArtistCrudController::class, 'Artists', 'fa fa-guitar');
        yield MenuItem::linkTo(GenreCrudController::class, 'Genres', 'fa fa-tags');

        yield MenuItem::section('Sales');
        yield MenuItem::linkTo(OrderCrudController::class, 'Orders', 'fa fa-box')
            ->setBadge($this->orders->countToShip() ?: null, 'info');
        yield MenuItem::linkTo(CustomerCrudController::class, 'Customers', 'fa fa-users');
        yield MenuItem::linkTo(ReviewCrudController::class, 'Reviews', 'fa fa-star')
            ->setBadge($this->reviews->countToModerate() ?: null, 'danger');
    }
}

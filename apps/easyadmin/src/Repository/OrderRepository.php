<?php

namespace App\Repository;

use App\Entity\Order;
use App\Entity\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Order>
 */
final class OrderRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Order::class);
    }

    /**
     * @return list<Order>
     */
    public function findLatest(int $limit): array
    {
        return $this->createQueryBuilder('o')
            ->addSelect('customer')
            ->join('o.customer', 'customer')
            ->orderBy('o.placedAt', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function revenueInCentsSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('SUM(line.quantity * line.unitPriceInCents)')
            ->join('o.lines', 'line')
            ->where('o.placedAt >= :since AND o.status != :cancelled')
            ->setParameter('since', $since)
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countSince(\DateTimeImmutable $since): int
    {
        return (int) $this->createQueryBuilder('o')
            ->select('COUNT(o)')
            ->where('o.placedAt >= :since AND o.status != :cancelled')
            ->setParameter('since', $since)
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function countToShip(): int
    {
        return $this->count(['status' => OrderStatus::Paid]);
    }

    /**
     * What each of the last weeks sold, the oldest one first.
     *
     * @return list<array{week: \DateTimeImmutable, revenueInCents: int}>
     */
    public function weeklyRevenue(\DateTimeImmutable $now, int $weeks): array
    {
        $monday = $now->modify('monday this week midnight');
        $revenues = [];
        for ($i = $weeks - 1; $i >= 0; --$i) {
            $week = $monday->modify(\sprintf('-%d weeks', $i));
            $revenues[$week->format('Y-m-d')] = ['week' => $week, 'revenueInCents' => 0];
        }

        $rows = $this->createQueryBuilder('o')
            ->select('o.placedAt, line.quantity, line.unitPriceInCents')
            ->join('o.lines', 'line')
            ->where('o.placedAt >= :since AND o.status != :cancelled')
            ->setParameter('since', $monday->modify(\sprintf('-%d weeks', $weeks - 1)))
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->getQuery()
            ->getArrayResult();
        foreach ($rows as $row) {
            $key = $row['placedAt']->modify('monday this week')->format('Y-m-d');
            $revenues[$key]['revenueInCents'] += $row['quantity'] * $row['unitPriceInCents'];
        }

        return array_values($revenues);
    }

    /**
     * @return array<string, array{color: string, revenueInCents: int}> by name of genre, the best one first
     */
    public function revenueByGenreSince(\DateTimeImmutable $since): array
    {
        $rows = $this->createQueryBuilder('o')
            ->select('genre.name, genre.color, SUM(line.quantity * line.unitPriceInCents) AS revenueInCents')
            ->join('o.lines', 'line')
            ->join('line.album', 'album')
            ->join('album.genre', 'genre')
            ->where('o.placedAt >= :since AND o.status != :cancelled')
            ->setParameter('since', $since)
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->groupBy('genre.id')
            ->orderBy('revenueInCents', 'DESC')
            ->getQuery()
            ->getArrayResult();

        $revenues = [];
        foreach ($rows as $row) {
            $revenues[$row['name']] = ['color' => $row['color'], 'revenueInCents' => (int) $row['revenueInCents']];
        }

        return $revenues;
    }
}

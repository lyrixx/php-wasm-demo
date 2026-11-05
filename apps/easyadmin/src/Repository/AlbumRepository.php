<?php

namespace App\Repository;

use App\Entity\Album;
use App\Entity\OrderStatus;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Album>
 */
final class AlbumRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Album::class);
    }

    /**
     * @return list<array{album: Album, copies: int}>
     */
    public function findBestSellers(\DateTimeImmutable $since, int $limit): array
    {
        $rows = $this->createQueryBuilder('album')
            ->addSelect('artist, SUM(line.quantity) AS copies')
            ->join('album.artist', 'artist')
            ->join('App\Entity\OrderLine', 'line', 'WITH', 'line.album = album')
            ->join('line.order', 'o')
            ->where('o.placedAt >= :since AND o.status != :cancelled')
            ->setParameter('since', $since)
            ->setParameter('cancelled', OrderStatus::Cancelled)
            ->groupBy('album.id')
            ->orderBy('copies', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        return array_map(static fn (array $row): array => ['album' => $row[0], 'copies' => (int) $row['copies']], $rows);
    }

    /**
     * @return list<Album>
     */
    public function findLowOnStock(int $limit): array
    {
        return $this->createQueryBuilder('album')
            ->addSelect('artist')
            ->join('album.artist', 'artist')
            ->where('album.stock <= :lowStock')
            ->setParameter('lowStock', Album::LOW_STOCK)
            ->orderBy('album.stock', 'ASC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();
    }

    public function countLowOnStock(): int
    {
        return (int) $this->createQueryBuilder('album')
            ->select('COUNT(album)')
            ->where('album.stock <= :lowStock')
            ->setParameter('lowStock', Album::LOW_STOCK)
            ->getQuery()
            ->getSingleScalarResult();
    }
}

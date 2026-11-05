<?php

namespace App\DataFixtures;

use App\Entity\Album;
use App\Entity\Artist;
use App\Entity\Customer;
use App\Entity\Format;
use App\Entity\Genre;
use App\Entity\Order;
use App\Entity\OrderLine;
use App\Entity\OrderStatus;
use App\Entity\Review;
use App\Entity\Track;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;

/**
 * A record shop that never existed, with its catalog and a few months of
 * sales. It runs in the browser, on the first visit: the dates are the ones of
 * the visitor, and the same seed gives the same shop to everyone.
 */
final class AppFixtures extends Fixture
{
    private const ALBUMS_PER_ARTIST = 3;
    private const ARTISTS = 32;
    private const COUNTRIES = ['BE', 'BR', 'CA', 'DE', 'ES', 'FR', 'GB', 'IT', 'JP', 'NL', 'PT', 'SE', 'US'];
    private const CUSTOMERS = 120;
    private const DAYS_OF_SALES = 120;
    private const GENRES = [
        'Ambient' => '#4a9e94',
        'Dub' => '#3f8f4f',
        'Electro' => '#7a5cc9',
        'Folk' => '#b8863b',
        'Funk' => '#d9663b',
        'Jazz' => '#3b6fb8',
        'Krautrock' => '#a83f6b',
        'Post-punk' => '#6e7382',
    ];
    private const NOUNS = ['Anchors', 'Antennas', 'Arcades', 'Bridges', 'Cassettes', 'Comets', 'Dynamos', 'Echoes', 'Embers', 'Engines', 'Ferries', 'Foxes', 'Gardens', 'Harbors', 'Horizons', 'Lanterns', 'Magnets', 'Meteors', 'Mirrors', 'Monsoons', 'Oscillators', 'Parasols', 'Pelicans', 'Satellites', 'Signals', 'Static', 'Tides', 'Trams', 'Valleys', 'Vapors'];
    private const ORDERS = 420;
    private const REVIEWS = 180;
    private const REVIEW_COMMENTS = [
        1 => ['Warped on arrival, and so is the second side.', 'I wanted to like it. My cat left the room.'],
        2 => ['One good track, and it is the last one.', 'The pressing is fine, the songs less so.'],
        3 => ['Solid, without surprises.', 'A nice sleeve, a decent record.', 'Grows on you, slowly.'],
        4 => ['Great pressing, dead silent between the tracks.', 'My neighbors asked what it was. Twice.', 'The B side is the real album.'],
        5 => ['On repeat since it arrived.', 'The bass line of the third track changed my week.', 'A masterpiece, and the sleeve smells great.', 'Bought a second copy, in case.'],
    ];
    private const SEED = 1995;
    private const WORDS = ['Amber', 'Atomic', 'Blue', 'Burning', 'Concrete', 'Copper', 'Crystal', 'Distant', 'Electric', 'Empty', 'Floating', 'Forgotten', 'Golden', 'Hollow', 'Late', 'Lunar', 'Midnight', 'Neon', 'Northern', 'Paper', 'Quiet', 'Rusty', 'Silent', 'Slow', 'Solar', 'Velvet', 'Violet', 'Wandering', 'Wild', 'Winter'];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create();
        $faker->seed(self::SEED);

        $genres = $this->genres($manager);
        $albums = $this->albums($manager, $faker, $genres);
        $customers = $this->customers($manager, $faker);
        $this->orders($manager, $faker, $albums, $customers);
        $this->reviews($manager, $faker, $albums, $customers);

        $manager->flush();
    }

    /**
     * @return list<Genre>
     */
    private function genres(ObjectManager $manager): array
    {
        $genres = [];
        foreach (self::GENRES as $name => $color) {
            $genre = new Genre();
            $genre->setName($name);
            $genre->setColor($color);
            $manager->persist($genre);
            $genres[] = $genre;
        }

        return $genres;
    }

    /**
     * @param list<Genre> $genres
     *
     * @return list<Album>
     */
    private function albums(ObjectManager $manager, Generator $faker, array $genres): array
    {
        $albums = [];
        for ($i = 0; $i < self::ARTISTS; ++$i) {
            $artist = new Artist();
            $artist->setName(\sprintf('The %s %s', self::WORDS[$i % \count(self::WORDS)], $faker->randomElement(self::NOUNS)));
            $artist->setCountry($faker->randomElement(self::COUNTRIES));
            $artist->setFormedIn($faker->numberBetween(1968, 2019));
            $artist->setBiography($faker->paragraph(4));
            $manager->persist($artist);

            $genre = $faker->randomElement($genres);
            for ($j = 0; $j < self::ALBUMS_PER_ARTIST; ++$j) {
                $album = $this->album($faker, $artist, $faker->boolean(80) ? $genre : $faker->randomElement($genres));
                $manager->persist($album);
                $albums[] = $album;
            }
        }

        return $albums;
    }

    private function album(Generator $faker, Artist $artist, Genre $genre): Album
    {
        $album = new Album();
        $album->setTitle(\sprintf('%s %s', $faker->randomElement(self::WORDS), $faker->randomElement(self::NOUNS)));
        $album->setArtist($artist);
        $album->setGenre($genre);
        $album->setReleaseYear($faker->numberBetween($artist->getFormedIn(), 2026));
        $album->setFormat($faker->randomElement([Format::Lp, Format::Lp, Format::Lp, Format::DoubleLp, Format::Ep, Format::Single, Format::BoxSet]));
        $album->setPriceInCents($faker->numberBetween(14, 42) * 100 + 90);
        $album->setStock($faker->boolean(18) ? $faker->numberBetween(0, 5) : $faker->numberBetween(6, 60));
        $album->setFeatured($faker->boolean(15));
        $album->setDescription(\sprintf('<p>%s</p>', $faker->paragraph(3)));

        for ($position = 1, $tracks = $faker->numberBetween(4, 9); $position <= $tracks; ++$position) {
            $track = new Track();
            $track->setPosition($position);
            $track->setTitle(\sprintf('%s %s', $faker->randomElement(self::WORDS), $faker->randomElement(self::NOUNS)));
            $track->setDurationInSeconds($faker->numberBetween(120, 480));
            $album->addTrack($track);
        }

        return $album;
    }

    /**
     * @return list<Customer>
     */
    private function customers(ObjectManager $manager, Generator $faker): array
    {
        $customers = [];
        for ($i = 0; $i < self::CUSTOMERS; ++$i) {
            $customer = new Customer();
            $customer->setName($faker->name());
            $customer->setEmail($faker->safeEmail());
            $customer->setCountry($faker->randomElement(self::COUNTRIES));
            $customer->setVip($faker->boolean(12));
            $customer->setJoinedAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-3 years', '-4 months')));
            $manager->persist($customer);
            $customers[] = $customer;
        }

        return $customers;
    }

    /**
     * @param list<Album>    $albums
     * @param list<Customer> $customers
     */
    private function orders(ObjectManager $manager, Generator $faker, array $albums, array $customers): void
    {
        $now = new \DateTimeImmutable();
        for ($i = 0; $i < self::ORDERS; ++$i) {
            // A shop that does better and better: the square root leans towards the recent days
            $ageInDays = (int) (self::DAYS_OF_SALES * (1 - sqrt($faker->randomFloat(4, 0, 1))));
            $order = new Order();
            $order->setReference(\sprintf('WE-%05d', 10000 + $i));
            $order->setCustomer($faker->randomElement($customers));
            $order->setPlacedAt($now->modify(\sprintf('-%d days -%d minutes', $ageInDays, $faker->numberBetween(0, 1439))));
            $order->setStatus($this->status($faker, $ageInDays));

            foreach ($faker->randomElements($albums, $faker->numberBetween(1, 3)) as $album) {
                $line = new OrderLine();
                $line->setAlbum($album);
                $line->setQuantity($faker->boolean(85) ? 1 : 2);
                $line->setUnitPriceInCents($album->getPriceInCents());
                $order->addLine($line);
            }

            $manager->persist($order);
        }
    }

    private function status(Generator $faker, int $ageInDays): OrderStatus
    {
        if ($faker->boolean(4)) {
            return OrderStatus::Cancelled;
        }

        return match (true) {
            $ageInDays < 2 => $faker->randomElement([OrderStatus::Pending, OrderStatus::Paid]),
            $ageInDays < 6 => $faker->randomElement([OrderStatus::Paid, OrderStatus::Shipped]),
            $ageInDays < 10 => OrderStatus::Shipped,
            default => OrderStatus::Delivered,
        };
    }

    /**
     * @param list<Album>    $albums
     * @param list<Customer> $customers
     */
    private function reviews(ObjectManager $manager, Generator $faker, array $albums, array $customers): void
    {
        for ($i = 0; $i < self::REVIEWS; ++$i) {
            $rating = $faker->randomElement([1, 2, 3, 3, 4, 4, 4, 5, 5, 5]);
            $review = new Review();
            $review->setAlbum($faker->randomElement($albums));
            $review->setCustomer($faker->randomElement($customers));
            $review->setRating($rating);
            $review->setComment($faker->randomElement(self::REVIEW_COMMENTS[$rating]));
            $review->setApproved($faker->boolean(85));
            $review->setWrittenAt(\DateTimeImmutable::createFromMutable($faker->dateTimeBetween('-100 days')));
            $manager->persist($review);
        }
    }
}

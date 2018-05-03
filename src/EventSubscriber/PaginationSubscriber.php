<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\SearchResult;
use App\Entity\Virtual\CustomSearchResult;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Event\AfterEvent;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginationSubscriber implements EventSubscriberInterface
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function after(AfterEvent $event)
    {
        $items = $event->getPaginationView()->getItems();

        if (empty($items)) {
            return;
        }

        $firstItem = reset($items);

        $qb = $this->em->createQueryBuilder();

        $searchResults = $qb
            ->select('sr')
            ->from(SearchResult::class, 'sr')
            ->where($qb->expr()->andX(
                $qb->expr()->eq('sr.request', '?0'),
                $qb->expr()->in('sr.hotel', '?1')
            ))
            ->setParameters([
                $firstItem->getRequest()->getId(),
                array_map(function(CustomSearchResult $csr){ return $csr->getHotel()->getId(); }, $items)
            ])
            ->orderBy('sr.price.amount')
            ->getQuery()
            ->getResult()
        ;

        foreach ($items as $item) {
            $set = [];
            /* @var $item CustomSearchResult */
            foreach ($searchResults as $searchResult) {
                /* @var SearchResult $searchResult */
                if ($searchResult->getHotel()->getId() === $item->getHotel()->getId()) {
                    $set[] = $searchResult;
                }
            }
            $item->setSearchResults($set);
        }
    }


    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.after' => array('after', 0),
        );
    }
}

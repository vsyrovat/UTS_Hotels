<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Entity\SearchResult;
use App\Entity\Virtual\CustomSearchResult;
use App\Service\SearchResultBuilder;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\Event\AfterEvent;
use Knp\Component\Pager\Event\ItemsEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginationSubscriber implements EventSubscriberInterface
{
    private $builder;

    public function __construct(SearchResultBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function after(AfterEvent $event)
    {
        $this->builder->applySearchResults(
            $event->getPaginationView()->getItems()
        );
    }


    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.after' => array('after', 0),
        );
    }
}

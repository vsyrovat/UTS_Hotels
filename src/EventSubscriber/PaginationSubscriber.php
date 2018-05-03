<?php declare(strict_types=1);

namespace App\EventSubscriber;

use App\Service\SearchResultBuilder;
use Knp\Component\Pager\Event\AfterEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PaginationSubscriber implements EventSubscriberInterface
{
    private $builder;

    public function __construct(SearchResultBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function after(AfterEvent $event): void
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

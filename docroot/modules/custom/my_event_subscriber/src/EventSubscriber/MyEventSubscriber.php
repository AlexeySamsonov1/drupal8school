<?php


namespace Drupal\my_event_subscriber\EventSubscriber;


use Drupal\Core\Routing\RouteBuildEvent;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MyEventSubscriber implements EventSubscriberInterface {
  /**
   * Code that should be triggered on event specified.
   */
  public function onRespond(FilterResponseEvent $event) {
    // Add extra HTTP headers.
    $response = $event->getResponse();
    $response->headers->set('X-Custom-Header', 'MyValue');
  }

  public function titleAlter(RouteBuildEvent $event) {
    $a=$event->getRouteCollection();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::RESPONSE][] = ['onRespond'];
    $events[RoutingEvents::ALTER][] = ['titleAlter'];

    return $events;
  }
}

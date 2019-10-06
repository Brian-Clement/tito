<?php

namespace Drupal\tito;

use Drupal\tito\Client;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class EventService.
 */
class EventService {

  /**
   * Drupal\tito\Client definition.
   *
   * @var \Drupal\tito\Client
   */
  protected $client;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new Event object.
   */
  public function __construct(Client $client, LoggerChannelFactoryInterface $logger_factory) {
    $this->client = $client;
    $this->loggerFactory = $logger_factory;
  }

  /**
   * Request details about an event.
   *
   * @param string $account
   *   Account
   *
   * @param string $event
   *   Event
   *
   * @return void
   */
  public function eventRequest($account, $event) {
    // Construct the URI.
    $uri = $account . '/' . $event;

    // Perform the request.
    $response = $this->client->request('GET', $uri);

    if ($response) {
      return $response;
    }

    return FALSE;

  }

}

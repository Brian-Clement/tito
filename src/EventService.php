<?php

namespace Drupal\tito;

use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\tito\Client;

/**
 * Fetches event details.
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
   *   The Tito account.
   * @param string $event
   *   The Tito event.
   *
   * @return array<mixed>|bool
   *   The API response, or FALSE.
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

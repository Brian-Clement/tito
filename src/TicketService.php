<?php

namespace Drupal\tito;

use Drupal\tito\Client;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;

/**
 * Class TicketService.
 */
class TicketService {

  /**
   * Drupal\tito\Client definition.
   *
   * @var \Drupal\tito\Client
   */
  protected $titoClient;

  /**
   * Drupal\Core\Logger\LoggerChannelFactoryInterface definition.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Constructs a new TicketService object.
   */
  public function __construct(Client $tito_client, LoggerChannelFactoryInterface $logger_factory) {
    $this->titoClient = $tito_client;
    $this->loggerFactory = $logger_factory;
  }

  public function getTickets($account, $event) {
    // Construct the URI.
    $uri = $account . '/' . $event . '/tickets';

    // Perform the request.
    $response = $this->titoClient->request('GET', $uri);

    if ($response) {
      return $response;
    }

    return FALSE;
  }

}

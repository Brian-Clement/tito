<?php

namespace Drupal\tito;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\Core\Url;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;

/**
 * The API Client.
 *
 * @package Drupal\tito
 */
class Client {

  use StringTranslationTrait;

  /**
   * A configuration instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $config;

  /**
   * The API base URI.
   *
   * @var string
   */
  protected $baseUri;

  /**
   * The API token.
   *
   * @var string
   */
  protected $token;

  /**
   * The API token.
   *
   * @var \GuzzleHttp\ClientInterface
   */
  protected $guzzleClient;

  /**
   * Logger Factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $loggerFactory;

  /**
   * Cache Backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $cacheBackend;

  /**
   * The Messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * Constructs the Client.
   *
   * @param \Drupal\Core\Config\ConfigFactory $config_factory
   *   The config factory.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   *   The cache backend.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   *   The logger factory.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The translation service.
   */
  public function __construct(
    ConfigFactory $config_factory,
    CacheBackendInterface $cacheBackend,
    LoggerChannelFactoryInterface $loggerFactory,
    MessengerInterface $messenger,
    TranslationInterface $string_translation,
  ) {

    // Fetch Tito settings.
    $this->config = $config_factory->get('tito.settings');
    $this->baseUri = $this->config->get('tito_api_url');
    $this->token = $this->config->get('tito_api_token');

    $this->guzzleClient = new GuzzleClient([
      'base_uri' => $this->baseUri,
    ]);

    $this->loggerFactory = $loggerFactory;
    $this->cacheBackend = $cacheBackend;
    $this->messenger = $messenger;
    $this->stringTranslation = $string_translation;
  }

  /**
   * Construct a Guzzle request.
   *
   * @param string $requestMethod
   *   The HTTP request method.
   * @param string $uri
   *   The request URI.
   * @param string $args
   *   Request arguments.
   * @param bool $cacheable
   *   Whether or not to cache the response.
   *
   * @return array<mixed>|bool
   *   False or array.
   */
  public function request($requestMethod, string $uri = '', string $args = '', $cacheable = TRUE) {
    // Minimally use the base URI.
    $url = $this->baseUri;

    // Include the endpoint.
    if ($uri) {
      $url = $url . $uri;
    }

    // Perform the request.
    $response = $this->doRequest($url, $args, $requestMethod);

    // Return result from source if found.
    if ($response) {
      return $response;
    }

  }

  /**
   * Perform Guzzle request.
   *
   * @param string $url
   *   Url.
   * @param mixed $parameters
   *   Parameters.
   * @param string $requestMethod
   *   Request method.
   *
   * @return bool|array<mixed>
   *   False or array.
   */
  private function doRequest($url, string $parameters = NULL, $requestMethod = 'GET') {
    if ($this->token == "") {
      $errorMessage = $this->t('Tito API Access Token is not set. It can be set on the <a href="@config_page">configuration page</a>.',
        ['@config_page' => Url::fromRoute('tito.settings')->toString()]
      );

      $this->messenger->addMessage($errorMessage, 'error');
      $this->loggerFactory->get('tito')->error("@message", ['@message' => $errorMessage]);
      return FALSE;
    }
    try {
      $options = $this->buildOptions($parameters);

      $response = $this->guzzleClient->request(
        $requestMethod,
        $url,
        $options
      );

      if ($response->getStatusCode() == 200) {
        $contents = $response->getBody()->getContents();

        $json = Json::decode($contents);

        return $json;
      }
    }
    catch (GuzzleException $e) {
      $this->loggerFactory->get('tito')->error("@message", ['@message' => $e->getMessage()]);
      return FALSE;
    }
    return FALSE;
  }

  /**
   * Construct options for Guzzle request.
   *
   * @param string $parameters
   *   Query parameters for the request.
   *
   * @return array<mixed>
   *   The array of options data.
   */
  protected function buildOptions(string $parameters) {
    $options = [];
    $options['headers'] = [
      'Accept' => 'application/json',
      'Authorization' => 'Token token=' . $this->token,
    ];
    $options['query'] = $parameters;

    return $options;
  }

}

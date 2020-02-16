<?php

namespace Drupal\tito;

use Drupal\Core\Config\ConfigFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use GuzzleHttp\Client as GuzzleClient;
use Drupal\Core\Url;
use Drupal\Core\Messenger\MessengerInterface;
use GuzzleHttp\Exception\GuzzleException;
use Drupal\Component\Serialization\Json;

/**
 * Class Client.
 *
 * @package Drupal\tito
 */
class Client {

  use StringTranslationTrait;

  /**
   * A configuration instance.
   *
   * @var \Drupal\Core\Config\ConfigFactory;
   */
  protected $config;

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
   * @param \Drupal\Core\Cache\CacheBackendInterface $cacheBackend
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $loggerFactory
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   */
  public function __construct(ConfigFactory $config_factory,
                              CacheBackendInterface $cacheBackend,
                              LoggerChannelFactoryInterface $loggerFactory,
                              MessengerInterface $messenger,
                              TranslationInterface $string_translation) {

    // Fetch Tito settings.
    $this->config = $config_factory->get('tito.settings');
    $this->base_uri = $this->config->get('tito_api_url');
    $this->token = $this->config->get('tito_api_token');

    $this->guzzleClient = new GuzzleClient([
      'base_uri' => $this->base_uri,
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
   * @param string $uri
   * @param string $args
   * @param bool $cacheable
   *
   * @return array|bool
   */
  public function request($requestMethod, string $uri = '', string $args = '', $cacheable = TRUE) {
    // Minimally use the base URI
    $url = $this->base_uri;

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
   * @return bool|array
   *   False or array.
   */
  private function doRequest($url, string $parameters = NULL, $requestMethod = 'GET') {
    if ($this->token == "") {
      $errorMessage = $this->t('Tito API Access Token is not set. It can be set on the <a href=":config_page">configuration page</a>.',
        [':config_page' => Url::fromRoute('tito.settings')]
      );

      $this->messenger->addMessage($errorMessage, 'error');
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
   *
   * @return array.
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

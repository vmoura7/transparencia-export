<?php

namespace Drupal\transparencia_export\Controller;

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Drupal\transparencia_export\Factory\ExportPresenterFactory;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use GuzzleHttp\Client;

class ExportController extends ControllerBase
{
  protected $renderer;
  protected $session;
  protected $exportPresenterFactory;
  protected $loggerFactory;
  protected $requestStack;
  protected $httpClient;

  public function __construct(
    RendererInterface $renderer,
    SessionInterface $session,
    ExportPresenterFactory $exportPresenterFactory,
    LoggerChannelFactoryInterface $loggerFactory,
    RequestStack $requestStack,
    Client $httpClient
  ) {
    $this->renderer = $renderer;
    $this->session = $session;
    $this->exportPresenterFactory = $exportPresenterFactory;
    $this->loggerFactory = $loggerFactory;
    $this->requestStack = $requestStack;
    $this->httpClient = $httpClient;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('renderer'),
      $container->get('session'),
      $container->get('transparencia_export.export_presenter_factory'),
      $container->get('logger.factory'),
      $container->get('request_stack'),
      $container->get('http_client')
    );
  }

  public function export($format)
  {
    try {
      $html = $this->getCurrentPageHtml();

      $presenter = $this->exportPresenterFactory->create($format);

      $request = $this->requestStack->getCurrentRequest();
      $currentUrl = $request->headers->get('referer') ?? $request->getSchemeAndHttpHost();

      $formattedContent = $presenter->format($html, $currentUrl);

      $response = new Response($formattedContent);
      foreach ($presenter->getHeaders() as $header => $value) {
        $response->headers->set($header, $value);
      }

      return $response;
    } catch (\Exception $e) {
      $this->loggerFactory->get('transparencia_export')
        ->error("Erro na exportação: {$e->getMessage()}");

      return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
  }

  protected function getCurrentPageHtml(): string
  {
    try {
      $request = $this->requestStack->getCurrentRequest();

      $destination = $request->query->get('destination');
      $referer = $request->headers->get('referer');

      $current_url = $destination ?
        $request->getSchemeAndHttpHost() . '/' . ltrim($destination, '/') :
        $referer;

      // $this->loggerFactory->get('transparencia_export')->info('URL determinada para captura: @url', ['@url' => $current_url]);

      if (!$current_url) {
        throw new \Exception('Não foi possível determinar a URL da página para capturar o HTML.');
      }

      $response = $this->httpClient->get($current_url, ['verify' => false]);

      if ($response->getStatusCode() === 200) {
        return $response->getBody()->getContents();
      }

      throw new \Exception("Não foi possível capturar o HTML da página: $current_url");
    } catch (\Exception $e) {
      throw new \Exception('Erro ao capturar HTML: ' . $e->getMessage());
    }
  }
}

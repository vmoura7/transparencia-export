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
use Drupal\Core\Url;
use Symfony\Component\DomCrawler\Crawler;

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
      // Obter HTML da página atual
      $html = $this->getCurrentPageHtml();

      // Usar factory para criar presenter correto
      $presenter = $this->exportPresenterFactory->create($format);

      // Formatar HTML
      $formattedContent = $presenter->format($html);

      // Criar resposta com cabeçalhos e conteúdo
      $response = new Response($formattedContent);
      foreach ($presenter->getHeaders() as $header => $value) {
        $response->headers->set($header, $value);
      }

      return $response;
    } catch (\Exception $e) {
      // Logar erro
      $this->loggerFactory->get('transparencia_export')
        ->error("Erro na exportação: {$e->getMessage()}");

      return new Response($e->getMessage(), Response::HTTP_BAD_REQUEST);
    }
  }

  protected function getCurrentPageHtml(): string
  {
    try {
      // Recuperar a requisição atual
      $request = $this->requestStack->getCurrentRequest();

      // Tentar obter a URL a partir do parâmetro "destination" ou do cabeçalho "Referer"
      $destination = $request->query->get('destination');
      $referer = $request->headers->get('referer');

      // Determinar a URL final
      $current_url = $destination ?
        $request->getSchemeAndHttpHost() . '/' . ltrim($destination, '/') :
        $referer;

      // Logar a URL que será usada
      $this->loggerFactory->get('transparencia_export')->info('URL determinada para captura: @url', ['@url' => $current_url]);

      if (!$current_url) {
        throw new \Exception('Não foi possível determinar a URL da página para capturar o HTML.');
      }

      // Fazer a requisição usando Guzzle
      $response = $this->httpClient->get($current_url, ['verify' => false]);
      //REMOVER VERIFY FALSE QUANDO FOR PARA PRODUÇÃO


      if ($response->getStatusCode() === 200) {
        return $response->getBody()->getContents();
      }

      // Caso a resposta não seja bem-sucedida
      throw new \Exception("Não foi possível capturar o HTML da página: $current_url");
    } catch (\Exception $e) {
      throw new \Exception('Erro ao capturar HTML: ' . $e->getMessage());
    }
  }
}

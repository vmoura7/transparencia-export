<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\CssSelector\CssSelectorConverter;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  /**
   * Formata o HTML renderizado para exportação.
   *
   * @param string $html
   *   HTML renderizado.
   *
   * @return string
   *   Dados formatados.
   */
  public function format($html): string
  {
    $cleanHtml = $this->cleanHtml($html);
    return $this->convertToFormat($cleanHtml);
  }

  /**
   * Limpa o HTML removendo elementos desnecessários.
   *
   * @param string $html
   *   HTML a ser limpo.
   *
   * @return string
   *   HTML limpo.
   */
  protected function cleanHtml(string $html): string
  {
    $crawler = new \Symfony\Component\DomCrawler\Crawler($html);

    // Remove elementos desnecessários com XPath.
    $crawler->filterXpath('//header|//footer|//script|//style')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    // Usar seletores CSS para capturar conteúdo específico.
    $converter = new CssSelectorConverter();
    $selector = $converter->toXPath('.view-content'); // Exemplo de classe CSS.

    $viewContent = $crawler->filterXpath($selector);

    if ($viewContent->count() > 0) {
      $textContent = $viewContent->text(); // Extrair o conteúdo específico.
    } else {
      // Padrão: extrair texto do body.
      $textContent = $crawler->filterXpath('//body')->text();
    }

    // Limpar espaços extras.
    $cleanedContent = preg_replace('/\s+/', ' ', $textContent);
    return trim($cleanedContent);
  }


  /**
   * Converte o HTML para o formato desejado (implementado em subclasses).
   *
   * @param string $html
   *   HTML a ser convertido.
   *
   * @return string
   *   HTML no formato desejado.
   */
  abstract protected function convertToFormat(string $html): string;
}

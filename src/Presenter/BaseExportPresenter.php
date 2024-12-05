<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  public function format($data, ?string $currentUrl = null): string
  {
    $cleanHtml = $this->cleanHtml($data);

    $cleanHtml['url'] = $currentUrl ?? 'URL não definida';
    $cleanHtml['data'] = date('d-m-Y H:i:s');

    return $this->convertToFormat($cleanHtml);
  }

  protected function cleanHtml(string $html): array
  {
    $crawler = new Crawler($html);

    // Remover elementos indesejados.
    $crawler->filterXpath('//header|//footer|//script|//style|//comment()')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    // Extrair título.
    $titleNode = $crawler->filter('.page-title, .title');
    $title = $titleNode->count() ? trim($titleNode->text()) : 'Título não encontrado';

    // Extrair tabelas.
    $tables = [];
    $crawler->filter('table')->each(function ($tableNode) use (&$tables) {
      $table = [];
      $tableNode->filter('tr')->each(function ($rowNode) use (&$table) {
        $row = [];
        $rowNode->filter('th, td')->each(function ($cellNode) use (&$row) {
          $row[] = trim($cellNode->text());
        });
        $table[] = $row;
      });
      $tables[] = $table;
    });

    // Extrair conteúdo principal sem tabelas.
    $contentNode = $crawler->filter('.node__content, .gavias-builder--content, .views-element-container, .view-page');
    $content = $contentNode->count() ? trim($contentNode->text()) : 'Conteúdo não encontrado';

    // Remover conteúdo da tabela do texto principal.
    if (!empty($tables)) {
      foreach ($tables as $table) {
        foreach ($table as $row) {
          foreach ($row as $cell) {
            $content = str_replace($cell, '', $content);
          }
        }
      }
      $content = preg_replace('/\s+/', ' ', $content); // Limpar espaços extras.
    }

    return [
      'titulo' => $title,
      'texto' => trim($content),
      'tabelas' => $tables,
    ];
  }

  abstract protected function convertToFormat(array $data): string;
}

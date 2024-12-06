<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  protected function filterEmptyFields(array $data): array
  {
    return array_filter($data, function ($value) {
      return !($value === null || $value === '' || (is_array($value) && empty($value)));
    });
  }

  public function format($data, ?string $currentUrl = null): string
  {
    $cleanHtml = $this->cleanHtml($data);

    $cleanHtml['url'] = $currentUrl ?? 'URL não definida';
    $cleanHtml['data'] = date('d-m-Y H:i:s');

    $cleanHtml = $this->filterEmptyFields($cleanHtml);

    return $this->convertToFormat($cleanHtml);
  }


  protected function cleanHtml(string $html): array
  {
    $crawler = new Crawler($html);

    $crawler->filterXpath('//header|//footer|//script|//style|//comment()')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    $filteredHtml = $crawler->html(); // Captura o HTML filtrado
    \Drupal::logger('transparencia_export')->debug('HTML totalmente filtrado: <pre>@html</pre>', ['@html' => $filteredHtml]);

    $crawler->filter('.pager, .pagination, .views-pagination')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    $titleNode = $crawler->filter('.page-title, .title');
    $title = $titleNode->count() ? trim($titleNode->text()) : 'Título não encontrado';

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

    $contentNode = $crawler->filter('.node__content, .gavias-builder--content, .views-element-container, .view-page');
    $content = $contentNode->count() ? trim($contentNode->text()) : 'Conteúdo não encontrado';

    if (!empty($tables)) {
      foreach ($tables as $table) {
        foreach ($table as $row) {
          foreach ($row as $cell) {
            $content = str_replace($cell, '', $content);
          }
        }
      }
      $content = preg_replace('/\s+/', ' ', $content);
    }

    return [
      'titulo' => $title,
      'texto' => trim($content),
      'tabelas' => $tables,
    ];
  }

  abstract protected function convertToFormat(array $data): string;
}

<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  protected array $config;

  public function __construct(array $config = [])
  {
    // Configurações padrão podem ser sobrescritas pelas passadas no construtor.
    $this->config = array_merge([
      'remove_elements' => '//header|//footer|//script|//style|//comment()',
      'block_selectors' => '#block-gavias-nonid-quicksideabout, .block-block-content',
    ], $config);
  }

  protected function filterEmptyFields(array $data): array
  {
    return array_filter($data, function ($value) {
      return !($value === null || $value === '' || (is_array($value) && empty($value)));
    });
  }

  public function format($data, ?string $currentUrl = null): string
  {
    $cleanHtml = $this->processHtml($data);

    $cleanHtml['url'] = $currentUrl ?? 'URL não definida';
    $cleanHtml['data'] = date('d-m-Y H:i:s');

    return $this->convertToFormat($this->filterEmptyFields($cleanHtml));
  }

  protected function processHtml(string $html): array
  {
    $crawler = new Crawler($html);
    $this->removeUnwantedElements($crawler);

    $title = $this->extractTitle($crawler);
    $tables = $this->extractTables($crawler);
    $subtitlesAndContents = $this->extractSubtitlesAndContents($crawler, $tables['texts']);
    $mainContent = $this->extractMainContent($crawler, $tables['texts'], !empty($subtitlesAndContents));

    return [
      'titulo' => $title,
      'texto' => $mainContent,
      'subtitulos' => $subtitlesAndContents,
      'tabelas' => $tables['data'] ?? null,
    ];
  }

  protected function removeUnwantedElements(Crawler $crawler): void
  {
    // Remover elementos genéricos (configuráveis).
    $crawler->filterXpath($this->config['remove_elements'])->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    // Remover blocos específicos (configuráveis).
    $crawler->filter($this->config['block_selectors'])->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    \Drupal::logger('transparencia_export')->debug('HTML após remoção de elementos indesejados.');
  }

  protected function extractTitle(Crawler $crawler): string
  {
    $titleNode = $crawler->filter('.page-title, .title');
    return $titleNode->count() ? trim($titleNode->text()) : 'Título não encontrado';
  }

  protected function extractTables(Crawler $crawler): array
  {
    $tables = [];
    $tableTexts = [];

    $crawler->filter('table')->each(function ($tableNode) use (&$tables, &$tableTexts) {
      $table = [];
      $tableNode->filter('tr')->each(function ($rowNode) use (&$table, &$tableTexts) {
        $row = [];
        $rowNode->filter('th, td')->each(function ($cellNode) use (&$row, &$tableTexts) {
          $cellText = trim($cellNode->text());
          $row[] = $cellText;
          $tableTexts[] = $cellText; // Coletar todos os textos da tabela
        });
        $table[] = $row;
      });
      $tables[] = $table;
    });

    return ['data' => $tables, 'texts' => $tableTexts];
  }

  protected function extractSubtitlesAndContents(Crawler $crawler, array $tableTexts): array
  {
    $subtitlesAndContents = [];
    $currentSubtitle = null;

    $crawler->filter('h1, h2, h3, h4, h5, h6, p')->each(function (Crawler $node) use (&$subtitlesAndContents, &$currentSubtitle, $tableTexts) {
      $tagName = $node->nodeName();
      $text = trim($node->text());

      if (in_array($text, $tableTexts, true)) {
        return; // Ignore texts already present in tables.
      }

      if (preg_match('/^h[1-6]$/', $tagName)) {
        $currentSubtitle = $text;
        $subtitlesAndContents[] = ['subtitulo' => $currentSubtitle, 'conteudo' => ''];
      } elseif ($currentSubtitle && $tagName === 'p') {
        $index = count($subtitlesAndContents) - 1;
        $subtitlesAndContents[$index]['conteudo'] .= ($subtitlesAndContents[$index]['conteudo'] ? ' ' : '') . $text;
      }
    });

    return array_filter($subtitlesAndContents, fn($item) => !empty(trim($item['conteudo'])));
  }

  protected function extractMainContent(Crawler $crawler, array $tableTexts, bool $hasSubtitles): ?string
  {
    $contentNode = $crawler->filter('.node__content, .gavias-builder--content, .views-element-container, .view-page');
    $mainContent = $contentNode->count() ? trim($contentNode->text()) : null;

    if ($mainContent) {
      foreach ($tableTexts as $tableText) {
        $mainContent = str_replace($tableText, '', $mainContent);
      }
      $mainContent = preg_replace('/\s+/', ' ', $mainContent);
    }

    return $hasSubtitles ? null : $mainContent; // Only include main content if there are no subtitles.
  }

  abstract protected function convertToFormat(array $data): string;
}

<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  protected array $config;

  public function __construct(array $config = [])
  {
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

    // $filteredHtml = $crawler->html();
    // \Drupal::logger('transparencia_export')->debug('HTML filtrado: {html}', ['html' => $filteredHtml]);

    $title = $this->extractTitle($crawler);
    $tables = $this->extractTables($crawler);
    $subtitlesAndContents = $this->extractSubtitlesAndContents($crawler, $tables['texts']);
    $mainContent = $this->extractMainContent($crawler, $tables['texts'], !empty($subtitlesAndContents));

    return [
      'titulo' => $title,
      'secoes' => $this->structureSections($subtitlesAndContents),
      'tabelas' => $tables['data'] ?? null,
      'versao' => time(),
    ];
  }


  protected function structureSections(array $subtitlesAndContents): array
  {
    $sectionCounter = 1;
    $sections = array_map(function ($section) use (&$sectionCounter) {
      return [
        'id' => 'secao_' . $sectionCounter,
        'slug' => $this->createSlug($section['subtitulo']),
        'titulo' => $section['subtitulo'],
        'conteudo' => $section['conteudo']
      ];
    }, $subtitlesAndContents);

    return array_combine(
      array_map(fn($i) => $i, range(1, count($sections))),
      $sections
    );
  }

  protected function createSlug(string $text): string
  {
    $slug = iconv('UTF-8', 'ASCII//TRANSLIT', $text);
    $slug = preg_replace('/[^a-zA-Z0-9\s]/', '', $slug);
    return strtolower(str_replace(' ', '-', trim($slug)));
  }

  protected function extractSummary(Crawler $crawler): ?string
  {
    $paragraphs = $crawler->filter('p');
    $summary = '';
    $paragraphs->slice(0, 2)->each(function ($paragraph) use (&$summary) {
      $summary .= $paragraph->text() . ' ';
    });
    return trim($summary) ?: null;
  }

  protected function extractKeywords(Crawler $crawler): array
  {
    $text = $crawler->text();
    $words = preg_split('/\s+/', $text);
    $words = array_filter($words, function ($word) {
      return strlen($word) > 3;
    });
    return array_slice(array_unique($words), 0, 10);
  }

  protected function removeUnwantedElements(Crawler $crawler): void
  {
    $crawler->filterXpath($this->config['remove_elements'])->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    $crawler->filter($this->config['block_selectors'])->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    $crawler->filter('.pager, .pagination, .views-pagination')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });
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
          $tableTexts[] = $cellText;
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
        return;
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

    return $hasSubtitles ? null : $mainContent;
  }

  abstract protected function convertToFormat(array $data): string;
}

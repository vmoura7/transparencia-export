<?php

namespace Drupal\transparencia_export\Presenter;

use Symfony\Component\DomCrawler\Crawler;

abstract class BaseExportPresenter implements ExportPresenterInterface
{
  public function format($data, ?string $currentUrl = null): string
  {
    $cleanHtml = $this->cleanHtml($data);

    $cleanHtml['url'] = $currentUrl ?? 'URL não definida';
    $cleanHtml['exported_at'] = date('d-m-Y H:i:s');

    return $this->convertToFormat($cleanHtml);
  }

  protected function cleanHtml(string $html): array
  {
    $crawler = new Crawler($html);

    $crawler->filterXpath('//header|//footer|//script|//style|//comment()')->each(function ($node) {
      $node->getNode(0)->parentNode->removeChild($node->getNode(0));
    });

    $titleNode = $crawler->filter('.page-title, .title');
    $title = $titleNode->count() ? trim($titleNode->text()) : 'Título não encontrado';

    $contentNode = $crawler->filter('.node__content, .gavias-builder--content');
    $content = $contentNode->count() ? trim($contentNode->text()) : 'Conteúdo não encontrado';

    return [
      'titulo' => $title,
      'texto' => $content,
    ];
  }

  abstract protected function convertToFormat(array $data): string;
}

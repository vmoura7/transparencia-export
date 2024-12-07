<?php

namespace Drupal\transparencia_export\Presenter;

class XmlExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    $xml = new \SimpleXMLElement('<conteudo/>');

    $xml->addChild('titulo', htmlspecialchars($data['titulo'], ENT_XML1, 'UTF-8'));

    if (!empty($data['subtitulos'])) {
      $subtitlesXml = $xml->addChild('subtitulos');
      foreach ($data['subtitulos'] as $subtitle) {
        $subtitleXml = $subtitlesXml->addChild('subtitulo');
        if (isset($subtitle['subtitulo'])) {
          $subtitleXml->addChild('titulo', htmlspecialchars($subtitle['subtitulo'], ENT_XML1, 'UTF-8'));
        }
        if (isset($subtitle['conteudo'])) {
          $subtitleXml->addChild('conteudo', htmlspecialchars($subtitle['conteudo'], ENT_XML1, 'UTF-8'));
        }
      }
    }

    if (isset($data['texto']) && empty($data['subtitulos'])) {
      $xml->addChild('texto', htmlspecialchars($data['texto'], ENT_XML1, 'UTF-8'));
    }

    if (!empty($data['tabelas'])) {
      $tablesXml = $xml->addChild('tabelas');
      foreach ($data['tabelas'] as $table) {
        $tableXml = $tablesXml->addChild('tabela');
        foreach ($table as $row) {
          $rowXml = $tableXml->addChild('linha');
          foreach ($row as $cell) {
            $rowXml->addChild('celula', htmlspecialchars($cell, ENT_XML1, 'UTF-8'));
          }
        }
      }
    }

    if (!empty($data['url'])) {
      $xml->addChild('url', htmlspecialchars($data['url'], ENT_XML1, 'UTF-8'));
    }

    if (!empty($data['data'])) {
      $xml->addChild('data', htmlspecialchars($data['data'], ENT_XML1, 'UTF-8'));
    }

    return $xml->asXML();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/xml; charset=utf-8'];
  }
}

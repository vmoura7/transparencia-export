<?php

namespace Drupal\transparencia_export\Presenter;

class XmlExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    $xml = new \SimpleXMLElement('<conteudo/>');

    $xml->addChild('titulo', htmlspecialchars($data['titulo'], ENT_XML1, 'UTF-8'));

    if (!empty($data['secoes'])) {
      $secaosXml = $xml->addChild('secoes');
      foreach ($data['secoes'] as $secao) {
        $secaoXml = $secaosXml->addChild('secao');
        $secaoXml->addChild('id', htmlspecialchars($secao['id'], ENT_XML1, 'UTF-8'));
        $secaoXml->addChild('slug', htmlspecialchars($secao['slug'], ENT_XML1, 'UTF-8'));
        $secaoXml->addChild('titulo', htmlspecialchars($secao['titulo'], ENT_XML1, 'UTF-8'));
        $secaoXml->addChild('conteudo', htmlspecialchars($secao['conteudo'], ENT_XML1, 'UTF-8'));
      }
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

    if (!empty($data['versao'])) {
      $xml->addChild('versao', htmlspecialchars((string)$data['versao'], ENT_XML1, 'UTF-8'));
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

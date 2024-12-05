<?php

namespace Drupal\transparencia_export\Presenter;

class XmlExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    $xml = new \SimpleXMLElement('<conteudo/>');

    // Adicionar título.
    $xml->addChild('titulo', htmlspecialchars($data['titulo'], ENT_XML1, 'UTF-8'));

    // Adicionar texto, se não houver tabelas.
    if (isset($data['texto']) && empty($data['tabelas'])) {
      $xml->addChild('texto', htmlspecialchars($data['texto'], ENT_XML1, 'UTF-8'));
    }

    // Adicionar tabelas.
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

    // Adicionar URL.
    $xml->addChild('url', htmlspecialchars($data['url'], ENT_XML1, 'UTF-8'));

    // Adicionar data.
    $xml->addChild('data', htmlspecialchars($data['data'], ENT_XML1, 'UTF-8'));

    // Retornar o XML formatado.
    return $xml->asXML();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/xml; charset=utf-8'];
  }
}

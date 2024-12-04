<?php

namespace Drupal\transparencia_export\Presenter;

class XmlExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    // Criar a estrutura XML com os dados fornecidos.
    $xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><conteudo></conteudo>');

    // Adicionar os elementos ao XML.
    $xml->addChild('titulo', htmlspecialchars($data['titulo']));
    $xml->addChild('texto', htmlspecialchars($data['texto']));
    $xml->addChild('url', htmlspecialchars($data['url']));
    $xml->addChild('data', htmlspecialchars($data['data']));

    // Retornar a string XML.
    return $xml->asXML();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/xml'];
  }
}

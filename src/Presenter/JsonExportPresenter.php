<?php

namespace Drupal\transparencia_export\Presenter;

class JsonExportPresenter extends BaseExportPresenter
{
  /**
   * Retorna os cabeçalhos HTTP para a resposta JSON.
   */
  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/json; charset=utf-8'];
  }
  /**
   * Converte os dados para o formato JSON.
   */
  protected function convertToFormat($data): string
  {
    // Verificar se $data é um array. Se não for, converte-o para um array.
    if (!is_array($data)) {
      $data = [$data];
    }

    // Processar cada item do array de dados (se aplicável).
    $cleanedData = array_map(function ($item) {
      if (is_string($item)) {
        return htmlspecialchars($item, ENT_QUOTES, 'UTF-8'); // Evitar problemas de codificação.
      }
      return $item;
    }, $data);

    // Converter o array limpo para JSON formatado.
    return json_encode($cleanedData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  }



  /**
   * Retorna o tipo MIME para JSON.
   */
  public function getMimeType(): string
  {
    return 'application/json';
  }
}

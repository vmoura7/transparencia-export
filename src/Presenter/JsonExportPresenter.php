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
   * Converte os dados formatados para JSON.
   */
  protected function convertToFormat(array $data): string
  {
    // Remover o campo 'texto' se houver tabelas no resultado.
    if (!empty($data['tabelas'])) {
      unset($data['texto']);
    }

    // Realizar filtragem adicional caso necessário.
    $filteredData = $this->filterEmptyFields($data);

    // Converter para JSON formatado.
    return json_encode($filteredData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }

  /**
   * Retorna o tipo MIME para JSON.
   */
  public function getMimeType(): string
  {
    return 'application/json';
  }
}

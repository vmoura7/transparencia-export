<?php

namespace Drupal\transparencia_export\Presenter;

interface ExportPresenterInterface
{
  /**
   * Formata os dados para exportação.
   *
   * @param mixed $data
   *   Dados a serem exportados (string ou array).
   *
   * @return string
   *   Dados formatados no formato desejado.
   */
  public function format($data): string;

  /**
   * Retorna os cabeçalhos HTTP necessários para a exportação.
   *
   * @return array
   *   Cabeçalhos HTTP (e.g., Content-Type).
   */
  public function getHeaders(): array;
}

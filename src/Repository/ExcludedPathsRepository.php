<?php

namespace Drupal\transparencia_export\Repository;

use Drupal\Core\Database\Database;

/**
 * Classe repositório para gerenciar caminhos excluídos.
 */
class ExcludedPathsRepository
{
  /**
   * Obtém os caminhos excluídos do banco de dados.
   *
   * @return array
   *   Um array contendo os caminhos excluídos.
   */
  public function getExcludedPaths()
  {
    $query = Database::getConnection()->select('transparencia_export_excluded_paths', 'e')
      ->fields('e', ['path']); // Supondo que você tenha uma coluna 'path' na tabela.

    return $query->execute()->fetchCol(); // Retorna um array de valores.
  }

  /**
   * Adiciona um caminho excluído ao banco de dados.
   *
   * @param string $path
   *   O caminho a ser excluído.
   *
   * @return void
   */
  public function addExcludedPath($path)
  {
    Database::getConnection()->insert('transparencia_export_excluded_paths')
      ->fields(['path' => $path])
      ->execute();
  }

  /**
   * Remove um caminho excluído do banco de dados.
   *
   * @param string $path
   *   O caminho a ser removido.
   *
   * @return void
   */
  public function removeExcludedPath($path)
  {
    Database::getConnection()->delete('transparencia_export_excluded_paths')
      ->condition('path', $path)
      ->execute();
  }

  /**
   * Verifica se um caminho está excluído.
   *
   * @param string $path
   *   O caminho a ser verificado.
   *
   * @return bool
   *   TRUE se o caminho está excluído, FALSE caso contrário.
   */
  public function isPathExcluded($path)
  {
    $query = Database::getConnection()->select('transparencia_export_excluded_paths', 'e')
      ->fields('e', ['id'])
      ->condition('path', $path);

    $result = $query->execute()->fetchField();

    return !empty($result);
  }

  /**
   * Limpa todos os caminhos excluídos do banco de dados.
   *
   * @return void
   */
  public function clearExcludedPaths()
  {
    // Obtém a conexão padrão do banco de dados.
    $connection = Database::getConnection();

    // Remove todos os registros da tabela de caminhos excluídos.
    $connection->delete('transparencia_export_excluded_paths') // Substitua pelo nome real da sua tabela
      ->execute();
  }
}

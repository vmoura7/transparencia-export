<?php

namespace Drupal\transparencia_export\Decorator;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class ExportNodeDecorator
{
  /**
   * EntityTypeManager para buscar entidades do Drupal.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager)
  {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Adiciona a URL ao array de dados de um nó.
   *
   * @param array $data Dados do nó.
   * @return array Dados do nó com a URL adicionada.
   */
  public function addUrl(array $data): array
  {
    // Obtém o NID do nó.
    $nid = $data['nid'] ?? null;
    if (!$nid) {
      $data['url'] = '';
      return $data;
    }

    // Tenta obter o alias do nó.
    $pathAliasStorage = $this->entityTypeManager->getStorage('path_alias');
    $pathAlias = $pathAliasStorage->loadByProperties([
      'path' => "/node/{$nid}",
    ]);

    if (!empty($pathAlias)) {
      // Obtém o primeiro alias disponível.
      $alias = reset($pathAlias);
      $aliasPath = $alias->get('alias')->value ?? "/node/{$nid}";
    } else {
      // Usa o path padrão se não houver alias.
      $aliasPath = "/node/{$nid}";
    }

    // Monta a URL completa (base_url pode ser configurada via settings.php).
    $baseUrl = \Drupal::request()->getSchemeAndHttpHost();
    $data['url'] = $baseUrl . $aliasPath;

    return $data;
  }
}

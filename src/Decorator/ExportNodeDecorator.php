<?php

namespace Drupal\transparencia_export\Decorator;

use Drupal\Core\Entity\EntityTypeManagerInterface;

class ExportNodeDecorator
{
  protected EntityTypeManagerInterface $entityTypeManager;

  public function __construct(EntityTypeManagerInterface $entityTypeManager)
  {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function addUrl(array $data): array
  {
    $nid = $data['nid'] ?? null;
    if (!$nid) {
      $data['url'] = '';
      return $data;
    }

    $pathAliasStorage = $this->entityTypeManager->getStorage('path_alias');
    $pathAlias = $pathAliasStorage->loadByProperties([
      'path' => "/node/{$nid}",
    ]);

    if (!empty($pathAlias)) {
      $alias = reset($pathAlias);
      $aliasPath = $alias->get('alias')->value ?? "/node/{$nid}";
    } else {
      $aliasPath = "/node/{$nid}";
    }

    $baseUrl = \Drupal::request()->getSchemeAndHttpHost();
    $data['url'] = $baseUrl . $aliasPath;

    return $data;
  }
}

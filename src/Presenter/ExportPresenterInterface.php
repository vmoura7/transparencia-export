<?php

namespace Drupal\transparencia_export\Presenter;

interface ExportPresenterInterface
{
  public function format($data, ?string $currentUrl = null): string;

  public function getHeaders(): array;
}

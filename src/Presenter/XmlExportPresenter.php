<?php

namespace Drupal\transparencia_export\Presenter;

class XmlExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(string $html): string
  {
    return '<?xml version="1.0" encoding="UTF-8"?><content>' . htmlspecialchars($html) . '</content>';
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/xml'];
  }
}

<?php

namespace Drupal\transparencia_export\Presenter;

class JsonExportPresenter extends BaseExportPresenter
{
  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/json; charset=utf-8'];
  }


  protected function convertToFormat(array $data): string
  {
    if (isset($data['url'])) {
      $data['url'] = str_replace('\/', '/', $data['url']);
    }

    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
  }

  public function getMimeType(): string
  {
    return 'application/json';
  }
}

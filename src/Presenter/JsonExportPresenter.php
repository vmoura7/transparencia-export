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
    if (!empty($data['tabelas'])) {
      unset($data['texto']);
    }

    return json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
  }



  public function getMimeType(): string
  {
    return 'application/json';
  }
}

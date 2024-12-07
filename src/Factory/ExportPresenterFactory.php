<?php

namespace Drupal\transparencia_export\Factory;

use Drupal\transparencia_export\Presenter\JsonExportPresenter;
use Drupal\transparencia_export\Presenter\XmlExportPresenter;
use Drupal\transparencia_export\Presenter\PdfExportPresenter;
use Drupal\transparencia_export\Presenter\ExportPresenterInterface;

class ExportPresenterFactory
{
  /**
   * Cria um presenter baseado no formato.
   *
   * @param string $format
   *   Formato desejado (e.g., 'json', 'xml', 'pdf').
   *
   * @return ExportPresenterInterface
   *   Instância do presenter.
   *
   * @throws \Exception
   *   Exceção se o formato não for suportado.
   */
  public static function create(string $format): ExportPresenterInterface
  {
    $presenters = [
      'json' => JsonExportPresenter::class,
      'xml' => XmlExportPresenter::class,
      'pdf' => PdfExportPresenter::class,
    ];

    if (!isset($presenters[$format])) {
      throw new \Exception("Formato '$format' não suportado.");
    }

    $presenterClass = $presenters[$format];

    if (!is_subclass_of($presenterClass, ExportPresenterInterface::class)) {
      throw new \Exception("A classe '$presenterClass' não implementa ExportPresenterInterface.");
    }

    return new $presenterClass();
  }
}

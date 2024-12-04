<?php

namespace Drupal\transparencia_export\Presenter;

use Dompdf\Dompdf;

class PdfExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(string $html): string
  {
    $pdfGenerator = new Dompdf();
    $pdfGenerator->loadHtml($html);
    $pdfGenerator->render();
    return $pdfGenerator->output();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

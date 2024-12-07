<?php

namespace Drupal\transparencia_export\Presenter;

use Dompdf\Dompdf;

class PdfExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    $css = "
            <style>
                body {
                    font-family: Arial, sans-serif;
                    margin: 20px;
                    padding: 20px;
                }
                h1 {
                    color:
                    border-bottom: 2px solid
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                h2 {
                    color:
                    margin-top: 20px;
                }
                p {
                    font-size: 12px;
                    color:
                    line-height: 1.6;
                    text-align: justify;
                }
                .content {
                    margin-bottom: 20px;
                }
                .table-content {
                    width: 100%;
                    border-collapse: collapse;
                    margin-top: 20px;
                }
                .table-content th, .table-content td {
                    border: 1px solid
                    padding: 8px;
                    text-align: left;
                }
                .table-content th {
                    background-color:
                    font-weight: bold;
                }
                footer {
                    position: fixed;
                    bottom: 20px;
                    left: 20px;
                    right: 20px;
                    text-align: center;
                    font-size: 10px;
                    color:
                }
            </style>
        ";

    $html = $css;

    $html .= '<h1>' . htmlspecialchars($data['titulo']) . '</h1>';

    if (!empty($data['texto'])) {
      $html .= '<div class="content">' . nl2br(htmlspecialchars($data['texto'])) . '</div>';
    }

    if (!empty($data['subtitulos'])) {
      foreach ($data['subtitulos'] as $subtitle) {
        $html .= '<h2>' . htmlspecialchars($subtitle['subtitulo']) . '</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($subtitle['conteudo'])) . '</p>';
      }
    }

    if (!empty($data['tabelas'])) {
      foreach ($data['tabelas'] as $table) {
        $html .= '<table class="table-content">';
        foreach ($table as $rowIndex => $row) {
          $html .= '<tr>';
          foreach ($row as $cell) {
            $tag = $rowIndex === 0 ? 'th' : 'td';
            $html .= "<$tag>" . htmlspecialchars($cell) . "</$tag>";
          }
          $html .= '</tr>';
        }
        $html .= '</table>';
      }
    }

    $html .= '<footer>';
    $html .= '<div>URL: ' . htmlspecialchars($data['url']) . '</div>';
    $html .= '<div>Exportado em: ' . htmlspecialchars($data['data']) . '</div>';
    $html .= '<div>&copy; ' . date('Y') . ' Portal da Transparência. Todos os direitos reservados.</div>';
    $html .= '</footer>';

    $pdfGenerator = new Dompdf();
    $pdfGenerator->loadHtml($html);
    $pdfGenerator->setPaper('A4', 'portrait');
    $pdfGenerator->render();

    return $pdfGenerator->output();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

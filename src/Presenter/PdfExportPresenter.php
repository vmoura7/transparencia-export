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
            font-family: 'Helvetica Neue', Arial, sans-serif;
            margin: 40px;
            padding: 0;
            background-color: #f9f9f9;
            color: #333;
        }
        header {
            text-align: center;
            padding-bottom: 20px;
            border-bottom: 2px solid #0047ab;
            margin-bottom: 30px;
        }
        header h1 {
            color: #0047ab;
            font-size: 24px;
            margin: 0;
        }
        header p {
            font-size: 14px;
            color: #666;
            margin: 5px 0 0;
        }
        h2 {
            color: #0047ab;
            margin-top: 30px;
            font-size: 18px;
        }
        p {
            font-size: 12px;
            color: #555;
            line-height: 1.8;
            text-align: justify;
            margin: 10px 0;
        }
        .content {
            background-color: #fff;
            border: 1px solid #ddd;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        }
        .table-content {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .table-content th, .table-content td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .table-content th {
            background-color: #0047ab;
            color: #fff;
            font-weight: bold;
        }
        .table-content tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background-color: #0047ab;
            color: #fff;
            text-align: center;
            padding: 10px;
            font-size: 10px;
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

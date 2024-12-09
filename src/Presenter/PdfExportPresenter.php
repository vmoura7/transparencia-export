<?php

namespace Drupal\transparencia_export\Presenter;

use Dompdf\Dompdf;

class PdfExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    $css = "
      <style>
          @page {
              margin: 0;
          }
          body {
              font-family: 'Helvetica Neue', Arial, sans-serif;
              margin: 0;
              padding: 40px;
              background-color: #f4f4f4;
              color: #333;
          }
          .watermark {
              position: absolute;
              top: 50%;
              left: 50%;
              transform: translate(-50%, -50%) rotate(-45deg);
              opacity: 0.1;
              font-size: 100px;
              font-weight: bold;
              color: #cccccc;
          }
          .letterhead {
              position: relative;
              border: 2px solid #0056b3;
              padding: 20px;
              margin-bottom: 30px;
          }
          .letterhead-header {
              display: flex;
              justify-content: space-between;
              align-items: center;
              margin-bottom: 20px;
              border-bottom: 1px solid #0056b3;
              padding-bottom: 15px;
          }
          .letterhead-logo {
              max-width: 150px;
          }
          .letterhead-title {
              text-align: right;
          }
          .letterhead-title h1 {
              color: #0056b3;
              font-size: 24px;
              margin: 0;
          }
          .letterhead-title p {
              font-size: 12px;
              color: #666;
              margin: 5px 0 0;
          }
          .content {
              border: 1px solid #e0e0e0;
              padding: 20px;
              border-radius: 5px;
              margin-bottom: 30px;
              box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          }
          h2 {
              color: #0056b3;
              margin-top: 30px;
              font-size: 18px;
              border-bottom: 1px solid #e0e0e0;
              padding-bottom: 10px;
          }
          p {
              font-size: 12px;
              color: #333;
              line-height: 1.8;
              text-align: justify;
              margin: 10px 0;
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
              background-color: #0056b3;
              color: #ffffff;
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
              background-color: #0056b3;
              color: #ffffff;
              text-align: center;
              padding: 10px;
              font-size: 10px;
          }
      </style>
      ";

    $html = $css;
    $html .= '
      <div class="watermark">Portal Transparência</div>
      <div class="letterhead">
          <div class="letterhead-header">
              <img src="path/to/your/logo.png" alt="Logo" class="letterhead-logo">
              <div class="letterhead-title">
                  <h1>Portal da Transparência</h1>
                  <p>Documento Oficial de Transparência</p>
              </div>
          </div>
      </div>';

    $html .= '<h1>' . htmlspecialchars($data['titulo']) . '</h1>';

    // Seções
    if (!empty($data['secoes'])) {
      foreach ($data['secoes'] as $secao) {
        $html .= '<h2>' . htmlspecialchars($secao['titulo']) . '</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($secao['conteudo'])) . '</p>';
      }
    }

    // Tabelas
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
    $html .= '<div>Versão: ' . htmlspecialchars((string)$data['versao']) . '</div>';
    $html .= '<div>URL: ' . htmlspecialchars($data['url']) . '</div>';
    $html .= '<div>Exportado em: ' . htmlspecialchars($data['data']) . '</div>';
    $html .= '<div>&copy; ' . date('Y') . ' Portal da Transparência. Todos os direitos reservados.</div>';
    $html .= '</footer>';

    $pdfGenerator = new Dompdf();

    if (!empty($data['tabelas'])) {
      $pdfGenerator->setPaper('A4', 'landscape');
    } else {
      $pdfGenerator->setPaper('A4', 'portrait');
    }

    $pdfGenerator->loadHtml($html);
    $pdfGenerator->render();

    return $pdfGenerator->output();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

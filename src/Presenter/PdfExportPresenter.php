<?php

namespace Drupal\transparencia_export\Presenter;

use Dompdf\Dompdf;

class PdfExportPresenter extends BaseExportPresenter
{
  protected function convertToFormat(array $data): string
  {
    // Estilos CSS para o layout do PDF.
    $css = "
          <style>
              body {
                  font-family: Arial, sans-serif;
                  margin: 0;
                  padding: 20px;
              }
              h1 {
                  color: #2C3E50;
                  border-bottom: 2px solid #2980B9;
                  padding-bottom: 10px;
              }
              p {
                  font-size: 12px;
                  color: #34495E;
              }
              table {
                  width: 100%;
                  border-collapse: collapse;
                  margin-top: 20px;
              }
              th, td {
                  border: 1px solid #ddd;
                  padding: 8px;
                  text-align: left;
              }
              th {
                  background-color: #2980B9;
                  color: #fff;
              }
              footer {
                  position: absolute;
                  bottom: 20px;
                  left: 20px;
                  right: 20px;
                  text-align: center;
                  font-size: 10px;
                  color: #7f8c8d;
                  display: flex;
                  justify-content: space-between;
              }
              .footer-item {
                  flex: 1;
              }
          </style>
      ";

    // Construir o conteúdo do PDF
    $html = $css;
    $html .= '<h1>' . htmlspecialchars($data['titulo']) . '</h1>';

    // Renderizar conteúdo principal, se não houver tabelas.
    if (!empty($data['texto']) && empty($data['tabelas'])) {
      $html .= '<div>' . nl2br(htmlspecialchars($data['texto'])) . '</div>';
    }

    // Renderizar tabelas.
    if (!empty($data['tabelas'])) {
      foreach ($data['tabelas'] as $table) {
        $html .= '<table>';
        foreach ($table as $rowIndex => $row) {
          $html .= '<tr>';
          foreach ($row as $cell) {
            $html .= $rowIndex === 0 ? '<th>' : '<td>';
            $html .= htmlspecialchars($cell);
            $html .= $rowIndex === 0 ? '</th>' : '</td>';
          }
          $html .= '</tr>';
        }
        $html .= '</table>';
      }
    }

    // Adicionando o rodapé com data, URL e copyright
    $html .= '<footer>';
    $html .= '<div class="footer-item">URL: ' . htmlspecialchars($data['url']) . '</div>';
    $html .= '<div class="footer-item">Exportado em: ' . htmlspecialchars($data['data']) . '</div>';
    $html .= '<div style="text-align: center; font-size: 10px; color: #7f8c8d; margin-top: 10px;">';
    $html .= '&copy; ' . date('Y') . ' Portal da Transparência. Todos os direitos reservados.';
    $html .= '</div>';
    $html .= '</footer>';

    // Gerar o PDF.
    $pdfGenerator = new Dompdf();
    $pdfGenerator->loadHtml($html);
    $pdfGenerator->setPaper('A4', 'portrait');
    $pdfGenerator->render();

    // Retornar o conteúdo do PDF gerado.
    return $pdfGenerator->output();
  }


  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

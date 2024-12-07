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
                    margin: 20px;
                    padding: 20px;
                }
                h1 {
                    color: #2C3E50;
                    border-bottom: 2px solid #2980B9;
                    padding-bottom: 10px;
                    margin-bottom: 20px;
                }
                h2 {
                    color: #34495E;
                    margin-top: 20px;
                }
                p {
                    font-size: 12px;
                    color: #34495E;
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
                    border: 1px solid #ccc;
                    padding: 8px;
                    text-align: left;
                }
                .table-content th {
                    background-color: #f2f2f2;
                    font-weight: bold;
                }
                footer {
                    position: fixed;
                    bottom: 20px;
                    left: 20px;
                    right: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #7f8c8d;
                }
            </style>
        ";

    // Construção do conteúdo do PDF.
    $html = $css;

    // Adicionar título principal.
    $html .= '<h1>' . htmlspecialchars($data['titulo']) . '</h1>';

    // Renderizar texto principal, se disponível.
    if (!empty($data['texto'])) {
      $html .= '<div class="content">' . nl2br(htmlspecialchars($data['texto'])) . '</div>';
    }

    // Renderizar subtítulos e conteúdos associados.
    if (!empty($data['subtitulos'])) {
      foreach ($data['subtitulos'] as $subtitle) {
        $html .= '<h2>' . htmlspecialchars($subtitle['subtitulo']) . '</h2>';
        $html .= '<p>' . nl2br(htmlspecialchars($subtitle['conteudo'])) . '</p>';
      }
    }

    // Renderizar tabelas, se disponíveis.
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

    // Adicionar rodapé com URL e data de exportação.
    $html .= '<footer>';
    $html .= '<div>URL: ' . htmlspecialchars($data['url']) . '</div>';
    $html .= '<div>Exportado em: ' . htmlspecialchars($data['data']) . '</div>';
    $html .= '<div>&copy; ' . date('Y') . ' Portal da Transparência. Todos os direitos reservados.</div>';
    $html .= '</footer>';

    // Gerar o PDF.
    $pdfGenerator = new Dompdf();
    $pdfGenerator->loadHtml($html);
    $pdfGenerator->setPaper('A4', 'portrait'); // Tamanho e orientação do papel.
    $pdfGenerator->render();

    return $pdfGenerator->output();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

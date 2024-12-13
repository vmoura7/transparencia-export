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
              :root {
                  --cor-fundo: #F0F4F8;  /* azul-cinza claro */
                  --cor-texto: #2C3E50;  /* azul-grafite escuro */
                  --cor-destaque: #607D8B;  /* blue-grey médio */
                  --cor-linha: #B0BEC5;  /* blue-grey suave */
                  --cor-link: #34495E;  /* azul-petróleo escuro */
              }
              body {
                  font-family: 'Helvetica Neue', Arial, sans-serif;
                  margin: 0;
                  padding: 40px;
                  background-color: var(--cor-fundo);
                  color: var(--cor-texto);
              }
              .watermark {
                  position: absolute;
                  top: 50%;
                  left: 50%;
                  transform: translate(-50%, -50%) rotate(-45deg);
                  opacity: 0.1;
                  font-size: 100px;
                  font-weight: bold;
                  color: var(--cor-destaque);
              }
              .letterhead {
                  position: relative;
                  border: 2px solid var(--cor-link);
                  padding: 20px;
                  margin-bottom: 30px;
              }
              .letterhead-header {
                  display: flex;
                  justify-content: space-between;
                  align-items: center;
                  margin-bottom: 20px;
                  border-bottom: 1px solid var(--cor-link);
                  padding-bottom: 15px;
              }
              .letterhead-logo {
                  max-width: 150px;
              }
              .letterhead-title {
                  text-align: right;
              }
              .letterhead-title h1 {
                  color: var(--cor-link);
                  font-size: 24px;
                  margin: 0;
              }
              .letterhead-title p {
                  font-size: 12px;
                  color: var(--cor-destaque);
                  margin: 5px 0 0;
              }
              .content {
                  border: 1px solid var(--cor-linha);
                  padding: 20px;
                  border-radius: 5px;
                  margin-bottom: 30px;
                  box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
              }
              h2 {
                  color: var(--cor-link);
                  margin-top: 30px;
                  font-size: 18px;
                  border-bottom: 1px solid var(--cor-linha);
                  padding-bottom: 10px;
              }
              p {
                  font-size: 12px;
                  color: var(--cor-texto);
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
                  border: 1px solid var(--cor-linha);
                  padding: 10px;
                  text-align: left;
              }
              .table-content th {
                  background-color: var(--cor-link);
                  color: #ffffff;
                  font-weight: bold;
              }
              .table-content tr:nth-child(even) {
                  background-color: #F4F6F7;
              }
              footer {
                  position: fixed;
                  bottom: 0;
                  left: 0;
                  right: 0;
                  background-color: var(--cor-link);
                  color: #ffffff;
                  text-align: center;
                  padding: 10px;
                  font-size: 10px;
              }
          </style>
      ";

    // Captura o nome do site do Drupal 10
    $site_name = \Drupal::config('system.site')->get('name');

    $html = $css;
    $html .= '
    <div class="watermark">' . htmlspecialchars($site_name) . '</div>
    <div class="letterhead">
        <div class="letterhead-header">
            <div class="letterhead-title">
                <h1>' . htmlspecialchars($site_name) . '</h1>
                <p>Portal da Transparência</p>
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

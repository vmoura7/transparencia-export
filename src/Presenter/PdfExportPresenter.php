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
                footer {
                    position: absolute;
                    bottom: 20px;
                    left: 20px;
                    right: 20px;
                    text-align: center;
                    font-size: 10px;
                    color: #7f8c8d;
                    display: flex; /* Usar flexbox para o layout do rodapé */
                    justify-content: space-between; /* Espaço entre os elementos */
                }
                .footer-item {
                    flex: 1; /* Flexível para ocupar o espaço */
                    cor: #34495E !important;
                }
            </style>
        ";

    // Construa o HTML do PDF com base nos dados recebidos.
    $html = $css; // Adicione o CSS ao HTML
    $html .= '<h1>' . htmlspecialchars($data['titulo']) . '</h1>';
    $html .= '<div>' . nl2br(htmlspecialchars($data['texto'])) . '</div>'; // Mantém quebras de linha no conteúdo

    // Adicionando o rodapé com data, URL e copyright
    $html .= '<footer>';
    $html .= '<div class="footer-item">URL: ' . htmlspecialchars($data['url']) . '</div>';
    $html .= '<div class="footer-item">Exportado em: ' . htmlspecialchars($data['data']) . '</div>';
    $html .= '<div style="text-align: center; font-size: 10px; color: #7f8c8d; margin-top: 10px;">';
    $html .= '&copy; ' . date('Y') . ' Portal da Transparência. Todos os direitos reservados.';
    $html .= '</div>';
    $html .= '</footer>'; // Fechar o rodapé

    // Gerar o PDF.
    $pdfGenerator = new Dompdf();
    $pdfGenerator->loadHtml($html);
    $pdfGenerator->setPaper('A4', 'portrait'); // Defina o tamanho do papel e orientação.
    $pdfGenerator->render();

    // Retornar o conteúdo do PDF gerado.
    return $pdfGenerator->output();
  }

  public function getHeaders(): array
  {
    return ['Content-Type' => 'application/pdf'];
  }
}

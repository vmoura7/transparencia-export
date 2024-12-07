<?php

namespace Drupal\transparencia_export\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\transparencia_export\Repository\ExcludedPathsRepository;

/**
 * Provides a 'Transparência Export Block' block.
 *
 * @Block(
 *   id = "transparencia_export_block",
 *   admin_label = @Translation("Transparência Export Block")
 * )
 */
class TransparenciaExportBlock extends BlockBase implements ContainerFactoryPluginInterface
{
  protected $excludedPathsRepository;

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ExcludedPathsRepository $excludedPathsRepository)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->excludedPathsRepository = $excludedPathsRepository;
  }

  /**
   * Creates an instance of the block.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The service container.
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   *
   * @return static
   *   A new instance of the block.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('transparencia_export.excluded_paths_repository') // Corrigido aqui
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build()
  {
    $module_path = \Drupal::service('extension.list.module')->getPath('transparencia_export_block');
    $file_url_generator = \Drupal::service('file_url_generator');

    // Gerar URLs para os ícones.
    $json_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/json-icon.svg');
    $xml_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/xml-icon.svg');
    $pdf_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/pdf-icon.svg');
    $print_icon_url = $file_url_generator->generateAbsoluteString($module_path . '/images/print-icon.svg');

    // Obter a URL atual
    $current_path = \Drupal::service('path.current')->getPath();
    $current_route_name = \Drupal::service('path_alias.manager')->getAliasByPath($current_path);

    // Aqui, você pode acessar os caminhos excluídos através do repositório.
    $excluded_paths = $this->excludedPathsRepository->getExcludedPaths();

    // Inicializar $rendered_buttons como string vazia.
    $rendered_buttons = '';

    if (!in_array($current_route_name, $excluded_paths)) {
      // Botões de exportação.
      $buttons = [
        'json' => $json_icon_url,
        'xml' => $xml_icon_url,
        'pdf' => $pdf_icon_url,
        'print' => $print_icon_url,
      ];

      foreach ($buttons as $format => $icon_url) {
        $rendered_buttons .= '
      <div id="export-' . $format . '-button" class="export-button">
        <img src="' . $icon_url . '" alt="Exportar ' . strtoupper($format) . '" height="24" width="24" />
      </div>';
      }
    }

    return [
      '#markup' => '<div class="export-json-container">' . $rendered_buttons . '</div>',
      '#attached' => [
        'library' => [
          'transparencia_export/export_block',
        ],
      ],
    ];
  }
}

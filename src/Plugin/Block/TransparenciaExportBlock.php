<?php

namespace Drupal\transparencia_export\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

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
  /**
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new TransparenciaExportBlock.
   *
   * @param array $configuration
   *   Configuration array.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, $entity_type_manager)
  {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
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
      $container->get('entity_type.manager')
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

    // Botões de exportação.
    $buttons = [
      'json' => $json_icon_url,
      'xml' => $xml_icon_url,
      'pdf' => $pdf_icon_url,
      'print' => $print_icon_url,
    ];

    $rendered_buttons = '';
    foreach ($buttons as $format => $icon_url) {
      $rendered_buttons .= '
        <div id="export-' . $format . '-button" class="export-button">
          <img src="' . $icon_url . '" alt="Exportar ' . strtoupper($format) . '" height="24" width="24" />
        </div>';
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

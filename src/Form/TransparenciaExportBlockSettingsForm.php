<?php

namespace Drupal\transparencia_export\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\transparencia_export\Repository\ExcludedPathsRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

class TransparenciaExportBlockSettingsForm extends FormBase
{

  protected $excludedPathsRepository;

  public function __construct(ExcludedPathsRepository $excludedPathsRepository)
  {
    $this->excludedPathsRepository = $excludedPathsRepository;
  }

  public static function create(ContainerInterface $container)
  {
    return new static(
      $container->get('transparencia_export.excluded_paths_repository')
    );
  }

  public function getFormId()
  {
    return 'transparencia_export_settings';
  }

  public function buildForm(array $form, FormStateInterface $form_state)
  {
    $excluded_paths = $this->excludedPathsRepository->getExcludedPaths();

    $form['excluded_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Caminhos Excluídos'),
      '#default_value' => implode("\n", $excluded_paths),
      '#description' => $this->t('Caminhos a serem excluídos, um por linha.'),
    ];

    $form['actions'] = [
      '#type' => 'actions',
    ];
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Salvar'),
      '#button_type' => 'primary',
    ];

    return $form;
  }

  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    $paths = array_filter(array_map('trim', explode("\n", $form_state->getValue('excluded_paths'))));

    $this->excludedPathsRepository->clearExcludedPaths();

    foreach ($paths as $path) {
      if (!$this->excludedPathsRepository->isPathExcluded($path)) {
        $this->excludedPathsRepository->addExcludedPath($path);
      }
    }

    \Drupal::messenger()->addMessage($this->t('Os caminhos excluídos foram salvos.'));
  }
}

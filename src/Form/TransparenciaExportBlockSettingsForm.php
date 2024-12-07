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
    // Aqui você pode usar o repositório para obter os caminhos excluídos.
    $excluded_paths = $this->excludedPathsRepository->getExcludedPaths();

    // Construa o formulário como necessário.
    $form['excluded_paths'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Caminhos Excluídos'),
      '#default_value' => implode("\n", $excluded_paths),
      '#description' => $this->t('Caminhos a serem excluídos, um por linha.'),
    ];

    // Adiciona o botão de envio
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
    // Obter os caminhos do campo de texto do formulário.
    $paths = array_filter(array_map('trim', explode("\n", $form_state->getValue('excluded_paths'))));

    // Limpar os caminhos antigos e adicionar novos.
    // Você pode querer limpar os caminhos atuais antes de adicionar novos.
    $this->excludedPathsRepository->clearExcludedPaths(); // Limpa os caminhos existentes

    foreach ($paths as $path) {
      if (!$this->excludedPathsRepository->isPathExcluded($path)) {
        $this->excludedPathsRepository->addExcludedPath($path); // Adiciona o caminho ao banco de dados
      }
    }

    // Definir uma mensagem de sucesso.
    \Drupal::messenger()->addMessage($this->t('Os caminhos excluídos foram salvos.'));
  }
}

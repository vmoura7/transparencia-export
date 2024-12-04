(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exportBlock = {
    attach: function (context, settings) {
      // Adicionando manipuladores de eventos para os botões de exportação
      once('export-json', '#export-json-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportData('json');
        });
      });

      once('export-xml', '#export-xml-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportData('xml');
        });
      });

      once('export-pdf', '#export-pdf-button', context).forEach((element) => {
        $(element).on('click', function () {
          exportData('pdf');
        });
      });

      // Imprimir página
      once('print-page', '#print-button', context).forEach((element) => {
        $(element).on('click', function () {
          window.print(); // Chama a função de impressão do navegador.
        });
      });

      // Função para exportar dados
      function exportData(format) {
        const currentPath = drupalSettings.path.currentPath;

        fetch(`/transparencia/export/${format}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Falha ao exportar dados no formato ${format}.`);
            }

            // Extrair o nome da página da URL atual
            const fileName = window.location.pathname.split('/').pop() || 'export';

            return response.blob().then(blob => ({ blob, fileName }));
          })
          .then(({ blob, fileName }) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${fileName}.${format}`; // Nome do arquivo baseado na URL
            a.click();
            URL.revokeObjectURL(url); // Limpar a URL do blob
          })
          .catch((error) => {
            console.error(`Erro ao exportar no formato ${format}:`, error);
            alert(`Erro ao exportar os dados em ${format}.`);
          });
      }
    },
  };
})(jQuery, Drupal, drupalSettings);

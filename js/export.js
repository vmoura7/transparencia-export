(function ($, Drupal, drupalSettings) {
  Drupal.behaviors.exportBlock = {
    attach: function (context, settings) {
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

      once('print-page', '#export-print-button', context).forEach((element) => {
        $(element).on('click', function () {
          window.print();
        });
      });

      function exportData(format) {
        const currentPath = drupalSettings.path.currentPath;

        fetch(`/transparencia/export/${format}`)
          .then((response) => {
            if (!response.ok) {
              throw new Error(`Falha ao exportar dados no formato ${format}.`);
            }

            const fileName = window.location.pathname.split('/').pop() || 'export';

            return response.blob().then(blob => ({ blob, fileName }));
          })
          .then(({ blob, fileName }) => {
            const url = URL.createObjectURL(blob);
            const a = document.createElement('a');
            a.href = url;
            a.download = `${fileName}.${format}`;
            a.click();
            URL.revokeObjectURL(url);
          })
          .catch((error) => {
            console.error(`Erro ao exportar no formato ${format}:`, error);
            alert(`Erro ao exportar os dados em ${format}.`);
          });
      }
    },
  };
})(jQuery, Drupal, drupalSettings);

jQuery(document).ready(function($) {
    console.log('cc!')

    $('#comprobante_pago_movil').change(function() {
      var file_data = $('#comprobante_pago_movil').prop('files')[0];
      var nonce = $('#comprobante-pago-movil').val()

      var form_data = new FormData();

      form_data.append('file', file_data);
      form_data.append('action', 'upload_comprobante_pago_movil');
      form_data.append('nonce', nonce);

      $.ajax({
        url: ajaxurl,
        type: 'POST',
        data: form_data,
        contentType: false,
        processData: false,
        success: function(response) {
          $('#comprobante-url').val(response);
          alert('Comprobante cargado con éxito');
        },
        error: function(xhr, textStatus, errorThrown) {
          alert('Error al cargar el comprobante: ' + textStatus);
        }
      });
    });
  });

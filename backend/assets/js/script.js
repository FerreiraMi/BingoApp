// assets/js/script.js

$(document).ready(function() {
    // Lógica para o formulário de contato
    $('#contactForm').submit(function(event) {
        event.preventDefault(); // Impede o recarregamento da página

        var formData = $(this).serialize(); // Pega os dados do formulário

        $.ajax({
            type: 'POST',
            url: $(this).attr('action'),
            data: formData,
            dataType: 'json',
            success: function(response) {
                // Limpa mensagens antigas
                $('#form-messages').empty();

                if (response.success) {
                    $('#form-messages').html('<div class="alert alert-success">' + response.message + '</div>');
                    // Limpa o formulário
                    $('#contactForm')[0].reset();
                } else {
                    $('#form-messages').html('<div class="alert alert-danger">' + response.message + '</div>');
                }
            },
            error: function() {
                $('#form-messages').html('<div class="alert alert-danger">Desculpe, parece que o servidor de e-mail não está respondendo. Por favor, tente novamente mais tarde!</div>');
            }
        });
    });
});

<?php

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize e validar os dados de entrada
    $name = strip_tags(trim($_POST["name"]));
    $name = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $email = filter_var(trim($_POST["email"]), FILTER_SANITIZE_EMAIL);
    $message = strip_tags(trim($_POST["message"]));
    $message = htmlspecialchars($message, ENT_QUOTES, 'UTF-8');

    // Verifica se os campos estão vazios
    if (empty($name) || empty($message) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        http_response_code(400);
        echo json_encode(["success" => false, "message" => "Oops! Houve um problema com seu envio. Por favor, preencha o formulário e tente novamente."]);
        exit;
    }

    // Endereço de e-mail do destinatário
    $recipient = "contato@abeenterprises.com.br";

    // Assunto do e-mail
    $subject = "Novo Contato do Site Bingou! de $name";

    // Conteúdo do e-mail
    $email_content = "Nome: $name\n";
    $email_content .= "Email: $email\n\n";
    $email_content .= "Mensagem:\n$message\n";

    // Cabeçalhos do e-mail
    $email_headers = "From: $name <$email>";

    // Envia o e-mail
    if (mail($recipient, $subject, $email_content, $email_headers)) {
        http_response_code(200);
        echo json_encode(["success" => true, "message" => "Obrigado! Sua mensagem foi enviada com sucesso."]);
    } else {
        http_response_code(500);
        echo json_encode(["success" => false, "message" => "Oops! Algo deu errado e não conseguimos enviar sua mensagem."]);
    }

} else {
    http_response_code(403);
    echo json_encode(["success" => false, "message" => "Houve um problema com seu envio, por favor, tente novamente."]);
}

?>

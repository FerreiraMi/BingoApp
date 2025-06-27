<?
$HOST_NAME = getenv('HOST_NAME');
$WSHOST_NAME = getenv('WSHOST_NAME');

if ($HOST_NAME === false) {
    $HOST_NAME = 'https://app-bingo.iw7.com.br'; // Define um valor padrão caso a variável não esteja definida.
}

if ($WSHOST_NAME === false) {
    $WSHOST_NAME = 'wss://app-bingo-ws.iw7.com.br'; // Define um valor padrão caso a variável não esteja definida.
}

#$HOST_NAME   =  "http:/bingo-webserver"; // For development purposes, you can set a local host name.
#$WSHOST_NAME = "http://localhost:8080";



?>

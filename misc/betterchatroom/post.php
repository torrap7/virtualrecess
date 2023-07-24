<?php
session_start();

if (isset($_SESSION['name']) && isset($_SESSION['chatroom_name']) && isset($_SESSION['password'])) {
    $text = $_POST['text'];

    $chatroom_name = $_SESSION['chatroom_name'];
    $text_message = "<div class='msgln'><span class='chat-time'>" . date("g:i A") . "</span> <b class='user-name'>" . $_SESSION['name'] . "</b> " . stripslashes(htmlspecialchars($text)) . "<br></div>";
    file_put_contents("log_" . $chatroom_name . ".html", $text_message, FILE_APPEND | LOCK_EX);
}
?>

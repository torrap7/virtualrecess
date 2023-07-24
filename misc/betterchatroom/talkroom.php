<?php
session_start();
session_regenerate_id(true);

if (!isset($_SESSION['name']) || !isset($_SESSION['chatroom_name']) || !isset($_SESSION['password'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['logout'])) {
    // Simple exit message
    $logout_message = "<div class='msgln'><span class='left-info'>User <b class='user-name-left'>" . $_SESSION['name'] . "</b> has left the chat session.</span><br></div>";
    file_put_contents("log.html", $logout_message, FILE_APPEND | LOCK_EX);

    session_destroy();
    header("Location: index.php"); // Redirect the user
    exit();
}

function sendMessage($message)
{
    $chatroom_name = $_SESSION['chatroom_name'];
    $formatted_message = "<div class='msgln'><span class='user-name-left'>" . $_SESSION['name'] . "</span>: " . stripslashes(htmlspecialchars($message)) . "<br></div>";
    file_put_contents("log_" . $chatroom_name . ".html", $formatted_message, FILE_APPEND | LOCK_EX);
}

function displayChatLog()
{
    $chatroom_name = $_SESSION['chatroom_name'];
    $log_file = "log_" . $chatroom_name . ".html";

    if (file_exists($log_file) && filesize($log_file) > 0) {
        $contents = file_get_contents($log_file);
        echo $contents;
    }
}

function loginForm()
{
    echo '
    <div id="loginform"> 
        <p>Please enter your name to continue!</p> 
        <form action="talkroom.php" method="post"> 
            <label for="name">Name &mdash;</label> 
            <input type="text" name="name" id="name" /> 
            <input type="submit" name="enter" id="enter" value="Enter" /> 
        </form> 
    </div>';
}

function chatroomForm()
{
    echo '
    <div id="chatroomform"> 
        <p>Create or join a chatroom!</p> 
        <form action="talkroom.php" method="post"> 
            <label for="chatroom_name">Chatroom Name:</label> 
            <input type="text" name="chatroom_name" id="chatroom_name" /><br/>
            <label for="password">Password:</label> 
            <input type="password" name="password" id="password" /><br/>
            <input type="submit" name="create_chatroom" id="create_chatroom" value="Create/Join" /> 
        </form> 
    </div>';
}

if (isset($_POST['enter'])) {
    if ($_POST['name'] != "") {
        $_SESSION['name'] = stripslashes(htmlspecialchars($_POST['name']));
    } else {
        echo '<span class="error">Please type in a name</span>';
    }
}

if (isset($_POST['create_chatroom'])) {
    if ($_POST['chatroom_name'] != "" && $_POST['password'] != "") {
        $chatroom_name = stripslashes(htmlspecialchars($_POST['chatroom_name']));
        $password = stripslashes(htmlspecialchars($_POST['password']));

        // Validate chatroom name and password
        if (preg_match('/^[a-zA-Z0-9_]+$/', $chatroom_name) && preg_match('/^[a-zA-Z0-9_]+$/', $password)) {
            $_SESSION['chatroom_name'] = $chatroom_name;
            $_SESSION['password'] = $password;

            $log_file = "log_" . $chatroom_name . ".html";
            if (!file_exists($log_file)) {
                // Create a new chatroom log file
                file_put_contents($log_file, "");
            }
        } else {
            echo '<span class="error">Invalid chatroom name or password</span>';
        }
    } else {
        echo '<span class="error">Please enter a chatroom name and password</span>';
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>Chatroom!</title>
    <meta name="description" content="Chatroom!" />
    <link rel="stylesheet" href="style.css" />
</head>

<body>
    <?php
    if (!isset($_SESSION['name'])) {
        loginForm();
    } else if (!isset($_SESSION['chatroom_name']) || !isset($_SESSION['password'])) {
        chatroomForm();
    } else {
    ?>
        <div id="wrapper">
            <div id="menu">
                <p class="welcome">Welcome, <b><?php echo $_SESSION['name']; ?></b></p>
                <p class="logout"><a id="exit" href="talkroom.php?logout=true">Exit Chat</a></p>
            </div>
            <div id="chatbox">
                <?php
                displayChatLog();
                ?>
            </div>
            <form name="message" action="">
                <input name="usermsg" type="text" id="usermsg" />
                <input name="submitmsg" type="submit" id="submitmsg" value="Send" />
            </form>
        </div>
        <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
        <script type="text/javascript">
            // jQuery Document 
            $(document).ready(function () {
                $("#submitmsg").click(function () {
                    var clientmsg = $("#usermsg").val();
                    $.post("post.php", { text: clientmsg });
                    $("#usermsg").val("");
                    return false;
                });
                function loadLog() {
                    var oldscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height before the request 
                    $.ajax({
                        url: "log_<?php echo $_SESSION['chatroom_name']; ?>.html",
                        cache: false,
                        success: function (html) {
                            $("#chatbox").html(html); // Insert chat log into the #chatbox div 
                            // Auto-scroll 
                            var newscrollHeight = $("#chatbox")[0].scrollHeight - 20; // Scroll height after the request 
                            if (newscrollHeight > oldscrollHeight) {
                                $("#chatbox").animate({ scrollTop: newscrollHeight }, 'normal'); // Autoscroll to bottom of div 
                            }
                        }
                    });
                }
                setInterval(loadLog, 3000);
                $("#exit").click(function () {
                    var exit = confirm("Are you sure you want to end the session?");
                    if (exit == true) {
                        window.location = "talkroom.php?logout=true";
                    }
                });
            });
        </script>
    <?php
    }
    ?>
</body>

</html>

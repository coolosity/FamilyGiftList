<?php
session_start();

function navBar($url, $mobile)
{
    $links = array(
        'home.php', 'Home',
        'families.php', 'Families',
        'logout.php', 'Logout');

    echo '
    <nav id="main-navbar" class="navbar navbar-default navbar-fixed-top">
        <div class="container-fluid">';
    if (!$mobile) {
        echo '
            <div class="navbar-header">
                <a class="navbar-brand" href="#">Family Gift List</a>
            </div>
        ';
    }
    echo '
            <ul class="nav navbar-nav">';
    if (!$mobile) {
        for ($i = 0; $i < count($links); $i += 2) {
            echo '<li';
            if (strpos($url, $links[$i]) !== false) {
                echo ' class="active"';
            }
            echo '><a href="' . $links[$i] . '">' . $links[$i + 1] . '</a></li>';
        }
    } else {
        if (strpos('mobile.php', $url) >= 0) $url = 'home.php';
        echo '<li class="dropdown"><a class="dropdown-toggle" data-toggle="dropdown" href="#">';
        for ($i = 0; $i < count($links); $i += 2) {
            if (strpos($url, $links[$i]) !== false) {
                echo $links[$i + 1];
            }
        }
        echo '
        <span class="caret"></span></a>
        <ul class="dropdown-menu">';
        for ($i = 0; $i < count($links); $i += 2) {
            echo '<li><a href="' . $links[$i] . '">' . $links[$i + 1] . '</a></li>';
        }
        echo '
        </ul>
        </li>
        ';
    }
    echo '
            </ul>
        </div>
    </nav>
    <script>
        function updateBodyPadding() {
            $(\'body\').css(\'padding-top\', parseInt($(\'#main-navbar\').css("height")) + 10);
        }
        updateBodyPadding();
        $(window).resize(updateBodyPadding);
    </script>
    ';
}

function assertLogin()
{
    if (!isset($_SESSION['id'])) {
        header('Location: index.php');
    }
    global $uid;
    $uid = $_SESSION['id'];
}

?>
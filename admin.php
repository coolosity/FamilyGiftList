<?php
require('connect.php');
session_start();

function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}

if (isset($_POST['adminpass'])) {
    if (strcmp($_POST['adminpass'], 'piggy1234') == 0) {
        $_SESSION['adminid'] = 1;
    }
}

if (isset($_POST['mode'])) {
    $mode = $_POST['mode'];
    if ($mode == 1 && isset($_POST['user'])) {
        $uid = $_POST['user'];
        $result = $conn->query("SELECT * FROM changepass WHERE userid=$uid");
        if ($result->num_rows == 0) {
            $auth = generateRandomString(20);
            $conn->query("INSERT INTO changepass(authid, userid) VALUES('$auth', $uid)");
        } else {
            $msg = 'Link already exists for this user';
        }
    }
}
$urispl = explode('/', $_SERVER['REQUEST_URI']);
$uri = '';
for ($i = 0; $i < count($urispl)-1; $i++) {
    $uri .= $urispl[$i] . '/';
}
$actual_link = "http://$_SERVER[HTTP_HOST]$uri";
$actual_link .= 'changepass.php?authid=';
?>
    <html>
    <head>
        <title>Admin Page</title>
    </head>
    <body>
    <?php
    if (isset($_SESSION['adminid'])) {
        echo '
    <a href="logout.php"><button>Logout</button></a>
    <br>
    <div id="msg">';
        if (isset($msg))echo $msg;
    echo '
    </div>
    <p>
        Links:
        <table border="1">
            <tr>
                <th>User</th>
                <th>Link</th>
                <th>Copy</th>
            </tr>';
        $result = $conn->query("SELECT c.id, u.realname, c.authid FROM changepass c JOIN users u ON c.userid=u.id");
        while ($row = $result->fetch_assoc()) {
            echo '
            <tr>
                <td>' . $row['realname'] . '</td>
                <td id="link' . $row['id'] . '">' . $actual_link . $row['authid'] . '</td>
                <td><button onclick="cp(' . $row['id'] . ')">Copy</button></td>
            </tr>
            ';
        }
        echo '
        </table>
    </p>
    <p>
        Generate Reset Password Link:<br>
        <form action="admin.php" method="post">
            <input type="hidden" name="mode" value="1"/>
            <select name="user">';
        $result = $conn->query("SELECT * FROM users");
        while ($row = $result->fetch_assoc()) {
            echo '<option value="' . $row['id'] . '">' . $row['realname'] . '</option>';
        }
        echo '
            </select>
            <input type="submit" value="Generate Link"/>
        </form>
    </p>
    ';
    } else {
        echo '
    <form action="admin.php" method="post">
        <input type="password" name="adminpass" placeholder="Admin Pass"/><br>
        <input type="submit" value="Login"/>
    </form>
    ';
    }
    ?>
    <script>
        function cp(id) {
            var text = document.getElementById("link" + id).innerHTML;
            window.prompt("Copy to clipboard: Ctrl+C, Enter", text);
        }
    </script>
    </body>
    </html>
<?php
$conn->close();
?>
<?php
require('connect.php');

if (isset($_POST['authid']) && isset($_POST['pass'])) {
    $auth = $_POST['authid'];
    $result = $conn->query("SELECT * FROM changepass WHERE authid='$auth'");
    if ($result->num_rows == 0) {
        exit('Invalid AuthID');
    }
    $row = $result->fetch_assoc();
    $hashpass = password_hash($_POST['pass'], PASSWORD_DEFAULT);
    $uid = $row['userid'];
    $conn->query("UPDATE users SET password='$hashpass' WHERE id=$uid");
    $conn->query("DELETE FROM changepass WHERE id=" . $row['id']);
    exit('Your password has been updated<br><a href="index.php">To login page</a>');
}

if (!isset($_GET['authid'])) {
    exit('You must provide an AuthID');
}
$auth = $_GET['authid'];

$result = $conn->query("SELECT u.realname, u.username FROM changepass c JOIN users u ON c.userid=u.id WHERE authid='$auth'");
if ($result->num_rows == 0) {
    exit('Invalid AuthID');
}
$row = $result->fetch_assoc();
?>
<html>
<head>
    <title>Change Password</title>
    <script src="assets/jquery-3.1.1.min.js"></script>
</head>
<body>
<?php
if (isset($row)) {
    echo '
    <p>
    Hello ' . $row['realname'] . '<br>
    Username: ' . $row['username'] . '
    </p>
    <form action="changepass.php" method="post">
        <input type="hidden" name="authid" value="' . $auth . '"/>
        <input type="password" name="pass" placeholder="New Password" id="newpass"/><br>
        <input type="password" placeholder="Confirm Password" id="cpass"/><br>
        <span id="passmsg"></span><br>
        <input type="submit" value="Change Password" id="cpbut" disabled/>
    </form>
    ';
}
?>
<script>
    var np = $("#newpass");
    var cp = $("#cpass");
    var msg = $("#passmsg");
    var but = $("#cpbut");

    function checkPass() {
        if (np.val() != cp.val()) {
            msg.html('Passwords do not match');
            msg.css('color', 'red');
            but.prop('disabled', true);
        } else if (np.val().length == 0) {
            msg.html('Password cannot be blank');
            msg.css('color', 'red');
        } else {
            msg.html('Passwords match');
            msg.css('color', 'green');
            but.prop('disabled', false);
        }
    }

    np.keyup(checkPass);
    cp.keyup(checkPass);
</script>
</body>
</html>
<?php
$conn->close();
?>
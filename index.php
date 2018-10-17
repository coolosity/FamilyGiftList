<?php
require('connect.php');
session_start();
if (isset($_POST['username']) && isset($_POST['password']) && isset($_POST['mode']) && isset($_POST['realname']) && isset($_POST['secretpass'])) {
    $mode = $conn->real_escape_string($_POST['mode']);
    $user = $conn->real_escape_string($_POST['username']);
    $rname = $conn->real_escape_string($_POST['realname']);
    $pass = $_POST['password'];
    if (strcmp($mode, 'login') == 0) {
        $result = $conn->query("SELECT * FROM users WHERE username='$user'");
        if ($result->num_rows == 0) {
            $msg = 'Invalid credentials';
        } else {
            $user = $result->fetch_assoc();
            if (password_verify($pass, $user['password'])) {
                $_SESSION['id'] = $user['id'];
            } else {
                $msg = 'Invalid credentials';
            }
        }
    } else if (strcmp($mode, 'register') == 0) {
        if (strcmp($_POST['secretpass'], 'fampass123') != 0) {
            $msg = 'Wrong secret password';
        } else {
            $result = $conn->query("SELECT * FROM users WHERE username='$user'");
            if ($result->num_rows > 0) {
                $msg = 'Username taken';
            } else {
                $hashpass = password_hash($pass, PASSWORD_DEFAULT);
                $conn->query("INSERT INTO users(username, password, realname) VALUES('$user', '$hashpass', '$rname')");
                $_SESSION['id'] = $conn->insert_id;
            }
        }
    }
}

if (isset($_SESSION['id'])) {
    header('Location: home.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Family Gift List</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <script src="assets/jquery-3.1.1.min.js"></script>
    <script src="assets/bootstrap.min.js"></script>
    <style>
        .header {
            text-align: center;
            color: green;
            font-size: 50px;
            width: 100%;
        }

        #logindiv {
            margin-left: auto;
            margin-right: auto;
            max-width: 300px;
            text-align: center;
        }

        #logindiv .panel-heading a {
            text-decoration: none;
            color: black;
            font-weight: bold;
            font-size: 15px;
        }

        #logindiv .panel-heading a.active {
            color: #029f5b;
            font-size: 18px;
        }

        #cpass, #rname, #scpass {
            display: none;
        }

        #regerror {
            color: red;
        }
    </style>
</head>
<body>
<div class="header">
    <img src="pics/tree.png" width="50px" height="50px">Family Gift List
</div>
<div class="container">
    <div id="logindiv" class="panel panel-default">
        <div class="panel-heading">
            <div class="row">
                <div class="col-xs-6">
                    <a href="#" class="active" id="login-form-link">Login</a>
                </div>
                <div class="col-xs-6">
                    <a href="#" id="register-form-link">Register</a>
                </div>
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-lg-12">
                    <form id="loginreg-form" style="display:block" action="index.php" method="post">
                        <input id="lrmode" type="hidden" name="mode" value="login"/>
                        <div class="form-group">
                            <input id="username" type="text" name="username" class="form-control" placeholder="Username"/>
                        </div>
                        <div class="form-group">
                            <input id="password" type="password" name="password" class="form-control" placeholder="Password"/>
                        </div>
                        <div id="cpass" class="form-group">
                            <input id="cpassword" type="password" class="form-control" placeholder="Confirm Password"/>
                        </div>
                        <div id="rname" class="form-group">
                            <input id="realname" type="text" name="realname" class="form-control" placeholder="Real Name"/>
                        </div>
                        <div id="scpass" class="form-group">
                            <input id="secretpass" type="password" name="secretpass" class="form-control" placeholder="Secret Password"/>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-6 col-sm-offset-3">
                                    <input type="submit" id="loginreg-submit" class="form-control btn btn-default" value="Login"/>
                                </div>
                            </div>
                        </div>
                        <div id="regerror"><?php if (isset($msg)) echo $msg; ?></div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var rfl = $("#register-form-link");
    var lfl = $("#login-form-link");
    var cpass = $("#cpass");
    var rname = $("#rname");
    var lrbut = $("#loginreg-submit");
    var scpass = $("#scpass");
    rfl.click(function() {
        rfl.addClass("active");
        lfl.removeClass("active");
        cpass.slideDown();
        rname.slideDown();
        scpass.slideDown();
        lrbut.val("Register");
        $("#lrmode").val("register");
    });
    lfl.click(function() {
        lfl.addClass("active");
        rfl.removeClass("active");
        cpass.slideUp();
        rname.slideUp();
        scpass.slideUp();
        lrbut.val("Login");
        $("#lrmode").val("login");
    });

    $("#loginreg-form").submit(function() {
        var isRegister = $("#logindiv").find(".panel-heading").find("a.active").text() == "Register";

        if (isRegister) {
            var user = $("#username").val();
            var pass = $("#password").val();
            var cpass = $("#cpassword").val();
            if (user.length == 0) {
                $("#regerror").html("Username cannot be blank");
                return false;
            }
            if (pass != cpass) {
                $("#regerror").html("Passwords do not match");
                return false;
            }
            if (pass.length < 1) {
                $("#regerror").html("Password cannot be blank");
                return false;
            }
        }
    });
</script>
</body>
</html>
<?php
$conn->close();
?>
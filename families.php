<?php
require('template.php');
require('connect.php');
assertLogin();

$result = $conn->query("SELECT * FROM users WHERE id=$uid");
$user = $result->fetch_assoc();

if ($user['familyid'] > 0) {
    $result = $conn->query("SELECT * FROM families WHERE id=" . $user['familyid']);
    $family = $result->fetch_assoc();
    $fid = $family['id'];
}

if (isset($_POST['mode']) && isset($_POST['password'])) {
    $mode = $conn->real_escape_string($_POST['mode']);
    $pass = $_POST['password'];
    if (strcmp($mode, 'create') == 0 && isset($_POST['famname'])) {
        $fname = $conn->real_escape_string($_POST['famname']);
        $result = $conn->query("SELECT * FROM families WHERE name='$fname'");
        if ($result->num_rows > 0) {
            $msg = 'Family Name taken';
        } else {
            $hashpass = password_hash($pass, PASSWORD_DEFAULT);
            $conn->query("INSERT INTO families(name, password, creatorid) VALUES('$fname', '$hashpass', $uid)");
            $conn->query("UPDATE users SET familyid=" . $conn->insert_id . " WHERE id=" . $uid);
        }
    } else if (strcmp($mode, 'join') == 0 && isset($_POST['famid'])) {
        $fid = intval($_POST['famid']);
        $result = $conn->query("SELECT * FROM families WHERE id=$fid");
        if ($result->num_rows == 0) {
            $msg = 'Family not found';
        } else {
            $fam = $result->fetch_assoc();
            if (password_verify($pass, $fam['password'])) {
                $conn->query("UPDATE users SET familyid=$fid WHERE id=$uid");
            } else {
                $msg = 'Invalid password';
            }
        }
    }

    header('Location: families.php');
}

if (isset($_POST['addfam']) && isset($_POST['famid'])) {
    $famid = intval($_POST['famid']);
    $result = $conn->query("SELECT * FROM familygroups WHERE familyid=$fid AND otherfamid=$famid");
    if ($result->num_rows == 0) {
        $conn->query("INSERT INTO familygroups(familyid,otherfamid) VALUES($fid, $famid)");
    }
}

if (isset($_POST['removefam']) && isset($_POST['famid'])) {
    $famid = intval($_POST['famid']);
    $result = $conn->query("DELETE FROM familygroups WHERE id=$famid");
}
?>
<html>
<head>
    <title>Families</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="assets/bootstrap.min.css">
    <script src="assets/jquery-3.1.1.min.js"></script>
    <script src="assets/bootstrap.min.js"></script>
    <style>
        #infowrap > div {
            max-width: 300px;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        #famdiv {
            max-width: 300px;
            text-align: center;
            margin-left: auto;
            margin-right: auto;
        }

        #famdiv .panel-heading a {
            text-decoration: none;
            color: black;
            font-weight: bold;
            font-size: 15px;
        }

        #famdiv .panel-heading a.active {
            color: #029f5b;
            font-size: 18px;
        }

        #fname, #cpass {
            display: none;
        }

        #regerror {
            color: red;
        }
    </style>
</head>
<body>
<div class="container">
    <?php echo navBar($_SERVER['REQUEST_URI'], false); ?>
    <div id="famwrapper"<?php if ($user['familyid'] > 0) echo ' style="display:none"' ?>>
        <div id="famdiv" class="panel panel-default">
            <div class="panel-heading">
                <div class="row">
                    <div class="col-xs-6">
                        <a href="#" class="active" id="join-link">Join Family</a>
                    </div>
                    <div class="col-xs-6">
                        <a href="#" id="create-link" onclick="tmp()">Create Family</a>
                    </div>
                </div>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-lg-12">
                        <form id="jc-form" style="display:block" action="families.php" method="post">
                            <input id="jcmode" type="hidden" name="mode" value="join"/>
                            <div id="fname" class="form-group">
                                <input id="famname" type="text" name="famname" class="form-control"
                                       placeholder="Family Name"/>
                            </div>
                            <div id="fselname" class="form-group">
                                <select class="form-control" name="famid">
                                    <?php
                                    $result = $conn->query("SELECT * FROM families");
                                    while ($row = $result->fetch_assoc()) {
                                        echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <input id="password" type="password" name="password" class="form-control"
                                       placeholder="Family Password"/>
                            </div>
                            <div id="cpass" class="form-group">
                                <input id="cpassword" type="password" class="form-control"
                                       placeholder="Confirm Password"/>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <input type="submit" id="jcbut" class="form-control btn btn-default"
                                               value="Join"/>
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
    <div id="faminfo"<?php if ($user['familyid'] == 0) echo ' style="display:none"' ?>>
        <div id="infowrap">
            <div class="panel-group">
                <div class="panel panel-default">
                    <div class="panel-heading">Your Family</div>
                    <div class="panel-body"><?php echo $family['name']; ?></div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Members</div>
                    <div class="panel-body">
                        <?php
                        if (isset($family)) {
                            $result = $conn->query("SELECT * FROM users WHERE id=" . $family['creatorid']);
                            $creator = $result->fetch_assoc();
                            $result = $conn->query("SELECT * FROM users WHERE familyid=" . $family['id'] . " AND id!=" . $creator['id']);
                            $b = $creator['realname'] . ', ';
                            while ($row = $result->fetch_assoc()) {
                                $b .= $row['realname'] . ', ';
                            }
                            echo substr($b, 0, strlen($b) - 2);
                        }
                        ?>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Families on your group list</div>
                    <div class="panel-body">
                        <form action="families.php" method="post">
                            <input type="hidden" name="removefam" value=""/>
                            <div class="form-group">
                                <select name="famid" class="form-control">
                                    <?php
                                    if (isset($family)) {
                                        $result = $conn->query("SELECT g.id, f.name FROM familygroups g JOIN families f ON g.otherfamid=f.id WHERE familyid=" . $family['id']);
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <input type="submit" class="form-control btn btn-default"
                                               value="Remove family"/>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="panel panel-default">
                    <div class="panel-heading">Families not on your group list</div>
                    <div class="panel-body">
                        <form action="families.php" method="post">
                            <input type="hidden" name="addfam" value=""/>
                            <div class="form-group">
                                <select name="famid" class="form-control">
                                    <?php
                                    if (isset($family)) {
                                        $result = $conn->query("SELECT * FROM families WHERE id NOT IN (SELECT otherfamid FROM familygroups WHERE familyid=$fid) AND id!=$fid");
                                        while ($row = $result->fetch_assoc()) {
                                            echo '<option value="' . $row['id'] . '">' . $row['name'] . '</option>';
                                        }
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <div class="row">
                                    <div class="col-sm-6 col-sm-offset-3">
                                        <input type="submit" class="form-control btn btn-default"
                                               value="Add family"/>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <input type="button" class="btn btn-default" style="margin: 10px" value="Leave Family" onclick="leaveFamily()"/>
            </div>
        </div>
    </div>
    <div align="center" style="font-size:30px">You will not be able to see their lists until they have added your family group as well</div>
</div>
<script>
    var joinlink = $("#join-link");
    var createlink = $("#create-link");
    createlink.click(function () {
        createlink.addClass("active");
        joinlink.removeClass("active");
        $("#cpass").slideDown();
        $("#jcbut").val("Create");
        $("#jcmode").val("create");
        $("#fname").css('display', 'block');
        $("#fselname").css('display', 'none');
    });

    joinlink.click(function () {
        joinlink.addClass("active");
        createlink.removeClass("active");
        $("#cpass").slideUp();
        $("#jcbut").val("Join");
        $("#jcmode").val("join");
        $("#fname").css('display', 'none');
        $("#fselname").css('display', 'block');
    });

    $("#jc-form").submit(function () {
        var head = $("#famdiv").find(".panel-heading");
        var links = head.find("a");
        var isCreate = links.index(head.find("a.active")) == 1;

        if (isCreate) {
            var famname = $("#famname").val();
            var pass = $("#password").val();
            var cpass = $("#cpassword").val();
            if (famname.length == 0) {
                $("#regerror").html("Family Name cannot be blank");
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

    function leaveFamily() {
        var yesno = confirm("Are you sure you want to leave this family?");
        if (!yesno)return;
        $.ajax({
            url: "userprocess.php",
            type: "post",
            data: {
                mode: 5
            }
        }).done(function(resp) {
            if (resp.length > 0) {
                alert(resp);
            }
            window.location.reload();
        });
    }
</script>
</body>
</html>
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
} else {
    header('Location: families.php');
}
?>
    <html>
    <head>
        <title>Family Gift List</title>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="assets/bootstrap.min.css">
        <script src="assets/jquery-3.1.1.min.js"></script>
        <script src="assets/bootstrap.min.js"></script>
        <style>

            #listsidebar {
                margin-bottom: 10px;
            }

            #listcontent {
                height: calc(100% - 100px);
                overflow-y: auto;
            }

            #userlist a {
                background: inherit;
                background-color: inherit;
            }

            .mylink {
                color: #0000EE !important;
                text-decoration: underline !important;
                cursor: pointer;
                display: inline-block;
            }

            #userlist a:not(.hoverblue) {
                cursor: default;
            }

            #additemerr {
                color: red;
                padding-top: 10px;
            }

            .listbtns img {
                width: 30px;
                height: auto;
                margin-left: 10px;
                cursor: pointer;
            }

            #editwindow {
                z-index: 3;
                text-align: center;
                position: absolute;
                max-width: 400px;
                width: 100%;
                left: calc(50% - 200px);
                top: -340px;
                max-height: 340px;

                -webkit-transition: top 1s ease 0s;
                transition: top 1s ease 0s;
            }

            #totalcover {
                position: absolute;
                left: 0;
                top: -100%;
                width: 100%;
                height: 100%;
                background: gray;
                z-index: 2;
                opacity: 0;

                -webkit-transition: opacity 1s ease 0s;
                transition: opacity 1s ease 0s;
            }

            #totalcover.isvisible {
                opacity: 0.5;
            }

            .itemname {
                word-break: keep-all;
                overflow: auto;
            }

            .btndisabled {
                cursor: default !important;
            }
        </style>
    </head>
    <body>
    <?php
    echo '<script>var myid=' . $uid . ';</script>';
    ?>
    <div class="container">
        <?php echo navBar($_SERVER['REQUEST_URI'], true); ?>
        <div id="listsidebar">
            <?php
            //your stuff
            echo '
            <select class="form-control" onchange="loadList(this.value)">
                <option value="olditems">My Old Items</option>
                <option value="mytaken">My Taken Items</option>
                <option value="' . $uid . '" selected>My List</option>';

            //your family's lists
            $result2 = $conn->query("SELECT * FROM users WHERE familyid=$fid AND id!=" . $uid);
            while ($row2 = $result2->fetch_assoc()) {
                echo '<option value="' . $row2['id'] . '">' . $row2['realname'] . '</option>';
            }

            //other family's lists
            $result = $conn->query("SELECT * FROM familygroups g WHERE g.otherfamid IN (SELECT familyid FROM familygroups g2 WHERE g2.otherfamid=g.familyid) AND g.familyid=$fid");
            while ($row = $result->fetch_assoc()) {
                $result2 = $conn->query("SELECT * FROM users WHERE familyid=" . $row['otherfamid'] . " AND id!=" . $uid);
                while ($row2 = $result2->fetch_assoc()) {
                    echo '<option value="' . $row2['id'] . '">' . $row2['realname'] . '</option>';
                }
            }

            echo '
            </select>
            ';
            ?>
        </div>
        <div id="listcontent"></div>
        <div id="editwindow" class="panel panel-default">
            <div class="panel-body">
                <div class="form-group">
                    <label for="item">Item:</label>
                    <textarea class="form-control" rows="5" id="edititem"></textarea>
                </div>
                <div class="form-group">
                    <label for="link">Link (optional):</label>
                    <input type="text" class="form-control" id="editlink"/>
                </div>
                <div class="form-group">
                    <label>Multiple OK: <input type="checkbox" id="editmultipleok"/></label>
                </div>
                <input type="button" class="btn btn-default" value="Update Item" onclick="confirmEdit()"/>
                <input type="button" class="btn btn-default" value="Cancel" onclick="cancelEdit()"/>
                <div id="additemerr"></div>
            </div>
        </div>
    </div>
    <div id="totalcover"></div>
    <script>
        var links = $("#listsidebar").find("a");
        links.click(function () {
            links.removeClass("active");
            $(this).addClass("active");
            var id = $(this).attr('name');

            loadList(id);
        });

        function loadList(uid) {
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 0,
                    userid: uid
                }
            }).done(function (resp) {
                $("#listcontent").html(resp);
            });
        }
        loadList(myid);

        function addItem() {
            var item = $("#item").val();

            if (item.length == 0) {
                $("#additemerr").html("Item cannot be blank");
                return;
            }

            var link = $("#link").val();
            var multipleok = $("#multipleok").prop('checked');
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 1,
                    item: item,
                    link: link,
                    multipleok: multipleok ? 1 : 0
                }
            }).done(function (resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                loadList(myid);
            });
        }

        function deleteItem(iid) {
            var doit = confirm("Are you sure you want to delete this item?");
            if (!doit) return;
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 2,
                    itemid: iid
                }
            }).done(function (resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                loadList(myid);
            });
        }

        var curEditItem;
        function editItem(iid) {
            curEditItem = iid;
            var editwin = $("#editwindow");
            var bounds = editwin[0].getBoundingClientRect();
            editwin.addClass("isvisible");
            doEditTop();
            var cover = $("#totalcover");
            cover.addClass("isvisible");
            cover.css('top', '0');

            var itm = $("#item" + iid);
            var item = itm.find(".itemname");
            var multok = itm.find('.itemmultipleok').html();
            $("#edititem").val(item.text());
            $("#editmultipleok").prop('checked', multok == 1);
            var elink = $("#editlink");
            if (item.hasClass("mylink")) {
                var onc = item.attr('onclick');
                var ind = onc.indexOf("open(");
                var end = onc.indexOf("',");
                elink.val(onc.substring(ind + 6, end));
            } else {
                elink.val('');
            }
        }

        function cancelEdit() {
            var editwin = $("#editwindow");
            var bounds = editwin[0].getBoundingClientRect();
            editwin.removeClass("isvisible");
            editwin.css('top', '-' + bounds.height + "px");
            $("#totalcover").removeClass("isvisible");
            setTimeout(function () {
                $("#totalcover").css('top', '-100%');
            }, 1000);
        }

        function confirmEdit() {
            var editwin = $("#editwindow");
            var bounds = editwin[0].getBoundingClientRect();
            editwin.removeClass("isvisible");
            editwin.css('top', '-' + bounds.height + "px");
            $("#totalcover").removeClass("isvisible");
            setTimeout(function () {
                $("#totalcover").css('top', '-100%');
            }, 1000);

            var multok = $("#editmultipleok").prop('checked');
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 3,
                    itemid: curEditItem,
                    name: $("#edititem").val(),
                    link: $("#editlink").val(),
                    multipleok: multok ? 1 : 0
                }
            }).done(function (resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                loadList(myid);
            });
        }

        function takeItem(iid) {
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 4,
                    itemid: iid
                }
            }).done(function(resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                var curlist = $('#listsidebar').find('select').find(':selected').val();
                loadList(curlist);
            });
        }

        $(window).resize(function () {
            var editwin = $("#editwindow");
            var bounds = editwin[0].getBoundingClientRect();
            editwin.css('left', ($(window).width() - bounds.width) / 2 + "px");
            if (editwin.hasClass("isvisible")) {
                doEditTop();
            }
        });

        function doEditTop() {
            var editwin = $("#editwindow");
            var bounds = editwin[0].getBoundingClientRect();
            editwin.css('top', ($(window).height() - bounds.height) / 2 + "px");
            editwin.css('left', ($(window).width() - bounds.width) / 2 + "px");
        }

        function moveOld(oid) {
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 6,
                    itemid: oid
                }
            }).done(function(resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                var curlist = $("#listsidebar").find("a.active").attr('name');
                loadList(curlist);
            });
        }

        function removeOld(oid) {
            $.ajax({
                url: "userprocess.php",
                type: "post",
                data: {
                    mode: 7,
                    itemid: oid
                }
            }).done(function(resp) {
                if (resp.length > 0) {
                    alert(resp);
                }
                var curlist = $("#listsidebar").find("a.active").attr('name');
                loadList(curlist);
            });
        }

        $('#listcontent').css('height', 'calc(100% - ' + ($('#listsidebar').height() + 20) + 'px)');
    </script>
    </body>
    </html>
<?php
$conn->close();
?>
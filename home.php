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
                position: absolute;
                left: 10px;
                top: auto;
                width: 210px;
                border-right: 1px solid black;
                padding-right: 10px;
                height: calc(100% - 65px);
                overflow-y: auto;
            }

            #listcontent {
                position: absolute;
                left: 220px;
                top: auto;
                width: calc(100% - 230px);
                height: calc(100% - 65px);
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

            #tmpiframe {
                display: none;
            }
        </style>
    </head>
    <body>
    <script>
        if($(window).width() < 500 || isMobile()) {
            window.location.href = 'mobile.php';
        }

        function isMobile() {
            var check = false;
            (function(a){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4))) check = true;})(navigator.userAgent||navigator.vendor||window.opera);
            return check;
        }
    </script>
    <?php
    echo '<script>var myid=' . $uid . ';</script>';
    ?>
    <div class="container">
        <?php echo navBar($_SERVER['REQUEST_URI'], false); ?>
        <div id="listsidebar">
            <div class="list-group">
                <?php
                //your stuff
                echo '
                <a href="#" class="list-group-item" name="olditems">My Old Items</a>
                <a href="#" class="list-group-item" name="mytaken">My Taken Items</a>
                <a href="#" class="list-group-item active" name="' . $uid . '">My List</a>';

                //your family's lists
                $result2 = $conn->query("SELECT * FROM users WHERE familyid=$fid AND id!=" . $uid);
                while ($row2 = $result2->fetch_assoc()) {
                    echo '<a href="#" class="list-group-item" name="' . $row2['id'] . '">' . $row2['realname'] . '</a>';
                }

                //other family's lists
                $result = $conn->query("SELECT * FROM familygroups g WHERE g.otherfamid IN (SELECT familyid FROM familygroups g2 WHERE g2.otherfamid=g.familyid) AND g.familyid=$fid");
                while ($row = $result->fetch_assoc()) {
                    $result2 = $conn->query("SELECT * FROM users WHERE familyid=" . $row['otherfamid'] . " AND id!=" . $uid);
                    while ($row2 = $result2->fetch_assoc()) {
                        echo '<a href="#" class="list-group-item" name="' . $row2['id'] . '">' . $row2['realname'] . '</a>';
                    }
                }
                ?>
            </div>
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
    <div id="tmpiframe"></div>
    <script>
        var links = $("#listsidebar").find("a");
        links.click(function () {
            links.removeClass("active");
            $(this).addClass("active");
            var id = $(this).attr('name');

            loadList(id);
        });

        var curlist = -1;

        function loadList(uid) {
            curlist = uid;
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
                var curlist = $("#listsidebar").find("a.active").attr('name');
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
            })
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

        function printlist() {
            $('#tmpiframe').html('<iframe src="printlist.php?uid=' + curlist + '"></iframe>');
        }

        $('body').on('readytoprint', function() {
            $('#tmpiframe').find('iframe').get(0).contentWindow.print();
        });
    </script>
    </body>
    </html>
<?php
$conn->close();
?>
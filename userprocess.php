<?php
require('connect.php');
session_start();

if (!isset($_SESSION['id'])) {
    exit('Please login first');
}

$uid = $_SESSION['id'];

if (!isset($_POST['mode'])) {
    exit('No mode provided');
}

$mode = $_POST['mode'];

$m = date('n');
$d = date('j');
$isdecember = ($m == 12 && $d >= 1);

//Get list display
if ($mode == 0) {
    if (!isset($_POST['userid'])) {
        exit('Not enough info provided');
    }
    $userid = $_POST['userid'];
    $isprint = false;
    if (isset($_POST['print'])) $isprint = true;

    if (!$isprint) {
        echo '
    <div id="printbutton" style="text-align:center;margin-bottom:10px">
        <input type="button" value="Print this list" class="btn btn-info" onclick="printlist()" />
    </div>
    ';
    }

    /**
     * MY TAKEN ITEMS
     */
    if (strcmp($userid, 'mytaken') == 0) {
        $result = $conn->query("SELECT i.id, i.name, i.multipleok, i.link, u.realname, t.userid AS taker FROM items i JOIN taken t ON i.id=t.itemid JOIN users u ON u.id=i.userid WHERE t.userid=$uid");
        if ($result->num_rows == 0) {
            echo '
            <div class="panel panel-default">
                <div class="panel-body">
                    You have not taken any items
                </div>
            </div>
            ';
            exit('');
        }
        echo '
        <div class="list-group" id="userlist" style="margin-left: auto; margin-right: auto; max-width: 700px;">';
        while ($row = $result->fetch_assoc()) {
            $name = nl2br(htmlspecialchars($row['name']));
            echo '
            <div class="list-group-item" class="linkwrapdiv" id="item' . $row['id'] . '">
                <div class="row">
                    <div class="col-sm-6">';
            if (strlen($row['link']) > 0) {
                echo '<div class="mylink itemname" onclick="window.open(\'' . $row['link'] . '\', \'_blank\');">' . $name . '</div>';
            } else {
                echo '<div class="itemname">' . $name . '</div>';
            }
            $extra = '';
            if ($row['multipleok'] == 1) {
                $numtaken = $conn->query("SELECT * FROM taken WHERE itemid=" . $row['id']);
                $extra = '<br>' . $numtaken->num_rows . ' Taken';
            }
            echo '
                    </div>
                    <div class="col-sm-3">
                        ' . $row['realname'] . $extra . '
                    </div>
                    <div class="col-sm-3 listbtns" style="text-align: right">';
            if ($userid == $uid) {
                echo '
                        <img src="pics/btn_edit.png" onclick="editItem(' . $row['id'] . ')"/>
                        <img src="pics/btn_delete.png" onclick="deleteItem(' . $row['id'] . ')"/>
                ';
            } else {
                $extra = $isdecember ? '' : ' disabled';
                $tid = intval($row['taker']);
                if ($tid == 0) {
                    echo '<input type="button" class="btn btn-default" value="Take Item" onclick="takeItem(' . $row['id'] . ')"' . $extra . '/>';
                } else if ($tid == $uid) {
                    echo '<input type="button" class="btn btn-default" value="Untake Item" onclick="takeItem(' . $row['id'] . ')"' . $extra . '/>';
                } else {
                    echo '<input type="button" class="btn btn-default" value="Taken" disabled/>';
                }
            }
            echo '
                    </div>
                </div>
            </div>';
        }
        echo '
        </div>
        ';
        exit('');
    } /**
     * MY OLD ITEMS
     */
    else if (strcmp($userid, 'olditems') == 0) {
        $result = $conn->query("SELECT * FROM olditems WHERE userid=$uid");
        if ($result->num_rows == 0) {
            echo '
            <div class="panel panel-default">
                <div class="panel-body">
                    You do not have any old items
                </div>
            </div>
            ';
        } else {
            echo '
            <div class="list-group" id="userlist" style="margin-left: auto; margin-right: auto; max-width: 700px;">';
            while ($row = $result->fetch_assoc()) {
                $name = nl2br(htmlspecialchars($row['name']));
                echo '
                <div class="list-group-item" class="linkwrapdiv" id="item' . $row['id'] . '">
                    <div class="row">
                        <div class="col-sm-6">';
                if (strlen($row['link']) > 0) {
                    echo '<div class="mylink itemname" onclick="window.open(\'' . $row['link'] . '\', \'_blank\');">' . $name . '</div>';
                } else {
                    echo '<div class="itemname">' . $name . '</div>';
                }
                echo '
                    </div>
                    <div class="col-sm-6 listbtns" style="text-align: right">
                        <input type="button" class="btn btn-default" value="Add to this year\'s list" onclick="moveOld(' . $row['id'] . ')"/>
                        <input type="button" class="btn btn-default" value="Delete" onclick="removeOld(' . $row['id'] . ')"/>
                    </div>
                </div>
            </div>';
            }
            echo '
        </div>
        ';
        }
        exit('');
    }

    /**
     * ADD ITEM PANEL
     */
    if ($userid == $uid && !$isprint) {
        echo '
        <div id="additempanel" class="panel panel-default" style="margin-left: auto; margin-right: auto; text-align: center; max-width: 400px;">
            <div class="panel-body">
                <div class="form-group">
                    <label for="item">Item:</label>
                    <textarea class="form-control" rows="5" id="item"></textarea>
                </div>
                <div class="form-group">
                    <label for="link">Link (optional):</label>
                    <input type="text" class="form-control" id="link"/>
                </div>
                <div class="form-group">
                    <label>Multiple OK: <input type="checkbox" id="multipleok"/></label>
                </div>
                <input type="button" class="btn btn-default" value="Add Item" onclick="addItem()"/>
                <div id="additemerr"></div>
            </div>
        </div>
        ';
    }

    $itemstodo = array();
    if ($uid == $userid) {
        $result = $conn->query("SELECT * FROM items WHERE userid=$uid");
        while ($row = $result->fetch_assoc()) {
            array_push($itemstodo, $row);
        }
    } else {
        //$result = $conn->query("SELECT i.name, i.link, i.id, i.multipleok, t.userid AS taker FROM items i LEFT JOIN taken t ON i.id=t.itemid WHERE i.userid=$userid ORDER BY CASE WHEN t.userid=$uid THEN 0 ELSE 1 END ASC, CASE WHEN t.userid<>$uid THEN 1 ELSE 0 END ASC");
        $result = $conn->query("SELECT i.id, i.name, i.link, i.multipleok, t.userid AS taker FROM items i LEFT JOIN taken t ON i.id=t.itemid WHERE i.userid=$userid");
        $items = array();
        $mytakenids = array();
        while ($row = $result->fetch_assoc()) {
            array_push($items, $row);
            if ($row['taker'] == $uid) {
                array_push($mytakenids, $row['id']);
            }
        }

        $mytaken = array();
        $available = array();
        $othertaken = array();
        foreach ($items as $row) {
            if ($row['taker'] == $uid) {
                array_push($mytaken, $row);
            } else if ($row['taker'] > 0 && $row['multipleok'] == 0) {
                array_push($othertaken, $row);
            } else if (!in_array($row['id'], $mytakenids)) {
                $already = false;
                for ($i = 0; $i < count($available); $i++) {
                    if ($available[$i]['id'] == $row['id'])$already = true;
                }
                if (!$already)array_push($available, $row);
            }
        }
        $itemstodo = array_merge($itemstodo, $mytaken);
        $itemstodo = array_merge($itemstodo, $available);
        $itemstodo = array_merge($itemstodo, $othertaken);
    }
    if (count($itemstodo) == 0) {
        echo '
        <div class="panel panel-default">
            <div class="panel-body">
                No items found for this list
            </div>
        </div>
        ';
    } else {
        echo '
        <div class="list-group" id="userlist" style="margin-left: auto; margin-right: auto; max-width: 700px;">';
        foreach ($itemstodo as $row) {
            $name = nl2br(htmlspecialchars($row['name']));
            echo '
            <div class="list-group-item" class="linkwrapdiv" id="item' . $row['id'] . '">
                <span class="itemmultipleok" style="display:none">' . $row['multipleok'] . '</span>
                <div class="row">
                    <div class="col-sm-7">';
            if (strlen($row['link']) > 0) {
                echo '<div class="mylink itemname" onclick="window.open(\'' . $row['link'] . '\', \'_blank\');">' . $name . '</div>';
            } else {
                echo '<div class="itemname">' . $name . '</div>';
            }
            echo '
                    </div>
                    <div class="col-sm-2">';
            if ($userid != $uid && $row['multipleok'] == 1) {
                $numtaken = $conn->query("SELECT * FROM taken WHERE itemid=" . $row['id']);
                echo $numtaken->num_rows . ' Taken';
            }
            echo '
                    </div>
                    <div class="col-sm-3 listbtns" style="text-align: right">';
            if ($userid == $uid) {
                echo '
                        <img src="pics/btn_edit.png" onclick="editItem(' . $row['id'] . ')"/>
                        ';
                if ($isdecember) {
                    echo '<img src="pics/btn_antidelete.png" class="btndisabled" />';
                } else {
                    echo '<img src="pics/btn_delete.png" onclick="deleteItem(' . $row['id'] . ')"/>';
                }
            } else {
                $tid = intval($row['taker']);
                $extra = $isdecember ? '' : ' disabled';
                if ($tid == $uid) {
                    echo '<input type="button" class="btn btn-default" value="Untake Item" onclick="takeItem(' . $row['id'] . ')"' . $extra . '/>';
                } else if ($tid == 0 || $row['multipleok'] == 1) {
                    echo '<input type="button" class="btn btn-default" value="Take Item" onclick="takeItem(' . $row['id'] . ')"' . $extra . '/>';
                } else {
                    echo '<input type="button" class="btn btn-default" value="Taken" disabled/>';
                }
            }
            echo '
                    </div>
                </div>
            </div>';
        }
        echo '
        </div>
        ';
    }
} //ADD ITEM
else if ($mode == 1) {
    if (!isset($_POST['item']) || !isset($_POST['link']) || !isset($_POST['multipleok'])) {
        exit('Not enough info provided');
    }
    $item = $conn->real_escape_string($_POST['item']);
    $link = $conn->real_escape_string($_POST['link']);
    $mult = intval($_POST['multipleok']);
    $conn->query("INSERT INTO items(name, link, userid, multipleok) VALUES('$item', '$link', $uid, $mult)");
} //DELETE ITEM
else if ($mode == 2) {
    if (!isset($_POST['itemid'])) {
        exit('Not enough info provided');
    }

    if ($isdecember) {
        exit('You cannot delete items throughout december');
    }

    $itemid = $_POST['itemid'];
    $conn->query("DELETE FROM items WHERE id=$itemid AND userid=$uid");
} //UPDATE ITEM
else if ($mode == 3) {
    if (!isset($_POST['itemid']) || !isset($_POST['name']) || !isset($_POST['link']) || !isset($_POST['multipleok'])) {
        exit('Not enough info provided');
    }
    $itemid = $_POST['itemid'];
    $name = $_POST['name'];
    $link = $_POST['link'];
    $multok = $_POST['multipleok'];

    $conn->query("UPDATE items SET name='$name', link='$link', multipleok=$multok WHERE id=$itemid AND userid=$uid");
} //TAKE OR UNTAKE ITEM
else if ($mode == 4) {
    if (!isset($_POST['itemid'])) {
        exit('Not enough info provided');
    }

    if (!$isdecember) {
        exit('Please wait until december to take items');
    }

    $itemid = $_POST['itemid'];

    $result = $conn->query("SELECT * FROM taken WHERE itemid=$itemid AND userid=$uid");
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $conn->query("DELETE FROM taken WHERE id=" . $item['id']);
        exit('');
    }
    $result = $conn->query("SELECT i.multipleok FROM taken t JOIN items i ON t.itemid=i.id WHERE itemid=$itemid");
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        if ($item['multipleok'] == 0) {
            exit('Item is already taken by someone else');
        }
    }

    $conn->query("INSERT INTO taken(itemid, userid) VALUES($itemid, $uid)");
} //LEAVE FAMILY
else if ($mode == 5) {
    $conn->query("UPDATE users SET familyid=0 WHERE id=$uid");
} //MOVE OLD ITEM
else if ($mode == 6) {
    if (!isset($_POST['itemid'])) {
        exit('Not enough info provided');
    }
    $itemid = $_POST['itemid'];
    $result = $conn->query("SELECT * FROM olditems WHERE id=$itemid AND userid=$uid");
    if ($result->num_rows > 0) {
        $item = $result->fetch_assoc();
        $name = $item['name'];
        $link = $item['link'];
        $multipleok = $item['multipleok'];
        $conn->query("INSERT INTO items(name, link, userid, multipleok) VALUES('$name', '$link', $uid, $multipleok)");
        $conn->query("DELETE FROM olditems WHERE id=$itemid");
    }
} //REMOVE FROM OLD LIST
else if ($mode == 7) {
    if (!isset($_POST['itemid'])) {
        exit('Not enough info provided');
    }
    $itemid = $_POST['itemid'];
    $conn->query("DELETE FROM olditems WHERE id=$itemid");
}
$conn->close();
?>

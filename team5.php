<?php
require "config.php";

$q_1 = "SELECT salesdetail.* FROM salesdetail INNER JOIN titles ON salesdetail.title_id=titles.title_id";
echo $q_1 . "<br>";
$r_1 = $db->query($q_1);


if ($r_1->num_rows > 0) {
    // output data of each row
    while($row = $r_1->fetch_assoc()) {
        echo "stor_id: " . $row["stor_id"]. " | ord_num: " . $row["ord_num"]. " | title_id: " . $row["title_id"]. " | qty: " . $row["qty"].  " | discount: " . $row["discount"].  "<br>";
    }
} else {
    echo "0 results";
}

$db->close();
?>

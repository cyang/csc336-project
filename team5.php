<?php

$host = "134.74.126.107";
$username = "F16336cyang";
$password = "23158294";
$team_database = "F16336team5";
// Connection
$db = new mysqli($host, $username, $password, $team_database);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}

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
echo "<br>";
$q_2 = "SELECT stor_id, ord_num, titles.title_id, qty*titles.price*discount/100 AS total FROM salesdetail INNER JOIN titles ON salesdetail.title_id=titles.title_id";
echo $q_2 . "<br>";
$r_2 = $db->query($q_2);
if ($r_2->num_rows > 0) {
    // output data of each row
    while($row = $r_2->fetch_assoc()) {
        echo "stor_id: " . $row["stor_id"]. " | ord_num: " . $row["ord_num"]. " | title_id: " . $row["title_id"]. " | total: " . $row["total"]. "<br>";
    }
} else {
    echo "0 results";
}
echo "<br>";
$q_3 = "SELECT SUM(qty*titles.price*discount/100) as gross_revenue from salesdetail INNER JOIN titles ON salesdetail.title_id=titles.title_id;";
echo $q_3 . "<br>";
$r_3 = $db->query($q_3);
if ($r_3->num_rows > 0) {
    // output data of each row
    while($row = $r_3->fetch_assoc()) {
        echo "gross_revenue: " . $row["gross_revenue"]. "<br>";
    }
} else {
    echo "0 results";
}
echo "<br>";
$q_4 = "SELECT stor_id, ord_num, SUM(qty*titles.price*discount/100) as gross_revenue FROM salesdetail INNER JOIN titles ON salesdetail.title_id=titles.title_id GROUP BY ord_num;";
echo $q_4 . "<br>";
$r_4 = $db->query($q_4);
if ($r_4->num_rows > 0) {
    // output data of each row
    while($row = $r_4->fetch_assoc()) {
        echo "stor_id: " . $row["stor_id"]. " | ord_num: " . $row["ord_num"]. " | gross_revenue: " . $row["gross_revenue"]. "<br>";
    }
} else {
    echo "0 results";
}

$db->close();
?>

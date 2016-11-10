<?php
require "config.php";

echo "GET DISCOUNT TO THE STORE BASED ON ORDER SIZE:<br>";
$q_1 = "select ord.ord_num, ord.stor_id, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY ord_num) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num;";
$r_1 = $db->query($q_1);


if ($r_1->num_rows > 0) {
    // output data of each row
    while($row = $r_1->fetch_assoc()) {
        echo "ord_num: " . $row["ord_num"]. " | stor_id: " . $row["stor_id"]. " | discount: " . $row["discount"]. "<br>";
    }
} else {
    echo "0 results";
}

echo "<br>GET DISCOUNT TO THE STORE BASED ON TOTAL QUANTITY OF ALL ORDERS:<br>";
$q_2 = "select ord.stor_id, ord.qtyTotal, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY stor_id) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num;";
$r_2 = $db->query($q_2);

if ($r_2->num_rows > 0) {
    // output data of each row
    while($row = $r_2->fetch_assoc()) {
        echo "stor_id: " . $row["stor_id"]. " | qtyTotal: " . $row["qtyTotal"]. " | discount: " . $row["discount"]. "<br>";
    }
} else {
    echo "0 results";
}

echo "<br>UPDATE TOTAL SALES IN TITLES TABLE:<br>";
$q_3 = "UPDATE titles,(
    SELECT salesdetail.title_id, SUM(qty) as total_sales
    FROM salesdetail
    GROUP BY title_id) as ord
set titles.total_sales=ord.total_sales
where titles.title_id=ord.title_id;";
$r_3 = $db->query($q_3);

if ($r_3) {
	echo "Update success<br>";
} else {
	echo "Update fail<br>";
}

echo "<br>CALCULATE GROSS REVENUE FROM SALES:<br>";
$q_4 = "select titles.title_id, titles.price, sum(disc.qtyTotal) as totalQuantity, sum((titles.price*disc.qtyTotal)*(1-(disc.discount/100))) as grossRevenue
from salesdetail
left join titles
ON titles.title_id=salesdetail.title_id
left join (
	select ord.ord_num, ord.stor_id, ord.qtyTotal, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY ord_num) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num) as disc
on disc.ord_num=salesdetail.ord_num
group by titles.title_id;";
$r_4 = $db->query($q_4);

if ($r_4->num_rows > 0) {
    // output data of each row
    while($row = $r_4->fetch_assoc()) {
        echo "title_id: " . $row["title_id"]. " | price: " . $row["price"]. " | totalQuantity: " . $row["totalQuantity"]. " | grossRevenue: " . $row["grossRevenue"]. "<br>";
    }
} else {
    echo "0 results";
}



echo "<br>CALCULATE AUTHOR ROYALTY FOR ALL SALES:<br>";
$q_5 = "select titleauthor.au_id, revenues.grossRevenue*(titleauthor.royaltyper/100) as totalPay
from titleauthor
left join
(select titles.title_id, titles.price, sum(disc.qtyTotal) as totalQuantity, sum((titles.price*disc.qtyTotal)*(1-(disc.discount/100))) as grossRevenue
	from salesdetail
	left join titles
	ON titles.title_id=salesdetail.title_id
	left join (
	select ord.ord_num, ord.stor_id, ord.qtyTotal, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY ord_num) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num) as disc
	on disc.ord_num=salesdetail.ord_num
	group by titles.title_id) as revenues
on titleauthor.title_id=revenues.title_id;";
$r_5 = $db->query($q_5);

if ($r_5->num_rows > 0) {
    // output data of each row
    while($row = $r_5->fetch_assoc()) {
        echo "au_id: " . $row["au_id"]. " | totalPay: " . $row["totalPay"]. "<br>";
    }
} else {
    echo "0 results";
}


$db->close();
?>

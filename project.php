<?php
require "config.php";
// Connection
$db = new mysqli($host, $username, $password, $team_database);
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
} 
$q_1 = "SELECT ord.ord_num, ord.stor_id, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY ord_num) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num;";
$r_1 = $db->query($q_1);

$q_2 = "SELECT ord.stor_id, ord.qtyTotal, coalesce(max(discounts.discount),0) as discount
	from(SELECT *, SUM(qty) as qtyTotal FROM salesdetail GROUP BY stor_id) as ord
	left JOIN discounts
	ON discounts.stor_id=ord.stor_id and discounts.lowqty <= ord.qtyTotal and discounts.highqty >= ord.qtyTotal
	GROUP BY ord.ord_num;";
$r_2 = $db->query($q_2);

$q_3 = "UPDATE titles,(
    SELECT salesdetail.title_id, SUM(qty) as total_sales
    FROM salesdetail
    GROUP BY title_id) as ord
set titles.total_sales=ord.total_sales
where titles.title_id=ord.title_id;";
$r_3 = $db->query($q_3);

$q_4 = "SELECT titles.title_id, titles.price, sum(disc.qtyTotal) as totalQuantity, sum((titles.price*disc.qtyTotal)*(1-(disc.discount/100))) as grossRevenue
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

$q_5 = "SELECT titleauthor.au_id, revenues.grossRevenue*(titleauthor.royaltyper/100) as totalPay
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
?>

<!DOCTYPE HTML>
<html>
<head>
	<title> Group 5 </title>
	<meta charset="UTF-8">
	<style type="text/css">
		tr:nth-child(even) {background-color: #f2f2f2}
		table, th, td {
   			border: 1px solid black;
		}
		caption{
			font-weight: bold;
			font-size: 20px;
		}
	</style>
</head>
<body>
	<table align="center" style="width:70%">
		<tr>
			<caption>Discount to stores based on Order Size </caption>
			<th>Order Number</th>
			<th>Store ID</th>
			<th>Discount</th>
		</tr>
		<?php while($row = mysqli_fetch_array($r_1)):;?>
			<tr>
				<td><?php echo $row[0];?></td>
				<td><?php echo $row[1];?></td>
				<td><?php echo $row[2];?></td>
			</tr>
		<?php endwhile;?>
	</table>
	<br>
	<br>
	<table align="center" style="width: 70%">
		<tr>
			<caption>Discount to the stores based on total quantity of all orders</caption>
			<th>Store ID </th>
			<th>Quantity Total</th>
			<th>Discount</th>
		</tr>
		<?php while($row2 = mysqli_fetch_array($r_2)):;?>
			<tr>
				<td><?php echo $row2[0];?></td>
				<td><?php echo $row2[1];?></td>
				<td><?php echo $row2[2];?></td>
			</tr>
		<?php endwhile;?>
	</table>
	<br>
	<br>
	<table align="center" style="width: 70%">
	<caption>Update Total Sales in Titles table</caption>
	<tr>
		<td>
			<?php if($r_3){
				echo "Update Successful <br>";

			}
			else{
				echo "Update Fail <br>";
			}
			?>
		</td>
	</tr>
	</table>
	<br>
	<br>
	<table align="center" style="width: 70%">
		<tr>
		<caption>Calculate Gross Revenue from Sales</caption>
			<th>Title ID </th>
			<th>Price</th>
			<th>Total Quantity</th>
			<th>Gross Revenue</th>
		</tr>
		<?php while($row4 = mysqli_fetch_array($r_4)):;?>
			<tr>
				<td><?php echo $row4[0];?></td>
				<td><?php echo $row4[1];?></td>
				<td><?php echo $row4[2];?></td>
				<td><?php echo $row4[3];?></td>
			</tr>
		<?php endwhile;?>
	</table>
	<br>
	<br>
	<table align="center" style="width: 70%">
		<tr>
		<caption>Calculate Author Royalty for all Sales</caption>
			<th>Author ID </th>
			<th>Total Pay</th>
		</tr>
		<?php while($row5 = mysqli_fetch_array($r_5)):;?>
			<tr>
				<td><?php echo $row5[0];?></td>
				<td><?php echo $row5[1];?></td>
			</tr>
		<?php endwhile;?>
	</table>
</body>
</html>


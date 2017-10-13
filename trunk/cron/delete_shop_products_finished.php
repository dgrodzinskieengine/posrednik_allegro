<?php

require_once(dirname(__FILE__)."/../lib/lib.php");

$_rs = mysql_query('SELECT spf_id FROM shop_products_finished WHERE insert_timestamp < now() - INTERVAL 30 day', $db->conn);
if ($_rs)
{
	$i = 0;
	while ($row = mysql_fetch_assoc($_rs))
	{
		$sql = "DELETE LOW_PRIORITY FROM shop_products_finished WHERE spf_id = '{$row['spf_id']}';";
#		echo $sql."\n";
		mysql_query($sql);
		$i++;
#		if ($i%1000 == 0) { echo "sleep()\n"; usleep(500000); }
		usleep(4000);
	}
}
else
	echo mysql_error($db->conn)." ($query)";


mysql_query("OPTIMIZE TABLE shop_products_finished;");
# mysql_query("ALTER TABLE shop_products_finished ADD spf_error INT NOT NULL DEFAULT '0' AFTER spf_used;");
# INSERT INTO shop_products_finished SELECT * FROM shop_products_finished WHERE insert_timestamp > now() - INTERVAL 30 day

<?php 

function updateCallList($result_list,$mysql)
{
	$query = "INSERT INTO const_calls (iConstituentID, call_date, call_phone)
VALUES";
	$temp = "";

	foreach ($result_list as $key=>$val)
	{
		if ($temp != "") 
			$temp .= ",";
		$temp .= "('".$result_list[$key]["const_key"]."','".$result_list[$key]["last_date"]."','".$result_list[$key]["BestPhone"]."')";
	}
	if ($temp!="")
	{	
		$temp.=";";
		$query.=$temp;
		try {
			$mysql->exec($query);
		} catch (PDOException  $e ){
			echo "Error: ".$e;
		}
		//print $query;
	}
	
}

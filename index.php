<?php
	//phpinfo();

	require 'PHPMailerAutoload.php';
	require 'PHPExcel.php';
	
	
	require 'scripts/log_function.php';
	require 'scripts/generateExcel.php';
	require 'scripts/sendMail.php';
	require 'scripts/updateCallList.php';
	
	log_message("Start load");
	
	
	$params = parse_ini_file("params.ini");
	
	$prompt_date = $params["prompt_date"];
	$time = strtotime($prompt_date);
	$newformat = date('d-m-Y',$time);
	
	
	$start_date  =  date('m/d/Y',(strtotime ( '-14 day' , strtotime ( $newformat) ) ));
	$end_date = date ('m/d/Y',$time);
	
	$start_date_mysql = date('Y-m-d',(strtotime ( '-28 day' , strtotime ( $newformat) ) ));
	$end_date_mysql = date ('Y-m-d',$time);
	
	log_message("Selected date period: ".$start_date." -- ".$end_date);
	
 
	$db_path_ansi = mb_convert_encoding($params["access_db_path"], $params["access_encoding"]);
	$dbh = new PDO('odbc:Driver={Microsoft Access Driver (*.mdb, *.accdb)};Dbq='.$db_path_ansi, "", "");

	$table1 = "Select 
			distinct tTransaction.iConstituentID as iConstituentID, 
			count(tTransaction.iTransactionID) as count_decline 
			from tTransaction		
			LEFT JOIN tTransactionProviderData on tTransaction.iTransactionID=tTransactionProviderData.iTransactionID
			WHERE tTransactionProviderData.sProviderData3='Rebill Decline' 
					and tTransaction.dDate between #".$start_date."# and #".$end_date."#
			GROUP BY tTransaction.iConstituentID
			HAVING count(tTransaction.iTransactionID)>2"; // Get all constituents having 3 decline during select period. they are the candidates to final list
	
	
	
	$table2 = "Select 
				t.iTransactionID as iTransactionID,
				t.iConstituentID as iConstituentID,
				parent_t.cAmount as cAmount,
				t.dDate as dDate,
				tpd.sProviderData2 as sProviderData2,
				tpd.sProviderData3 as sProviderData3
				from ((tTransaction t	 
			INNER JOIN tTransactionProviderData tpd on t.iTransactionID=tpd.iTransactionID)
			INNER JOIN tTransactionProviderData parent_tpd on parent_tpd.sProviderData1 = tpd.sProviderData2)
			INNER JOIN tTransaction as parent_t on parent_t.iTransactionID = parent_tpd.iTransactionID
			WHERE t.dDate between #".$start_date."# and #".$end_date."#
			
	"; // list of transactions durind the selected period 
	
					
					
    $sql = "SELECT 
			
			main_table.iTransactionID as iTransactionID,
			main_table.iConstituentID as iConstituentID,
			main_table.cAmount as cAmount,
			main_table.dDate as dDate,
			main_table.sProviderData2 as sProviderData2,
			main_table.sProviderData3 as sProviderData3
			
			
			from (".$table2.") as main_table
			INNER JOIN (".$table1.") as filter_table on main_table.iConstituentID=filter_table.iConstituentID
			WHERE main_table.cAmount>=300
			"; // we are getting all transactions about people, that have more than 2 declines during the selected period
				

	$data = '';
	$count_transactions =0;
    foreach ($dbh->query($sql) as $row) {
		
		//print $row["iConstituentID"]." ".$row["sProviderData2"]." ".$row["dDate"]." ".$row["cAmount"]." ".$row["sProviderData3"]."<br>";
		
        $transactions[$row["iConstituentID"]][$row["sProviderData2"]][$row["dDate"]]["sProviderData3"] = $row["sProviderData3"];
        $transactions[$row["iConstituentID"]][$row["sProviderData2"]][$row["dDate"]]["iTransactionID"] = $row["iTransactionID"];
        $transactions[$row["iConstituentID"]][$row["sProviderData2"]][$row["dDate"]]["cAmount"] = $row["cAmount"];
		
		$count_transactions++;
    }
	
	if ($count_transactions==0)
	{
		log_message("No transactions");
		exit(0);
	}
	
	
	$count_info_list = 0;
	foreach ($transactions as $const_key=>$const_val) // внутри каждого конститутента
	{
		foreach ($transactions[$const_key] as $prov_key=>$prov_val) // внутри каждой транзакции одного типа
		{
			ksort($transactions[$const_key][$prov_key]); // сортим по дате
			
			
			$count_decline = 0;
			$amount = 0;
			foreach ($transactions[$const_key][$prov_key] as $date_key=>$val) //проходим по транзакциям и ищем цепочку деклайнов
			{	
				if ($transactions[$const_key][$prov_key][$date_key]["sProviderData3"]=="Rebill decline")
				{
					$count_decline++;
					$last_date = $date_key;
					$amount = $transactions[$const_key][$prov_key][$date_key]["cAmount"];
				}
				else $count_decline=0;
			}
	
			if ($count_decline>=3) // если нашли - добавляем в инфо лист
			{
				$info_list[$count_info_list]["const_key"] = $const_key;
				$info_list[$count_info_list]["prov_key"] = $prov_key;
				$info_list[$count_info_list]["last_date"] = $last_date;
				$info_list[$count_info_list]["amount"] = $amount;
				$count_info_list++;
			}
		}
	}
	log_message("Call list consists ".$count_info_list." records");
	if ($count_info_list==0)
	{
		log_message("Call list is empty, terminating");
		exit(0);
	}
	
	
	$sql_const_info = "Select ids.iConstituentID as iConstituentID,
							tTelecom_BestPhone.BestPhone as BestPhone,
							tConstituent.sLastName as last_name
							from 
					((select distinct tTransaction.iConstituentID as iConstituentID
						from tTransaction where tTransaction.dDate between #".$start_date."# and #".$end_date."# ) as ids
						LEFT JOIN tTelecom_BestPhone on ids.iConstituentID=tTelecom_BestPhone.iConstituentID)
						LEFT JOIN tConstituent on ids.iConstituentID=tConstituent.iConstituentID
						";

	
						
		foreach ($dbh->query($sql_const_info) as $row) {
			
			$constituent[$row["iConstituentID"]]["last_name"] = $row["last_name"];
			$constituent[$row["iConstituentID"]]["BestPhone"] = $row["BestPhone"];
			
			$phones[$row["BestPhone"]] = $row["iConstituentID"];
			
		}
	

	
	
	$mysql = new PDO($params["mysql_host"], $params["mysql_user"], $params["mysql_password"]);
	$mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$mysql_query = "Select 
					call_phone
					from const_calls 
					where call_date between '".$start_date_mysql."' and '".$end_date_mysql."'";
	
	print $start_date_mysql." ".$end_date_mysql;
	print $mysql_query;
	foreach ($mysql->query($mysql_query) as $row) // удаляем записи о констах, которым не надо звонить, потому что уже звонили
	{
		unset($constituent[$phones[$row["call_phone"]]]);
	}
	
	$count_result = 0;
	for ($i=0;$i<$count_info_list;$i++)
	{
		if (isset($constituent[$info_list[$i]["const_key"]]))
		{
			$info_list[$i]["last_name"] = iconv("CP1251","UTF-8",$constituent[$info_list[$i]["const_key"]]["last_name"]);
			$info_list[$i]["BestPhone"] = $constituent[$info_list[$i]["const_key"]]["BestPhone"];
			$result_list[$count_result] = $info_list[$i];
			$count_result++;
		}
	}
	
	
	updateCallList($result_list,$mysql);
	$currentListName = generateExcelFile($result_list,$params);
	
	sendMailWithAttach($currentListName,$params);
	
	
	
	
	
?>>
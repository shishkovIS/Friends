<?php

function generateExcelFile($info_list,$params)
{
	$timestamp = date('m-d-Y_his');
	
	
	$objPHPExcel = new PHPExcel();
	

	$objPHPExcel->getProperties()->setCreator("Auto generated file")
							 ->setLastModifiedBy("Maarten Balliauw")
							 ->setTitle("List Constituent");
	$objPHPExcel->getActiveSheet()->fromArray($info_list, NULL, 'A2');
	$objPHPExcel->getActiveSheet()->setCellValue('A1', 'ConstituentID');
	$objPHPExcel->getActiveSheet()->setCellValue('B1', 'TransactiondID');
	$objPHPExcel->getActiveSheet()->setCellValue('C1','Дата последнего неудачного списания');
	$objPHPExcel->getActiveSheet()->setCellValue('D1', 'Сумма');
	$objPHPExcel->getActiveSheet()->setCellValue('E1', 'Фамилия');
	$objPHPExcel->getActiveSheet()->setCellValue('F1', 'Телефон');
	
	
	$objPHPExcel->getActiveSheet()->getProtection()->setPassword($params["excel_password"]);
	
	$objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
	
	$currentListName = $params["filepath"].$params["filename"].$timestamp.".xls";
	$objWriter->save($currentListName);
	
	return $currentListName;
	
}
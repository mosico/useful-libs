<?php

class PHPExcelDemo
{
	private $_metricsProvider;
	private $_PHPExcel;
	private $_savePath = '';
	private $_sheetCount = 0;

	public function __construct()
	{
		$this->_PHPExcel = new PHPExcel();

		if (APPLICATION_ENV == 'dev' || APPLICATION_ENV == 'local') {
			$this->_savePath = SOURCE_PATH;
		} else {
			$this->_savePath = '/inv/sites/apache/writable';
		}
		if (!is_dir($this->_savePath)) {
			mkdir($this->_savePath, 666, true);
		}
	}

	public function createMetricsExcel()
	{
		$excelFile =  $this->_savePath . '/Simulator_Metrics_' . date('Ymd') . '.xlsx';
		$this->_setDocProperty();

		/***************************************** Start create worksheet ******************************/
		// Create Active Users WorkSheet
		$this->_setSheetData($this->buildActiveUser(), 'Active Users');

		// Create Active Games WorkSheet
		$this->_setSheetData($this->buildActiveGame(), 'Active Games');
		/***************************************** End create worksheet ******************************/

		// Set active sheet index to the first sheet, so Excel opens this as the first sheet
		$this->_PHPExcel->setActiveSheetIndex(0);

		// Save Excel 2007 file
		$objWriter = PHPExcel_IOFactory::createWriter($this->_PHPExcel, 'Excel2007');
		$objWriter->save($excelFile);

		echo "Create Excel Success!! \nFileName: $excelFile \n";
	}

	private function buildActiveUser()
	{
		$sheet = array();
		$au = $this->_metricsProvider->GetMetricsActiveUsers($this->_startDate, $this->_endDate);
		if (empty($au)) return $sheet;

		$sheet[] = $this->_buildTitleData(array(
				array('text' => 'Date', 'width' => 22),
				'Total Simulator Users', 'Active Last 7 days', 'Active Last 30 days', 'Active Last 90 days'));
		$_profilesCount = $_last7 = $_last30 = $_last90 = 0;
		foreach ($au as $row) {
			$sheet[] = array(
				'A' => array('value' => $this->_shortDate($row->MetricsDate)),
				'B' => array('value' => $row->ProfilesCount),
				'C' => array('value' => $row->Last7),
				'D' => array('value' => $row->Last30),
				'E' => array('value' => $row->Last90),
			);
			$_profilesCount += $row->ProfilesCount;
			$_last7 += $row->Last7;
			$_last30 += $row->Last30;
			$_last90 += $row->Last90;
		}

		$rowCount = count($au);
		$profilesAvg = $_profilesCount / $rowCount;
		$sheet[] = array(
				'A' => array('value' => 'Average:', 'isBold' => true),
				'B' => array('value' => round($_profilesCount / $rowCount, 1), 'isBold' => true),
				'C' => array('value' => round($_last7 / $rowCount, 1), 'isBold' => true),
				'D' => array('value' => round($_last30 / $rowCount, 1), 'isBold' => true),
				'E' => array('value' => round($_last90 / $rowCount, 1), 'isBold' => true),
		);

		return $sheet;
	}

	private function buildClassificationMetrics()
	{
		$sheet = array();
		$uc = $this->_metricsProvider->GetMetricsUsersClassification($this->_startDate, $this->_endDate);
		if (empty($uc)) return $sheet;

		$sheet[] = $this->_buildTitleData(array(
				array('text' => 'Date', 'width' => 22),
				'Total Users', 'Eligible Users', 'Professionals', 'Enrolled at an educational institution'));
		foreach ($uc as $row) {
			$sheet[] = array(
					'A' => array('value' => $this->_shortDate($row->MetricsDate)),
					'B' => array('value' => $row->TotalUsers),
					'C' => array('value' => $row->EligibleUsers),
					'D' => array('value' => $row->Professionals),
					'E' => array('value' => $row->Educational),
			);
		}

		return $sheet;
	}


	private function _buildTitleData($titleArr)
	{
		$cellArr = array();
		if (empty($titleArr) || !is_array($titleArr)) {
			return $cellArr;
		}
		$column = 'A';
		foreach ($titleArr as $title) {
			$width = 0;
			$text = $title;
			if (is_array($title)) {
				$text = $title['text'];
				if (isset($title['width'])) $width = (int) $title['width'];
			}
			if (!$width) {
				$textLen = strlen($text);
				if ($textLen > 7) {
					$width = $textLen + 4;
				}
			}
			$cellArr[$column] = array('value' => $text, 'isBold' => true, 'width' => $width);
			$column++;
		}
		return $cellArr;
	}

	private function _setDocProperty()
	{
		// Set document properties
		$this->_PHPExcel->getProperties()->setCreator("Simulator")
		->setLastModifiedBy("Simulator")
		->setTitle("Simulator Daily Report")
		->setSubject("Simulator Daily Report")
		->setDescription("Simulator Daily Report, trade/active information")
		->setKeywords("Excel Report")
		->setCategory("Report");
	}

	private function _setSheetData($dataArr, $sheetName)
	{
		if ($this->_sheetCount > 0) {
			// Create new worksheet
			$this->_PHPExcel->createSheet($this->_sheetCount);
		}

		$activeSheet = $this->_PHPExcel->setActiveSheetIndex($this->_sheetCount);

		$maxColumn = 'A';
		$curRow = 1;
		foreach ($dataArr as $row) {
			if (empty($row)) {
				$curRow++;
				continue;
			}
			$curColumn = 'A';

			// Set excel eache row cell data
			foreach ($row as $column => $cell) {
				$curColumn = ctype_alpha($column) ? $column : $curColumn;
				//$value = isset($cell['isBold']) && $cell['isBold'] ? $this->_createBlodText($cell['value']) : $cell['value'];
				$value = $cell['value'];
				$activeSheet->setCellValue($curColumn.$curRow, $value);
				if (isset($cell['width']) && $cell['width'] > 0) $activeSheet->getColumnDimension($curColumn)->setWidth($cell['width']);
				if (isset($cell['isBold']) && $cell['isBold']) $activeSheet->getStyle($curColumn.$curRow)->getFont()->setBold(true);
				$curColumn++;
			}
			// Fix if the last row has few column, can't alignment all columns
			$maxColumn = $curColumn > $maxColumn ? $curColumn : $maxColumn;
			$curRow++;
		}

		// Set wordsheet name/title
		if (empty($sheetName)) $sheetName = 'Default Sheet';
		$activeSheet->setTitle($sheetName);

		// Set cell text center alignment
		$activeSheet->getStyle('A1:'.$maxColumn.$curRow)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);

		$this->_sheetCount++;
	}

	private function _createBlodText($text)
	{
		$objRichText = new PHPExcel_RichText();
		$objBold = $objRichText->createTextRun($text);
		$objBold->getFont()->setBold(true);
		return $objRichText;
	}

	private function _shortDate($date)
	{
		return date('j-M-y', strtotime($date));
	}
	
	
	public function readExcel()
	{
		$phpExcel = PHPExcel_IOFactory::load($this->_scenarioFile);
// 		$cv = $phpExcel->getActiveSheet()->getCell('B6')->getValue();
// 		$date = \PHPExcel_Shared_Date::ExcelToPHP($cv);
// 		var_dump($cv, $date, date('Y-d-m H:i:s'));exit;
		
		$gameObj = new stdClass();
		$actionList = array();
		$handleAction = false;
		$rowNum = 0;
		$rowInterator = $phpExcel->setActiveSheetIndex(0)->getRowIterator();
		foreach ($rowInterator as $row) {
			$rowNum++;
			// first row is title, ignore
			if ($rowNum == 1) continue;
			
			$cellIterator = $row->getCellIterator();
			$cellIterator->setIterateOnlyExistingCells(false);
			$colNum = 0 ;
			$actionObj = new stdClass();
			foreach ($cellIterator as $cell) {
				$colNum++;
				$cellVal = trim($cell->getValue());
				if ($colNum == 1 && $cellVal == 'Action') {
					$handleAction = true;
					continue 2;
				}
				
				//If first cell is empty, skip this row
				if ($colNum == 1 && empty($cellVal)) {
					continue 2;
				}
				
				// Game info
				if (!$handleAction) {
					if ($colNum == 2 && !empty($cellVal)) {
						$gameObj->setPropValue($rowNum, $cellVal);
					}
				}
				
				// Action info
				if ($handleAction) {
					$actionObj->setPropValue($colNum, $cellVal);
				}
			}
			
			// Action info list
			if (is_object($actionObj) && !empty($actionObj->action)) {
				$actionList[] = $actionObj;
			}
		}
		
		$scenario = array('game' => $gameObj, 'actionList' => $actionList);
		return $scenario;
	}
}
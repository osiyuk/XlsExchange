<?php

require_once 'vendor/autoload.php';


trait parseJSON {

	protected function parseJSON(string $filename, bool $assoc = true)
	{
		$json = file_get_contents($filename);
		return json_decode($json, $assoc);
	}
}


trait validateEAN13 {

	protected function validateEAN13(string $barcode) : ?string
	{
		$code = intval(substr($barcode, 0, 3));
		$original_sum = intval($barcode[12]);

		if (strlen($barcode) != 13) {
			return null;
		}

		if ($code < 200 || 299 < $code) {
			return null;
		}

		for ($sum = $i = 0; $i < 12; $i++) {
			$num = intval($barcode[$i]);
			$sum += ($num % 2 ? 3 : 1) * $num;
		}

		$check_sum = (10 - $sum % 10) % 10;

		if ($original_sum !== $check_sum) {
			return null;
		}

		return $barcode;
	}
}


trait exportToXLSX {
	protected $xlsx_writer;

	protected function exportToXLSX(
		array $sheet_header_names_types,
		array $sheet_rows_data,
		array $sheet_header_styles = null,
		array $sheet_row_styles = null
	) {
		$this->xlsx_writer = new XLSXWriter();

		$writer =& $this->xlsx_writer;
		$sheet_name = 'Sheet1';

		$writer->writeSheetHeader(
			$sheet_name,
			$sheet_header_names_types,
			$sheet_header_styles
		);

		foreach ($sheet_rows_data as $row)
		$writer->writeSheetRow(
			$sheet_name,
			$row,
			$sheet_row_styles
		);
	}

	protected function writeToFile(string $filename)
	{
		$writer =& $this->xlsx_writer;

		$writer->writeToFile($filename);
	}
}


trait uploadToFTP {
	protected $ftp_host;
	protected $ftp_login;
	protected $ftp_password;
	protected $ftp_dir;

	protected function uploadToFTP(string $remote_file, string $local_file)
	{
		$conn = ftp_connect($this->ftp_host)
			or die("failed connect to '{$this->ftp_host}'");

		ftp_login($conn, $this->ftp_login, $this->ftp_password)
			or die("failed login as '{$this->ftp_login}'");

		ftp_chdir($conn, $this->ftp_dir)
			or die("failed chdir to '{$this->ftp_dir}'");

		ftp_put($conn, $remote_file, $local_file)
			or die("failed upload to '$remote_file'");
	}

	public function setFtpHost(string $host = '')
	{
		$this->ftp_host = $host;
		return $this;
	}

	public function setFtpLogin(string $user = '')
	{
		$this->ftp_login = $user;
		return $this;
	}

	public function setFtpPassword(string $pass = '')
	{
		$this->ftp_password = $pass;
		return $this;
	}

	public function setFtpDir(string $directory = '')
	{
		$this->ftp_dir = $directory;
		return $this;
	}
}


final class XlsExchange {
	use parseJSON;
	use validateEAN13;
	use uploadToFTP;

	private const INVALID_BCODE = 'INVALID_BCODE';
	private const COLNAMES = [
		'Id' => '0',
		'ШК' => '@',
		'Название' => '@',
		'Кол-во' => '0',
		'Сумма' => '0'
	];
	private const SHEET_HEADER_STYLES = [
		'font' => 'Times New Roman',
		'font-size' => 12,
		'font-style' => 'bold',
		'halign' => 'center',
		'widths' => [10, 15, 50, 10, 10],
	];
	private const SHEET_ROW_STYLES = [
		'font-size' => 11,
	];

	protected $path_to_input_json_file;
	protected $path_to_output_xlsx_file;
	protected $isLocal = true;


	private function exportItems(array $items)
	{
		$this->exportToXLSX(
			self::COLNAMES,
			$items,
			self::SHEET_HEADER_STYLES,
			self::SHEET_ROW_STYLES);
	}

	private function extractFields(array $position) : array
	{
		$item = $position['item'];
		$barcode = $this->validateEAN13($item['barcode']) ??
			self::INVALID_BCODE;

		return [
			$position['id'],
			$barcode,
			$item['name'],
			$position['quantity'],
			$position['price'],
		];
	}

	static function compareFunc(array $A, array $B) : int
	{
		$j = 4; // price
		return $B[$j] - $A[$j];
	}


	public function export()
	{
		$order = $this->parseJSON($this->path_to_input_json_file);

		$items = array_map([self, 'extractFields'], $order['items']);
		usort($items, [self, 'compareFunc']);

		$xlsx = new XLSXWriter();
		$style = [
			'font' => 'Times New Roman',
			'font-size' => 12,
			'font-style' => 'bold',
			'halign' => 'center',
			'widths' => [10, 15, 50, 10, 10],
		];

		$row_style = [
			'font-size' => 11,
		];
		$xlsx->writeSheetHeader('Sheet1', self::COLNAMES, $style);

		foreach ($items as $row) {
			$xlsx->writeSheetRow('Sheet1', $row, $row_style);
		}

		if ($this->isLocal) {
			$xlsx->writeToFile($this->path_to_output_xlsx_file);
			return;
			//  DONE
		}

		//  NOT TESTED, SORRY
		$filename = tempnam(sys_get_temp_dir(), 'xlsx_writer_');
		$xlsx->writeToFile($filename);
		$this->uploadToFTP($this->path_to_output_xlsx_file, $filename);
	}


	public function testEAN13(string $barcode)
	{
		$result = $this->validateEAN13($barcode) ?? self::INVALID_BCODE;
		echo "$barcode validated $result\n";
		return $this;
	}


	public function setInputFile(string $filename)
	{
		$this->path_to_input_json_file = $filename;
		return $this;
	}

	public function setOutputFile(string $filename)
	{
		$this->path_to_output_xlsx_file = $filename;
		return $this;
	}

	public function setFtpHost(string $host)
	{
		$this->ftp_host = $host;
		$this->isLocal = false;
		return $this;
	}
}


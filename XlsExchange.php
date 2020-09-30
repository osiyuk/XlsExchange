<?php


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


final class XlsExchange {
	use validateEAN13;

	private const INVALID_BCODE = 'INVALID_BCODE';

	protected $path_to_input_json_file;
	protected $path_to_output_xlsx_file;

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


	public function testEAN13(string $barcode)
	{
		$result = $this->validateEAN13($barcode) ?? self::INVALID_BCODE;
		echo "$barcode validated $result\n";
		return $this;
	}
}


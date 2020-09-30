<?php


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


final class XlsExchange {
	use parseJSON;
	use validateEAN13;

	private const INVALID_BCODE = 'INVALID_BCODE';
	private const COLNAMES = ['Id', 'ШК', 'Название', 'Кол-во', 'Сумма'];

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


	public function export()
	{
		$order = $this->parseJSON($this->path_to_input_json_file);
		$items = [self::COLNAMES];

		foreach ($order['items'] as $position) {
			$item = $position['item'];
			$items[] = [
				$position['id'],
				$position['price'],
				$position['quantity'],
				$item['barcode'],
				$item['name'],
			];
break;
		}
		var_dump($items);
	}


	public function testEAN13(string $barcode)
	{
		$result = $this->validateEAN13($barcode) ?? self::INVALID_BCODE;
		echo "$barcode validated $result\n";
		return $this;
	}
}


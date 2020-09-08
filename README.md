# Задача

Нужно взять данные из order.json и превратить их в формат items.xlsx.
Затем сохранить файл на FTP или локально. Для этого нужно реализовать класс XlsExchange с методом ```export```

```
...

protected $path_to_input_json_file;
protected $path_to_output_xlsx_file;
protected $ftp_host;
protected $ftp_login;
protected $ftp_password;
protected $ftp_dir;

export() {...}

...
```

Устанавливаем необходимые данные, затем проводим экспорт. Во время экспорта нужно:
1. Проверить штрих-коды на валидность (там должны быть только EAN-13)
2. Привести в читаемый вид utf-последловательности.
3. Если данных о FTP-сервере не передали, сохранить файл на локальном сервере.

## Пример вызова во время проверки

```
<?php

require_once 'XlsExchange.php';

(new \XlsExchange())
    ->setInputFile('/tmp/orders.json')
    ->setOutputFile('/tmp/items.xlsx')
    ...
    ->export();

```

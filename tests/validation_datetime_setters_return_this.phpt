--TEST--
Validation datetime setters return $this for chaining
--SKIPIF--
<?php
require __DIR__ . '/include/skipif.inc';
?>
--FILE--
<?php
$config = ['path' => './tests'];

/* minimumDatetime/maximumDatetime in non-terminal chain position used to
 * return null instead of $this, fataling on the next chained call. */
$between = new \Vtiful\Kernel\Validation();
$ret = $between->validationType(\Vtiful\Kernel\Validation::TYPE_DATE)
    ->criteriaType(\Vtiful\Kernel\Validation::CRITERIA_BETWEEN)
    ->minimumDatetime(mktime(0, 0, 0, 1, 1, 2024))
    ->maximumDatetime(mktime(0, 0, 0, 12, 31, 2024))
    ->ignoreBlank(true);
var_dump($ret instanceof \Vtiful\Kernel\Validation);

$greater = new \Vtiful\Kernel\Validation();
$ret = $greater->validationType(\Vtiful\Kernel\Validation::TYPE_DATE)
    ->criteriaType(\Vtiful\Kernel\Validation::CRITERIA_GREATER_THAN)
    ->valueDatetime(mktime(0, 0, 0, 6, 1, 2024))
    ->showInput(true);
var_dump($ret instanceof \Vtiful\Kernel\Validation);

/* Round-trip: chained datetime validations still produce a loadable file. */
$excel    = new \Vtiful\Kernel\Excel($config);
$filePath = $excel->fileName('validation_datetime_setters_return_this.xlsx')
    ->validation('A1', $between->toResource())
    ->validation('B1', $greater->toResource())
    ->output();
var_dump($filePath);

$reader = new \Vtiful\Kernel\Excel($config);
$data   = $reader->openFile('validation_datetime_setters_return_this.xlsx')
                 ->openSheet()->getSheetData();
var_dump($data);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/validation_datetime_setters_return_this.xlsx');
?>
--EXPECT--
bool(true)
bool(true)
string(52) "./tests/validation_datetime_setters_return_this.xlsx"
array(0) {
}

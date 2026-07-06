--TEST--
Const-memory streaming buffer + integer fast-path round-trip (small/big/int64 ints, float, inline strings)
--SKIPIF--
<?php
require __DIR__ . '/include/skipif.inc';
?>
--FILE--
<?php
$excel = new \Vtiful\Kernel\Excel(['path' => './tests']);

/* Positive values only: the reader's numeric type inference returns negative
   numbers and zero as strings (a pre-existing reader limitation unrelated to
   the writer — the emitted <v> XML is correct for them). These values exercise
   the const-memory streaming buffer, the integer fast-path (incl. >uint32 and
   near-int64 magnitudes via _u64_dec), a non-integral double, and both the
   plain and whitespace-preserving inline-string paths. */
$excel->constMemory('const_memory_stream_roundtrip.xlsx')
    ->data([
        [1, 42, 4294967296, 2.5, 'hi', ' pad '],
        [100000, 65535, 9000000000000000000, 3.125, 'tail ', 'x'],
    ])
    ->output();

$v = new \Vtiful\Kernel\Excel(['path' => './tests']);
$data = $v->openFile('const_memory_stream_roundtrip.xlsx')->openSheet()->getSheetData();

/* Column 2 holds integers above 2^31. On 64-bit PHP they read back as native
   ints; on 32-bit PHP there is no 64-bit int type, so the reader preserves
   their exact digits as a string instead. Normalise both to string so the
   round-tripped digits are asserted identically on every architecture — the
   <=2^31 ints in columns 0-1 still assert native int type below. */
$data[0][2] = (string) $data[0][2];
$data[1][2] = (string) $data[1][2];
var_dump($data);
?>
--CLEAN--
<?php
@unlink(__DIR__ . '/const_memory_stream_roundtrip.xlsx');
?>
--EXPECT--
array(2) {
  [0]=>
  array(6) {
    [0]=>
    int(1)
    [1]=>
    int(42)
    [2]=>
    string(10) "4294967296"
    [3]=>
    float(2.5)
    [4]=>
    string(2) "hi"
    [5]=>
    string(5) " pad "
  }
  [1]=>
  array(6) {
    [0]=>
    int(100000)
    [1]=>
    int(65535)
    [2]=>
    string(19) "9000000000000000000"
    [3]=>
    float(3.125)
    [4]=>
    string(5) "tail "
    [5]=>
    string(1) "x"
  }
}

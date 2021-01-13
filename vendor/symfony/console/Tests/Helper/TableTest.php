<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Tests\Helper;

use MolliePrefix\PHPUnit\Framework\TestCase;
use MolliePrefix\Symfony\Component\Console\Helper\Table;
use MolliePrefix\Symfony\Component\Console\Helper\TableCell;
use MolliePrefix\Symfony\Component\Console\Helper\TableSeparator;
use MolliePrefix\Symfony\Component\Console\Helper\TableStyle;
use MolliePrefix\Symfony\Component\Console\Output\StreamOutput;
class TableTest extends \MolliePrefix\PHPUnit\Framework\TestCase
{
    protected $stream;
    protected function setUp()
    {
        $this->stream = \fopen('php://memory', 'r+');
    }
    protected function tearDown()
    {
        \fclose($this->stream);
        $this->stream = null;
    }
    /**
     * @dataProvider renderProvider
     */
    public function testRender($headers, $rows, $style, $expected, $decorated = \false)
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream($decorated));
        $table->setHeaders($headers)->setRows($rows)->setStyle($style);
        $table->render();
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    /**
     * @dataProvider renderProvider
     */
    public function testRenderAddRows($headers, $rows, $style, $expected, $decorated = \false)
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream($decorated));
        $table->setHeaders($headers)->addRows($rows)->setStyle($style);
        $table->render();
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    /**
     * @dataProvider renderProvider
     */
    public function testRenderAddRowsOneByOne($headers, $rows, $style, $expected, $decorated = \false)
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream($decorated));
        $table->setHeaders($headers)->setStyle($style);
        foreach ($rows as $row) {
            $table->addRow($row);
        }
        $table->render();
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function renderProvider()
    {
        $books = [['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'], ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens'], ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'], ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie']];
        return [[['ISBN', 'Title', 'Author'], $books, 'default', <<<'TABLE'
+---------------+--------------------------+------------------+
| ISBN          | Title                    | Author           |
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
| 9971-5-0210-0 | A Tale of Two Cities     | Charles Dickens  |
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

TABLE
], [['ISBN', 'Title', 'Author'], $books, 'compact', <<<'TABLE'
 ISBN          Title                    Author           
 99921-58-10-7 Divine Comedy            Dante Alighieri  
 9971-5-0210-0 A Tale of Two Cities     Charles Dickens  
 960-425-059-0 The Lord of the Rings    J. R. R. Tolkien 
 80-902734-1-6 And Then There Were None Agatha Christie  

TABLE
], [['ISBN', 'Title', 'Author'], $books, 'borderless', <<<'TABLE'
 =============== ========================== ================== 
  ISBN            Title                      Author            
 =============== ========================== ================== 
  99921-58-10-7   Divine Comedy              Dante Alighieri   
  9971-5-0210-0   A Tale of Two Cities       Charles Dickens   
  960-425-059-0   The Lord of the Rings      J. R. R. Tolkien  
  80-902734-1-6   And Then There Were None   Agatha Christie   
 =============== ========================== ================== 

TABLE
], [['ISBN', 'Title'], [['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'], ['9971-5-0210-0'], ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'], ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie']], 'default', <<<'TABLE'
+---------------+--------------------------+------------------+
| ISBN          | Title                    |                  |
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
| 9971-5-0210-0 |                          |                  |
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

TABLE
], [[], [['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'], ['9971-5-0210-0'], ['960-425-059-0', 'The Lord of the Rings', 'J. R. R. Tolkien'], ['80-902734-1-6', 'And Then There Were None', 'Agatha Christie']], 'default', <<<'TABLE'
+---------------+--------------------------+------------------+
| 99921-58-10-7 | Divine Comedy            | Dante Alighieri  |
| 9971-5-0210-0 |                          |                  |
| 960-425-059-0 | The Lord of the Rings    | J. R. R. Tolkien |
| 80-902734-1-6 | And Then There Were None | Agatha Christie  |
+---------------+--------------------------+------------------+

TABLE
], [['ISBN', 'Title', 'Author'], [['99921-58-10-7', "Divine\nComedy", 'Dante Alighieri'], ['9971-5-0210-2', "Harry Potter\nand the Chamber of Secrets", "Rowling\nJoanne K."], ['9971-5-0210-2', "Harry Potter\nand the Chamber of Secrets", "Rowling\nJoanne K."], ['960-425-059-0', 'The Lord of the Rings', "J. R. R.\nTolkien"]], 'default', <<<'TABLE'
+---------------+----------------------------+-----------------+
| ISBN          | Title                      | Author          |
+---------------+----------------------------+-----------------+
| 99921-58-10-7 | Divine                     | Dante Alighieri |
|               | Comedy                     |                 |
| 9971-5-0210-2 | Harry Potter               | Rowling         |
|               | and the Chamber of Secrets | Joanne K.       |
| 9971-5-0210-2 | Harry Potter               | Rowling         |
|               | and the Chamber of Secrets | Joanne K.       |
| 960-425-059-0 | The Lord of the Rings      | J. R. R.        |
|               |                            | Tolkien         |
+---------------+----------------------------+-----------------+

TABLE
], [['ISBN', 'Title'], [], 'default', <<<'TABLE'
+------+-------+
| ISBN | Title |
+------+-------+

TABLE
], [[], [], 'default', ''], 'Cell text with tags used for Output styling' => [['ISBN', 'Title', 'Author'], [['<info>99921-58-10-7</info>', '<error>Divine Comedy</error>', '<fg=blue;bg=white>Dante Alighieri</fg=blue;bg=white>'], ['9971-5-0210-0', 'A Tale of Two Cities', '<info>Charles Dickens</>']], 'default', <<<'TABLE'
+---------------+----------------------+-----------------+
| ISBN          | Title                | Author          |
+---------------+----------------------+-----------------+
| 99921-58-10-7 | Divine Comedy        | Dante Alighieri |
| 9971-5-0210-0 | A Tale of Two Cities | Charles Dickens |
+---------------+----------------------+-----------------+

TABLE
], 'Cell text with tags not used for Output styling' => [['ISBN', 'Title', 'Author'], [['<strong>99921-58-10-700</strong>', '<f>Divine Com</f>', 'Dante Alighieri'], ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens']], 'default', <<<'TABLE'
+----------------------------------+----------------------+-----------------+
| ISBN                             | Title                | Author          |
+----------------------------------+----------------------+-----------------+
| <strong>99921-58-10-700</strong> | <f>Divine Com</f>    | Dante Alighieri |
| 9971-5-0210-0                    | A Tale of Two Cities | Charles Dickens |
+----------------------------------+----------------------+-----------------+

TABLE
], 'Cell with colspan' => [['ISBN', 'Title', 'Author'], [['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), [new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Divine Comedy(Dante Alighieri)', ['colspan' => 3])], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), [new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Arduino: A Quick-Start Guide', ['colspan' => 2]), 'Mark Schmidt'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['9971-5-0210-0', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("A Tale of \nTwo Cities", ['colspan' => 2])], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), [new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Cupiditate dicta atque porro, tempora exercitationem modi animi nulla nemo vel nihil!', ['colspan' => 3])]], 'default', <<<'TABLE'
+-------------------------------+-------------------------------+-----------------------------+
| ISBN                          | Title                         | Author                      |
+-------------------------------+-------------------------------+-----------------------------+
| 99921-58-10-7                 | Divine Comedy                 | Dante Alighieri             |
+-------------------------------+-------------------------------+-----------------------------+
| Divine Comedy(Dante Alighieri)                                                              |
+-------------------------------+-------------------------------+-----------------------------+
| Arduino: A Quick-Start Guide                                  | Mark Schmidt                |
+-------------------------------+-------------------------------+-----------------------------+
| 9971-5-0210-0                 | A Tale of                                                   |
|                               | Two Cities                                                  |
+-------------------------------+-------------------------------+-----------------------------+
| Cupiditate dicta atque porro, tempora exercitationem modi animi nulla nemo vel nihil!       |
+-------------------------------+-------------------------------+-----------------------------+

TABLE
], 'Cell with rowspan' => [['ISBN', 'Title', 'Author'], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('9971-5-0210-0', ['rowspan' => 3]), new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Divine Comedy', ['rowspan' => 2]), 'Dante Alighieri'], [], ["The Lord of \nthe Rings", "J. R. \nR. Tolkien"], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['80-902734-1-6', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("And Then \nThere \nWere None", ['rowspan' => 3]), 'Agatha Christie'], ['80-902734-1-7', 'Test']], 'default', <<<'TABLE'
+---------------+---------------+-----------------+
| ISBN          | Title         | Author          |
+---------------+---------------+-----------------+
| 9971-5-0210-0 | Divine Comedy | Dante Alighieri |
|               |               |                 |
|               | The Lord of   | J. R.           |
|               | the Rings     | R. Tolkien      |
+---------------+---------------+-----------------+
| 80-902734-1-6 | And Then      | Agatha Christie |
| 80-902734-1-7 | There         | Test            |
|               | Were None     |                 |
+---------------+---------------+-----------------+

TABLE
], 'Cell with rowspan and colspan' => [['ISBN', 'Title', 'Author'], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('9971-5-0210-0', ['rowspan' => 2, 'colspan' => 2]), 'Dante Alighieri'], ['Charles Dickens'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['Dante Alighieri', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('9971-5-0210-0', ['rowspan' => 3, 'colspan' => 2])], ['J. R. R. Tolkien'], ['J. R. R']], 'default', <<<'TABLE'
+------------------+---------+-----------------+
| ISBN             | Title   | Author          |
+------------------+---------+-----------------+
| 9971-5-0210-0              | Dante Alighieri |
|                            | Charles Dickens |
+------------------+---------+-----------------+
| Dante Alighieri  | 9971-5-0210-0             |
| J. R. R. Tolkien |                           |
| J. R. R          |                           |
+------------------+---------+-----------------+

TABLE
], 'Cell with rowspan and colspan contains new line break' => [['ISBN', 'Title', 'Author'], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("9971\n-5-\n021\n0-0", ['rowspan' => 2, 'colspan' => 2]), 'Dante Alighieri'], ['Charles Dickens'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['Dante Alighieri', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("9971\n-5-\n021\n0-0", ['rowspan' => 2, 'colspan' => 2])], ['Charles Dickens'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), [new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("9971\n-5-\n021\n0-0", ['rowspan' => 2, 'colspan' => 2]), new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("Dante \nAlighieri", ['rowspan' => 2, 'colspan' => 1])]], 'default', <<<'TABLE'
+-----------------+-------+-----------------+
| ISBN            | Title | Author          |
+-----------------+-------+-----------------+
| 9971                    | Dante Alighieri |
| -5-                     | Charles Dickens |
| 021                     |                 |
| 0-0                     |                 |
+-----------------+-------+-----------------+
| Dante Alighieri | 9971                    |
| Charles Dickens | -5-                     |
|                 | 021                     |
|                 | 0-0                     |
+-----------------+-------+-----------------+
| 9971                    | Dante           |
| -5-                     | Alighieri       |
| 021                     |                 |
| 0-0                     |                 |
+-----------------+-------+-----------------+

TABLE
], 'Cell with rowspan and colspan without using TableSeparator' => [['ISBN', 'Title', 'Author'], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("9971\n-5-\n021\n0-0", ['rowspan' => 2, 'colspan' => 2]), 'Dante Alighieri'], ['Charles Dickens'], ['Dante Alighieri', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("9971\n-5-\n021\n0-0", ['rowspan' => 2, 'colspan' => 2])], ['Charles Dickens']], 'default', <<<'TABLE'
+-----------------+-------+-----------------+
| ISBN            | Title | Author          |
+-----------------+-------+-----------------+
| 9971                    | Dante Alighieri |
| -5-                     | Charles Dickens |
| 021                     |                 |
| 0-0                     |                 |
| Dante Alighieri | 9971                    |
| Charles Dickens | -5-                     |
|                 | 021                     |
|                 | 0-0                     |
+-----------------+-------+-----------------+

TABLE
], 'Cell with rowspan and colspan with separator inside a rowspan' => [['ISBN', 'Author'], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('9971-5-0210-0', ['rowspan' => 3, 'colspan' => 1]), 'Dante Alighieri'], [new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator()], ['Charles Dickens']], 'default', <<<'TABLE'
+---------------+-----------------+
| ISBN          | Author          |
+---------------+-----------------+
| 9971-5-0210-0 | Dante Alighieri |
|               |-----------------|
|               | Charles Dickens |
+---------------+-----------------+

TABLE
], 'Multiple header lines' => [[[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Main title', ['colspan' => 3])], ['ISBN', 'Title', 'Author']], [], 'default', <<<'TABLE'
+------+-------+--------+
| Main title            |
+------+-------+--------+
| ISBN | Title | Author |
+------+-------+--------+

TABLE
], 'Row with multiple cells' => [[], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('1', ['colspan' => 3]), new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('2', ['colspan' => 2]), new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('3', ['colspan' => 2]), new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('4', ['colspan' => 2])]], 'default', <<<'TABLE'
+---+--+--+---+--+---+--+---+--+
| 1       | 2    | 3    | 4    |
+---+--+--+---+--+---+--+---+--+

TABLE
], 'Coslpan and table cells with comment style' => [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('<comment>Long Title</comment>', ['colspan' => 3])], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('9971-5-0210-0', ['colspan' => 3])], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['Dante Alighieri', 'J. R. R. Tolkien', 'J. R. R']], 'default', <<<TABLE
+-----------------+------------------+---------+
|\33[32m \33[39m\33[33mLong Title\33[39m\33[32m                                   \33[39m|
+-----------------+------------------+---------+
| 9971-5-0210-0                                |
+-----------------+------------------+---------+
| Dante Alighieri | J. R. R. Tolkien | J. R. R |
+-----------------+------------------+---------+

TABLE
, \true], 'Row with formatted cells containing a newline' => [[], [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('<error>Dont break' . "\n" . 'here</error>', ['colspan' => 2])], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['foo', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('<error>Dont break' . "\n" . 'here</error>', ['rowspan' => 2])], ['bar']], 'default', <<<'TABLE'
+-------+------------+
[39;49m| [39;49m[37;41mDont break[39;49m[39;49m         |[39;49m
[39;49m| [39;49m[37;41mhere[39;49m               |
+-------+------------+
[39;49m| foo   | [39;49m[37;41mDont break[39;49m[39;49m |[39;49m
[39;49m| bar   | [39;49m[37;41mhere[39;49m       |
+-------+------------+

TABLE
, \true]];
    }
    public function testRenderMultiByte()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['■■'])->setRows([[1234]])->setStyle('default');
        $table->render();
        $expected = <<<'TABLE'
+------+
| ■■   |
+------+
| 1234 |
+------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testTableCellWithNumericIntValue()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setRows([[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell(12345)]]);
        $table->render();
        $expected = <<<'TABLE'
+-------+
| 12345 |
+-------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testTableCellWithNumericFloatValue()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setRows([[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell(12345.01)]]);
        $table->render();
        $expected = <<<'TABLE'
+----------+
| 12345.01 |
+----------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testStyle()
    {
        $style = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $style->setHorizontalBorderChar('.')->setVerticalBorderChar('.')->setCrossingChar('.');
        \MolliePrefix\Symfony\Component\Console\Helper\Table::setStyleDefinition('dotfull', $style);
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['Foo'])->setRows([['Bar']])->setStyle('dotfull');
        $table->render();
        $expected = <<<'TABLE'
.......
. Foo .
.......
. Bar .
.......

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testRowSeparator()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['Foo'])->setRows([['Bar1'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['Bar2'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), ['Bar3']]);
        $table->render();
        $expected = <<<'TABLE'
+------+
| Foo  |
+------+
| Bar1 |
+------+
| Bar2 |
+------+
| Bar3 |
+------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
        $this->assertEquals($table, $table->addRow(new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator()), 'fluent interface on addRow() with a single TableSeparator() works');
    }
    public function testRenderMultiCalls()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setRows([[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('foo', ['colspan' => 2])]]);
        $table->render();
        $table->render();
        $table->render();
        $expected = <<<TABLE
+----+---+
| foo    |
+----+---+
+----+---+
| foo    |
+----+---+
+----+---+
| foo    |
+----+---+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testColumnStyle()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['ISBN', 'Title', 'Author', 'Price'])->setRows([['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri', '9.95'], ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens', '139.25']]);
        $style = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $style->setPadType(\STR_PAD_LEFT);
        $table->setColumnStyle(3, $style);
        $table->render();
        $expected = <<<TABLE
+---------------+----------------------+-----------------+--------+
| ISBN          | Title                | Author          |  Price |
+---------------+----------------------+-----------------+--------+
| 99921-58-10-7 | Divine Comedy        | Dante Alighieri |   9.95 |
| 9971-5-0210-0 | A Tale of Two Cities | Charles Dickens | 139.25 |
+---------------+----------------------+-----------------+--------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testThrowsWhenTheCellInAnArray()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Console\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('A cell must be a TableCell, a scalar or an object implementing "__toString()", "array" given.');
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['ISBN', 'Title', 'Author', 'Price'])->setRows([['99921-58-10-7', [], 'Dante Alighieri', '9.95']]);
        $table->render();
    }
    public function testColumnWidth()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['ISBN', 'Title', 'Author', 'Price'])->setRows([['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri', '9.95'], ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens', '139.25']])->setColumnWidth(0, 15)->setColumnWidth(3, 10);
        $style = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $style->setPadType(\STR_PAD_LEFT);
        $table->setColumnStyle(3, $style);
        $table->render();
        $expected = <<<TABLE
+-----------------+----------------------+-----------------+------------+
| ISBN            | Title                | Author          |      Price |
+-----------------+----------------------+-----------------+------------+
| 99921-58-10-7   | Divine Comedy        | Dante Alighieri |       9.95 |
| 9971-5-0210-0   | A Tale of Two Cities | Charles Dickens |     139.25 |
+-----------------+----------------------+-----------------+------------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testColumnWidths()
    {
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setHeaders(['ISBN', 'Title', 'Author', 'Price'])->setRows([['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri', '9.95'], ['9971-5-0210-0', 'A Tale of Two Cities', 'Charles Dickens', '139.25']])->setColumnWidths([15, 0, -1, 10]);
        $style = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $style->setPadType(\STR_PAD_LEFT);
        $table->setColumnStyle(3, $style);
        $table->render();
        $expected = <<<TABLE
+-----------------+----------------------+-----------------+------------+
| ISBN            | Title                | Author          |      Price |
+-----------------+----------------------+-----------------+------------+
| 99921-58-10-7   | Divine Comedy        | Dante Alighieri |       9.95 |
| 9971-5-0210-0   | A Tale of Two Cities | Charles Dickens |     139.25 |
+-----------------+----------------------+-----------------+------------+

TABLE;
        $this->assertEquals($expected, $this->getOutputContent($output));
    }
    public function testIsNotDefinedStyleException()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Console\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Style "absent" is not defined.');
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($this->getOutputStream());
        $table->setStyle('absent');
    }
    public function testGetStyleDefinition()
    {
        $this->expectException('MolliePrefix\\Symfony\\Component\\Console\\Exception\\InvalidArgumentException');
        $this->expectExceptionMessage('Style "absent" is not defined.');
        \MolliePrefix\Symfony\Component\Console\Helper\Table::getStyleDefinition('absent');
    }
    public function testBoxedStyleWithColspan()
    {
        $boxed = new \MolliePrefix\Symfony\Component\Console\Helper\TableStyle();
        $boxed->setHorizontalBorderChar('─')->setVerticalBorderChar('│')->setCrossingChar('┼');
        $table = new \MolliePrefix\Symfony\Component\Console\Helper\Table($output = $this->getOutputStream());
        $table->setStyle($boxed);
        $table->setHeaders(['ISBN', 'Title', 'Author'])->setRows([['99921-58-10-7', 'Divine Comedy', 'Dante Alighieri'], new \MolliePrefix\Symfony\Component\Console\Helper\TableSeparator(), [new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('This value spans 3 columns.', ['colspan' => 3])]]);
        $table->render();
        $expected = <<<TABLE
┼───────────────┼───────────────┼─────────────────┼
│ ISBN          │ Title         │ Author          │
┼───────────────┼───────────────┼─────────────────┼
│ 99921-58-10-7 │ Divine Comedy │ Dante Alighieri │
┼───────────────┼───────────────┼─────────────────┼
│ This value spans 3 columns.                     │
┼───────────────┼───────────────┼─────────────────┼

TABLE;
        $this->assertSame($expected, $this->getOutputContent($output));
    }
    protected function getOutputStream($decorated = \false)
    {
        return new \MolliePrefix\Symfony\Component\Console\Output\StreamOutput($this->stream, \MolliePrefix\Symfony\Component\Console\Output\StreamOutput::VERBOSITY_NORMAL, $decorated);
    }
    protected function getOutputContent(\MolliePrefix\Symfony\Component\Console\Output\StreamOutput $output)
    {
        \rewind($output->getStream());
        return \str_replace(\PHP_EOL, "\n", \stream_get_contents($output->getStream()));
    }
}

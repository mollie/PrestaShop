<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Helper\TableCell;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle;
//Ensure formatting tables when using multiple headers with TableCell
return function (\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output) {
    $headers = [[new \MolliePrefix\Symfony\Component\Console\Helper\TableCell('Main table title', ['colspan' => 3])], ['ISBN', 'Title', 'Author']];
    $rows = [['978-0521567817', 'De Monarchia', new \MolliePrefix\Symfony\Component\Console\Helper\TableCell("Dante Alighieri\nspans multiple rows", ['rowspan' => 2])], ['978-0804169127', 'Divine Comedy']];
    $output = new \MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
    $output->table($headers, $rows);
};

<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle;
//Ensure that questions have the expected outputs
return function (\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output) {
    $output = new \MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
    $stream = \fopen('php://memory', 'r+', \false);
    \fwrite($stream, "Foo\nBar\nBaz");
    \rewind($stream);
    $input->setStream($stream);
    $output->ask('What\'s your name?');
    $output->ask('How are you?');
    $output->ask('Where do you come from?');
};

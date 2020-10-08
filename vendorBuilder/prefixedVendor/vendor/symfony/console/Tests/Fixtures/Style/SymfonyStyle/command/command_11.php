<?php

namespace MolliePrefix;

use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle;
// ensure long words are properly wrapped in blocks
return function (\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output) {
    $word = 'Lopadotemachoselachogaleokranioleipsanodrimhypotrimmatosilphioparaomelitokatakechymenokichlepikossyphophattoperisteralektryonoptekephalliokigklopeleiolagoiosiraiobaphetraganopterygon';
    $sfStyle = new \MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle($input, $output);
    $sfStyle->block($word, 'CUSTOM', 'fg=white;bg=blue', ' § ', \false);
};

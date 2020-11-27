<?php

$finder = PhpCsFixer\Finder::create()
	->exclude(array('.git', 'deploy', 'bin', 'mails', 'translations', 'vendor', 'views', 'vendorBuilder'))
	->in(__DIR__);

return PhpCsFixer\Config::create()
	->setIndent("\t")
	->setRules([
		'@PSR2' => true,
		'@Symfony' => true
	])
	->setFinder($finder);

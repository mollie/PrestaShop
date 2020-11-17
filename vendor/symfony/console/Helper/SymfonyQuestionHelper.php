<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace MolliePrefix\Symfony\Component\Console\Helper;

use MolliePrefix\Symfony\Component\Console\Exception\LogicException;
use MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter;
use MolliePrefix\Symfony\Component\Console\Input\InputInterface;
use MolliePrefix\Symfony\Component\Console\Output\OutputInterface;
use MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion;
use MolliePrefix\Symfony\Component\Console\Question\ConfirmationQuestion;
use MolliePrefix\Symfony\Component\Console\Question\Question;
use MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * Symfony Style Guide compliant question helper.
 *
 * @author Kevin Bond <kevinbond@gmail.com>
 */
class SymfonyQuestionHelper extends \MolliePrefix\Symfony\Component\Console\Helper\QuestionHelper
{
    /**
     * {@inheritdoc}
     *
     * To be removed in 4.0
     */
    public function ask(\MolliePrefix\Symfony\Component\Console\Input\InputInterface $input, \MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, \MolliePrefix\Symfony\Component\Console\Question\Question $question)
    {
        $validator = $question->getValidator();
        $question->setValidator(function ($value) use($validator) {
            if (null !== $validator) {
                $value = $validator($value);
            } else {
                // make required
                if (!\is_array($value) && !\is_bool($value) && 0 === \strlen($value)) {
                    @\trigger_error('The default question validator is deprecated since Symfony 3.3 and will not be used anymore in version 4.0. Set a custom question validator if needed.', \E_USER_DEPRECATED);
                    throw new \MolliePrefix\Symfony\Component\Console\Exception\LogicException('A value is required.');
                }
            }
            return $value;
        });
        return parent::ask($input, $output, $question);
    }
    /**
     * {@inheritdoc}
     */
    protected function writePrompt(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, \MolliePrefix\Symfony\Component\Console\Question\Question $question)
    {
        $text = \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escapeTrailingBackslash($question->getQuestion());
        $default = $question->getDefault();
        switch (\true) {
            case null === $default:
                $text = \sprintf(' <info>%s</info>:', $text);
                break;
            case $question instanceof \MolliePrefix\Symfony\Component\Console\Question\ConfirmationQuestion:
                $text = \sprintf(' <info>%s (yes/no)</info> [<comment>%s</comment>]:', $text, $default ? 'yes' : 'no');
                break;
            case $question instanceof \MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion && $question->isMultiselect():
                $choices = $question->getChoices();
                $default = \explode(',', $default);
                foreach ($default as $key => $value) {
                    $default[$key] = $choices[\trim($value)];
                }
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape(\implode(', ', $default)));
                break;
            case $question instanceof \MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion:
                $choices = $question->getChoices();
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape(isset($choices[$default]) ? $choices[$default] : $default));
                break;
            default:
                $text = \sprintf(' <info>%s</info> [<comment>%s</comment>]:', $text, \MolliePrefix\Symfony\Component\Console\Formatter\OutputFormatter::escape($default));
        }
        $output->writeln($text);
        $prompt = ' > ';
        if ($question instanceof \MolliePrefix\Symfony\Component\Console\Question\ChoiceQuestion) {
            $output->writeln($this->formatChoiceQuestionChoices($question, 'comment'));
            $prompt = $question->getPrompt();
        }
        $output->write($prompt);
    }
    /**
     * {@inheritdoc}
     */
    protected function writeError(\MolliePrefix\Symfony\Component\Console\Output\OutputInterface $output, \Exception $error)
    {
        if ($output instanceof \MolliePrefix\Symfony\Component\Console\Style\SymfonyStyle) {
            $output->newLine();
            $output->error($error->getMessage());
            return;
        }
        parent::writeError($output, $error);
    }
}

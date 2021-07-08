<?php

declare(strict_types=1);

namespace UnicoETL;

use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;
use UnicoETL\Enums\SupportedFiles;
use UnicoETL\Helpers\RemoteZipFileManager;
use UnicoETL\Parsers\Fair2014;

/**
 * Class ImportFairsCommand
 * Implements all the logic for importing Sao Paulo's Open Fairs
 * data inside a relational database.
 *
 * Currently supported files: `DEINFO_AB_FEIRASLIVRES_2014.csv`
 *
 * @package UnicoETL
 * @author Nick Moraes <contato@nickgomes.dev>
 * @version 1.0
 * @access public
 * @license https://creativecommons.org/licenses/by-nc/3.0/
 */
final class ImportFairsCommand extends Command
{
    /**
     * Defines the source URL containing a .zip format
     * file that has all the available Open Fairs files.
     */
    private const REMOTE_SOURCE_URL = 'http://www.prefeitura.sp.gov.br/cidade/secretarias/upload/chamadas/feiras_livres_1429113213.zip';

    /**
     * Defines the command name to be used as CLI parameter.
     */
    protected static $defaultName = 'import-fairs';

    /**
     * Scaffold the ETL process.
     *
     * @param InputInterface $input The Symfony CLI input interface
     * @param OutputInterface $output The Symfony CLI output interface
     *
     * @throws Exception
     *
     * @return int UNIX status code
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        /** Cosmetic stuff */
        $io = new SymfonyStyle(input: $input, output: $output);
        $io->writeln('Starting ETL process...');
        $io->newLine(2);

        /** Query the user for the file to perform ETL */
        $fileName = $this->askUserForFileInput(input: $input, output: $output);

        $io->newLine(2);

        /** Attempts to download the remote source .zip and find the user queried file */
        $manager = new RemoteZipFileManager();
        $io->writeln('[File Manager] Remote file downloading...');
        $fileContents = $manager->getRemoteFile(url: self::REMOTE_SOURCE_URL)
            ->extractFile(fileName: $fileName);
        $io->writeln('[File Manager] Remote file downloaded!');

        /** Switch to determine which parser should be used to evaluate the file */
        switch ($fileName) {
            case SupportedFiles::FAIRS_2014:
                $io->writeln('[File Parser] Running...');
                $parser = new Fair2014($fileContents);
                $io->writeln('[File Parser] Finished!');

                $io->writeln('[Database] Inserting records...');
                $parser->output();
                $io->writeln('[Database] Finished!');
                break;
            default:
                throw new Exception('File not available for ETL');
        }

        $io->newLine(2);
        $io->writeln('ETL process successfull!');
        return Command::SUCCESS;
    }

    /**
     * Query the user to select a file for performing ETL on.
     *
     * @param InputInterface $input The Symfony CLI input interface
     * @param OutputInterface $output The Symfony CLI output interface
     *
     * @return string The selected file name
     */
    private function askUserForFileInput(InputInterface $input, OutputInterface $output): string
    {
        $helper = $this->getHelper('question');
        $question = new ChoiceQuestion(
            question: 'Please select a remote file to perform ETL',
            choices: SupportedFiles::getConstants()
        );
        return $helper->ask(
            input: $input,
            output: $output,
            question: $question
        );
    }
}

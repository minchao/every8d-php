<?php

namespace Every8d\Console\Command;

use Every8d\Console\ClientTrait;
use Every8d\Console\HelperTrait;
use Every8d\Message;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\VarDumper\VarDumper;

class Send extends Command
{
    use ClientTrait, HelperTrait;

    protected function configure()
    {
        $this->setName('send')
            ->setDescription('發送 SMS')
            ->setDefinition(
                new InputDefinition([
                    new InputArgument(
                        'to',
                        InputArgument::REQUIRED,
                        'The destination phone number'
                    ),
                    new InputArgument(
                        'content',
                        InputArgument::REQUIRED,
                        'Message content'
                    ),
                    new InputOption(
                        'subject',
                        's',
                        InputOption::VALUE_OPTIONAL,
                        'Message subject'
                    ),
                    new InputOption(
                        'reservationTime',
                        'R',
                        InputOption::VALUE_OPTIONAL,
                        'Reservation time'
                    ),
                    new InputOption(
                        'retryTime',
                        'r',
                        InputOption::VALUE_OPTIONAL,
                        'SMS validity period of unit: minutes'
                    ),
                    new InputOption(
                        'id',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Message record ID'
                    ),
                ])
            );
    }


    public function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $to = $input->getArgument('to');
        $content = $input->getArgument('content');

        $client = $this->createClient($input);

        $sms = new Message\SMS($to, $content);
        $sms->subject = $input->getOption('subject');
        $sms->reservationTime = $input->getOption('reservationTime');
        $sms->retryTime = $input->getOption('retryTime');
        $sms->id = $input->getOption('id');

        try {
            $resp = $client->getApi()->sendSMS($sms);

            VarDumper::dump($resp);

            return null;
        } catch (\Exception $e) {
            $io->error([$this->getErrorMessage($e)]);

            return 1;
        }
    }
}

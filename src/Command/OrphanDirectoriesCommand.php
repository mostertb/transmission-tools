<?php

namespace Mostertb\TransmissionTools\Command;


use Mostertb\TransmissionTools\Helper\Config\Config;
use Mostertb\TransmissionTools\Helper\Humanizer;
use Mostertb\TransmissionTools\Helper\TranmissionApi\TransmissionApiClientFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

class OrphanDirectoriesCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('orphan-directories:list')
            ->setDescription('Finds directories under the provided path that are not associated with a torrent')
            ->addOption('path', null, InputOption::VALUE_REQUIRED, 'Path to evaluate directories under')
            ->addOption('names-only', null, InputOption::VALUE_NONE,
                'Only print the directory names without detailed information');
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->acquireLock();
        parent::initialize($input, $output);
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $path = realpath($input->getOption('path'));
        $output->writeln('Evaluating: '.$path);

        $namesOnly = $input->getOption('names-only') ? true : false;
        if($namesOnly){
            $output->writeln('Running in \'names-only\' mode');
        }

        $finder = new Finder();
        $finder->directories()->depth('< 1')->in($path);
        if(!$finder->hasResults()){
            throw new \Exception('No directories found in: '.$path);
        }

        $clientConfigs = Config::getInstance()->getClientConfigs(true);
        $torrentDirectories = [];
        foreach ($clientConfigs as $clientConfig){
            $client = TransmissionApiClientFactory::makeApiClient(
                $clientConfig['host'],
                $clientConfig['port'],
                $clientConfig['username'],
                $clientConfig['password']
            );
            $output->writeln('Getting torrents from: '.$clientConfig['name']);

            foreach ($client->all() as $torrent){
                if(strpos($torrent->getDownloadDir(), $path) === 0){ // torrent is under the path we are evaluating
                    $torrentDirectories[] = $torrent->getDownloadDir();
                }
            }
        }

        if(empty($torrentDirectories)){
            $output->writeln('<error>Clients checked have no torrents under: '.$path.'</error>');
        }

        foreach ($finder as $file){
            if(!in_array($file->getPathname(), $torrentDirectories)){
                if($namesOnly){
                    $output->writeln($file->getPathname());
                } else {
                    $output->writeln(str_pad(Humanizer::bytes($file->getSize()), 12).$file->getPathname());
                }
            }
        }

        return 0;
    }
}
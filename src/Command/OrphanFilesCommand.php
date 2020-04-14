<?php

namespace Mostertb\TransmissionTools\Command;


use Mostertb\TransmissionTools\Helper\Config\Config;
use Mostertb\TransmissionTools\Helper\Humanizer;
use Mostertb\TransmissionTools\Helper\TranmissionApi\TransmissionApiClientFactory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Transmission\Model\File;

class OrphanFilesCommand extends AbstractCommand
{
    protected function configure()
    {
        $this->setName('orphan:files-list')
            ->setDescription('For all the torrents managed by a client, find any files in each torrent\'s directory '.
                'that are not part of that torrent')
            ->addOption('client-name', null, InputOption::VALUE_REQUIRED,
                'Torrent client to scan for orphaned files')
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
        $clientConfig = Config::getInstance()->getClientConfigByName($input->getOption('client-name'));
        $client = TransmissionApiClientFactory::makeApiClient(
            $clientConfig['host'],
            $clientConfig['port'],
            $clientConfig['username'],
            $clientConfig['password']
        );
        $output->writeln('Processing torrents from: '.$clientConfig['name']);

        $totalBytes = 0;
        foreach ($client->all() as $torrent){
            if(!$torrent->isSeeding()){
                continue; // skip incomplete torrents
            }

            if(count($torrent->getFiles()) == 1 && ($torrent->getFiles()[0])->getName() == $torrent->getName()){
                continue; // Single file torrent
            }

            $torrentDirectory = $torrent->getDownloadDir().'/'.$torrent->getName().'/';

            // Get files in the Torrent's directory on disk
            try{
                $finder = new Finder();
                $finder->files()->in($torrentDirectory)->size('> 100mi');
            } catch (\Exception $e){
                $output->writeln('ERROR searching files: '.$e->getMessage());
                continue;
            }

            if(!$finder->hasResults()){
                continue; // no large files found - continue
            }

            // Get list of names of files that are mean to be in the torrent
            $transmissionFileNames = [];
            foreach ($torrent->getFiles() as $transmissionFile){
                $transmissionFileNames[] = $transmissionFile;
            }

            // Look for files on disk that are not part of the torrent
            foreach ($finder as $fileOnDisk){
                $relativeFilename = str_replace($torrent->getDownloadDir().'/', '', $fileOnDisk->getPathname());

                if(!in_array($relativeFilename, $transmissionFileNames)){
                    $output->writeln(str_pad(Humanizer::bytes($fileOnDisk->getSize()), 12).$fileOnDisk->getPathname());
                    $totalBytes += $fileOnDisk->getSize();
                }
            }
        }


        $output->writeln('Total orphaned size: '.Humanizer::bytes($totalBytes));

        return 0;
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: gtostain
 * Date: 23/05/2016
 * Time: 17:10
 */
namespace Ens\TostainGuillaumeBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Ens\TostainGuillaumeBundle\Entity\Job;

class JobeetCleanupCommand extends ContainerAwareCommand {

    protected function configure()
    {
        $this
            ->setName('ens:jobeet:cleanup')
            ->setDescription('Cleanup Jobeet database')
            ->addArgument('days', InputArgument::OPTIONAL, 'The email', 90)
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = $input->getArgument('days');

        $em = $this->getContainer()->get('doctrine')->getEntityManager();
        $nb = $em->getRepository('EnsTostainGuillaumeBundle:Job')->cleanup($days);

        $output->writeln(sprintf('Removed %d stale jobs', $nb));
    }
}
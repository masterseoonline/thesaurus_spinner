<?php

namespace EcService\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/*
 * @author Elvis Ciotti <info@info@softwareengineeringsolutions.co.uk>
 *
 * # regenerate db
 * php -d memory_limit=4024M app/console thesaurus --regeneratedb
 *
 * # test
 * bin/console fc:thesaurus --locale=en_GB --text=home
 */
class ThesaurusCommand extends ContainerAwareCommand
{

    protected function configure()
    {
        $this
                ->setName('thesaurus')
                ->addOption('regeneratedb', null, InputOption::VALUE_NONE)
                ->addOption('locale', null, InputOption::VALUE_OPTIONAL)
                ->addOption('text', null, InputOption::VALUE_OPTIONAL)
                ->addOption('debug', null, InputOption::VALUE_NONE)
                ->addOption('prob', null, InputOption::VALUE_OPTIONAL)
                ->addOption('seed', null, InputOption::VALUE_OPTIONAL)
                ->setDescription(
                    "Thesaurus\n"
                    . "  bin/console fc:thesaurus --locale=en_GB --text=home\n"
                    . "    Input : text (STDIN)\n"
                    . "    Output: text (translated)"
                )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $t = $this->getContainer()->get('thesaurus_db');
        
        if ($input->getOption('regeneratedb')) {
            $locales = $input->getOption('locale') 
                    ? [$input->getOption('locale')] 
                    : ['it_IT','en_GB','en_US','fr_FR','de_DE','es_ES'];
            foreach ($locales as $locale) {
                // https://sourceforge.net/projects/lyxwininstaller/files/thesaurus/
                $output->write("Recreate db $locale ...");
                $t->regenerateDb($locale, "th_{$locale}_v2.dat.gz", $output);
                $output->writeln('done');
            }
            $output->writeln('done');
            return;
        }
        
        /* @var $tExt \EcService\Tools\Thesaurus\ThesaurusTwigExtension   */
        $tExt = $this->getContainer()->get('twig.thesaurusExtension');
        if ($locale = $input->getOption('locale')) {
            $text = $input->getOption('text') ?: $this->getStdin();
            $out = $tExt->synonymText($locale, $text, [
                'probability' => $input->getOption('prob'),
                'seed' => $input->getOption('seed'),
                'debug' => $input->getOption('debug')
            ]);
            $output->writeln($out);
        }
    }
    
    private function getStdin()
    {
        $lines = [];
        $handle = fopen('php://stdin', 'r');
        while (!feof($handle)) {
            $lines[] = fgets($handle);
        }
        fclose($handle);

        return implode("\n", $lines);
    }

}

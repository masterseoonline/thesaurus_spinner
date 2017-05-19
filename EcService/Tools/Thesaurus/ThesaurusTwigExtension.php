<?php

namespace EcService\Tools\Thesaurus;

use Psr\Log\LoggerInterface;

/**
 * Text functions for Twig
 *
 * @author Elvis Ciotti <info@info@softwareengineeringsolutions.co.uk>
 */
class ThesaurusTwigExtension extends \Twig_Extension
{
    // skip thesaurus for words shorter than this. otherwise "as" get translate into 'chemical element"
    const EN_MIN_LENGTH = 3;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ThesaurusDb
     */
    private $thesaurusDb;


    public function __construct(ThesaurusDb $thesaurus, LoggerInterface $logger)
    {
        $this->thesaurusDb = $thesaurus;
        $this->logger = $logger;
    }


    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return [
            'thesaurus'      => new \Twig_SimpleFilter('thesaurus', function ($text, $locale, array $options = []) {
                return $this->synonymText($locale, $text, $options);
            }),
        ];
    }

    /**
     * Options
     * - probability default 0.1
     * - debug default false
     * - seed default false
     *
     * @param $locale
     * @param $text
     * @param array $options
     * @return string
     */
    public function synonymText($locale, $text, array $options = [])
    {
        $ret = [];
        $pieces = array_values(array_filter(explode(' ', $text))); // filter and reset keys
        for ($i = 0; $i < count($pieces); $i++) {
            $word = $pieces[$i];

            // try two words
            if (isset($pieces[$i + 1])) {
                $twoWords = $pieces[$i] . ' ' . $pieces[$i + 1];
                $twoWordsSyn = $this->synonySingle($locale, $twoWords, $options);

                if ($twoWordsSyn !== null) {
                    $ret[] = $twoWordsSyn;
                    $i++; //skip next loop
                    continue;
                }
            }

            $ret[] = $this->synonySingle($locale, $word, $options) ?: $word;
        }

        return implode(' ', $ret);
    }


    private function synonySingle($locale, $word, array $options)
    {
        $probability = $options['probability'] ?? 0.1;
        $debugMode = $options['debug'] ?? false;
        $seed = $options['seed'] ?? false;

        // skip 1 and 2 char-length english words, normally shit thesaurus
        if (substr($locale, 0, 2) == 'en' && strlen($word) < self::EN_MIN_LENGTH) {
            return null;
        }
        $syn = $this->thesaurusDb->find($locale, $word);
        if (count($syn) === 0) {
            return null; // no syn => exit
        }

        $rand = mt_rand(0, 100) / 100;
        if ($rand > $probability) {
            return null; // probability
        }

        $ret = array_rand($syn); // take a random word

        if ($debugMode) {
            $ret .= " <!-- $word -->";
        }

        //$this->logger->info("Thesaurus: [$locale]: $word -> $ret");

        return $ret;
    }


    /**
     * Return extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'thesaurus';
    }

}
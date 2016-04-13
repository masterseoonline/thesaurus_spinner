<?php

namespace EcService\Tools\Thesaurus;

class ThesaurusDbTest extends \PHPUnit_Framework_TestCase
{


    public static function testfindProvider()
    {
        return [
            ['it_IT', 'avvilire', ['degradare', 'deprezzare', 'sminuire', 'svalutare', 'togliere pregio']],
            ['en_GB', 'womanhood', ['fair sex', 'class', 'social class', 'socio-economic class']],
        ];
    }


    public function setUp()
    {
        $this->t = new ThesaurusDb('1024M');
    }


    /**
     * @dataProvider testfindProvider
     */
    public function testfind($locale, $input, $expectedOuput)
    {
        $this->markTestSkipped('move to http://php.net/manual/en/sqlite3.construct.php o mangia troopa RAM');

        $actual = $this->t->find($locale, $input);

        $this->assertEquals($expectedOuput, $actual);
    }


    public static function dictProvider()
    {
        return [
            ['en_GB', [
                    '\'hood' => [
                        'vicinity' => 'noun',
                        'locality' => 'noun',
                        'neck of the woods' => 'noun',
                        'neighbourhood' => 'noun',
                    ],
                    'prolate' => [
                        'rounded' => 'adj',
                        'watermelon-shaped' => 'adj',
                        'egg-shaped' => 'adj',
                        'elliptic' => 'adj',
                    ],
                    'prolate cycloid' => [
                        'cycloid' => 'noun',
                    ],
                    'quintet' => [
                        'quintette' => 'noun',
                        'composition' => 'noun',
                        'musical composition' => 'noun',
                        'opus' => 'noun',
                        'piece' => 'noun',
                        'piece of music' => 'noun',
                        'five' => 'noun',
                        '5' => 'noun',
                    ]]],
            ['it_IT', [
                    'a' => [
                        'per' => 'cong.',
                        'verso' => 'prep.'
                    ],
                    'abate' => [
                        'priore' => 's.m.',
                        'superiore' => 's.m.'
                    ],
                    'abbacinamento' => [
                        'abbagliamento' => "s.m.",
                        'abbaglio' => "s.m.",
                        'miraggio' => "s.m.",
                        'visione' => "s.m.",
                    ],
                ]],
            ['fr_FR', [
                    'Ascension' => [ // keep case
                        'assomption' => '?',
                        'élévation' => '?'
                    ],
                    'paysagiste' => [
                        'peintre' => 'adjectif nom',
                        'artiste' => 'adjectif nom',
                    ],
                    'peignée' => [
                        'raclée' => 'adjectif nom verbe'
                    ]
                ]
            ],
        ];
    }


    /**
     * @dataProvider dictProvider   
     */
    public function testloadDictionaryFromFile($locale, $expected)
    {
        $actual = $this->t->loadDictionaryFromFile($locale, __DIR__ . "/th_{$locale}_v2.dat");

        $this->assertEquals($expected, $actual);
    }

}
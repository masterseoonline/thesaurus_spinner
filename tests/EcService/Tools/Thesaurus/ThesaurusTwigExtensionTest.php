<?php

namespace Ec\Twig;

use Mockery as m;

class ThesaurusTwigExtensionTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $m = m::mock('EcService\Tools\Thesaurus\ThesaurusDb');
        $m->shouldReceive('find')->with('it_IT', 'seed')->andReturn(['seed1'=>'s.m.', 'seed2'=>'s.m.']);
        $m->shouldReceive('find')->with('it_IT', 'casa')->andReturn(['abitazione'=>'s.m.']);
        $m->shouldReceive('find')->with('it_IT', 'casa brutta')->andReturn(['abitazione'=>'s.m.']);
        $m->shouldReceive('find')->with('it_IT', '1')->andReturn(['uno'=>'s.m.']);
        
        $m->shouldIgnoreMissing();
        
        $logger = m::mock('Psr\Log\LoggerInterface')->shouldIgnoreMissing();
        
        $this->object = new \EcService\Tools\Thesaurus\ThesaurusTwigExtension($m, $logger);
    }
    
    
    public static function thesaurusProvider()
    {
        return [
            // empty
            ['', ''],
            // simple
            ['casa casa', 'abitazione abitazione'],
            // skip Upper case things
            ['casa Casa', 'abitazione Casa'],
            // two words
            ['1 casa brutta', 'uno abitazione'],
            ['casa brutta casa', 'abitazione abitazione'],
            ['casa 1 brutta casa', 'abitazione uno brutta abitazione'],
            ['casa brutta casa brutta 1',  'abitazione abitazione uno'],
        ];
    }
    
    /**
     * @dataProvider thesaurusProvider
     */
    public function testThesaurusProba($text, $expected)
    {
        $this->markTestSkipped('use cmd to test e.g. bin/console thesaurus --locale=en_GB --text=home --prob=1 --seed=');

        $locale = 'it_IT';

        $f = $this->object->getFilters()['thesaurus']->getCallable();

        $this->assertSame($expected, $f($text, $locale, ['probability' => 1]));
        $this->assertSame($text, $f($text, $locale, ['probability' => -1])); //check that with no probability, nth happens
    }

    public function testThesaurusSeed()
    {
        $this->markTestSkipped('use cmd to test e.g. bin/console thesaurus --locale=en_GB --text=home --prob=1 --seed=');
        $f = $this->object->getFilters()['thesaurus']->getCallable();

        // test with same seed
        $ret = [];
        $i = 100; while($i--){
            $ret[]= $f('seed seed seed', 'it_IT', ['seed' => 1]);
        }
        $ret = array_unique($ret);
        $this->assertCount(1, array_unique($ret));

        // test with different seed
        $ret = [];
        $i = 100; while($i--){
        $ret[]= $f('seed seed seed', 'it_IT', ['seed' => $i]);
    }
        $ret = array_unique($ret);
        $this->assertTrue(count($ret) > 1);
    }
    
    
}

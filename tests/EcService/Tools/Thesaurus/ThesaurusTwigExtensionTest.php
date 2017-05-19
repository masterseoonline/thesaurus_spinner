<?php

namespace Ec\Twig;

use Mockery as m;

class ThesaurusTwigExtensionTest extends \PHPUnit_Framework_TestCase {

    public function setUp()
    {
        $m = m::mock('EcService\Tools\Thesaurus\ThesaurusDb');
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
    public function testthesaurus($text, $expected)
    {
        $locale = 'it_IT';

        $f = $this->object->getFilters()['thesaurus']->getCallable();
        
        $this->assertSame($expected, $f($text, $locale, ['probability' => 1]));
        $this->assertSame($text, $f($text, $locale, ['probability' => 0])); //check that with no probability, nth happens
    }
    
    
}

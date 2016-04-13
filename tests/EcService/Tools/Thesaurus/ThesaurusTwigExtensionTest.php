<?php

namespace Ec\Twig;

use MockeryStub as m;

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
            ['', 'it_IT', 1, false, ''],
            // simple
            ['casa casa', 'it_IT', 1, false, 'abitazione abitazione'],
            // skip Upper case things
            ['casa Casa', 'it_IT', 1, false, 'abitazione Casa'],
            // two words
            ['1 casa brutta', 'it_IT', 1, false, 'uno abitazione'],
            ['casa brutta casa', 'it_IT', 1, false, 'abitazione abitazione'],
            ['casa 1 brutta casa', 'it_IT', 1, false, 'abitazione uno brutta abitazione'],
            ['casa brutta casa brutta 1', 'it_IT', 1, false, 'abitazione abitazione uno'],
        ];
    }
    
    /**
     * @dataProvider thesaurusProvider
     */
    public function testthesaurus($text, $locale, $probability, $debug, $expected)
    {
        
        $f = $this->object->getFilters()['thesaurus']->getCallable();
        
        $this->assertSame($expected, $f($text, $locale, $probability, $debug));
        $this->assertSame($text, $f($text, $locale, -1, $debug)); //check that with no probability, nth happens
    }
    
    
}

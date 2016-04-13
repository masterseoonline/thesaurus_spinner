<?php

namespace EcService\Tools\Thesaurus;

use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Helper\ProgressBar;

/**
 * @author Elvis Ciotti <info@info@softwareengineeringsolutions.co.uk>
 */
class ThesaurusDb
{

    private $dbs = [];

    public function regenerateDb($locale, $file, OutputInterface $output)
    {
        $data = $this->loadDictionaryFromFile($locale, __DIR__ . "/dict/dat/{$file}");
        $file = __DIR__ . "/dict/{$locale}.sqlite";
        @unlink($file);
        $db = new \SQLite3($file);

        // http://php.net/manual/en/book.sqlite3.php
        $db->exec('DROP TABLE IF EXISTS words');
        $db->exec('CREATE TABLE words (word TEXT, syn BLOB)');
        $db->exec('BEGIN TRANSACTION');
        $statement = $db->prepare('INSERT INTO words (word, syn) VALUES (:word, :syn);');

        $types = [];
        $progress = new ProgressBar($output, count($data));
        foreach ($data as $word => $syns) {
            $statement->bindValue(':word', $word);
            $statement->bindValue(':syn', base64_encode(gzcompress(serialize($syns), 9)));
            $statement->execute();
            $progress->advance();
        }
        $progress->finish();
        $db->exec('COMMIT');
        
        // write log
        file_put_contents($file . '.log', 
                count($data) . " words\n"
                . "Types: ". print_r($types, true)
                . print_r(array_slice($data, 0, 10), true)
        );

        $db->exec('CREATE INDEX word_idx ON words(word)');
    }

    private function getNextLine()
    {
        return fgetcsv($this->handle, 1000, '|');
    }

    /**
     * @return array e.g. [bifolcheria] => Array(cafoneria =>s.f., rozzezza=>s.f.)
     */
    public function loadDictionaryFromFile($locale, $file)
    {
        $this->handle = gzopen($file, "r");

        $ret = [];
        while (($data = $this->getNextLine()) !== false) { // quintet|5
            if (!isset($data[1]) || !ctype_digit($data[1])) { // skip if not a word (should only happen for 1st line or wrong files)
                continue;
            }
            $ret[$data[0]] = $this->readDefinitionsFromNextLines($locale, $data[1]);
        }
        fclose($this->handle);

        return $ret;
    }

    private function readDefinitionsFromNextLines($locale, $numberOfLinesToRead)
    {
        $removeTextAmongBrackets = function($w) {
            return trim(preg_replace('/\(.*\)/', '', $w)); //remove things in brackets. e.g. "imballare (antonym)"
        };

        $ret = [];
        while ($numberOfLinesToRead --) { // take next lines
            $nextLine = $this->getNextLine(true);
            $type = $this->parseType($locale, array_shift($nextLine));
            $wordsInCurrentLine = array_map($removeTextAmongBrackets, $nextLine);
            foreach ($wordsInCurrentLine as $w) {
                $ret[$w] = $type;
            }
        }
        return $ret;
    }

    private function parseType($locale, $string)
    {
        if ($locale == 'it_IT') {
            $string = trim(explode(' ', $string)[0], ','); // (agg., relativo a)
        }
        return strtolower(trim($string, '()'));
    }

    /**
     * @return array [  [per] => cong. [verso] => prep.)
     */

    /**
     * 
     * @param string $locale e.g. it_IT
     * @param string $word .e.g "a"
     * @return array e.g. ( [per] => cong. [verso] => prep.)
     */
    public function find($locale, $word)
    {
        // array cache
        if (!isset($this->dbs[$locale]->db)) {
            $dbFile = __DIR__ . '/dict/' . $locale . '.sqlite';
            if (!file_exists($dbFile)) {
                throw new \RuntimeException("$dbFile not found ! ");
            }
            $this->dbs[$locale]['db'] = new \SQLite3($dbFile);
            $this->dbs[$locale]['selectStmt'] = $this->dbs[$locale]['db']
                    ->prepare('SELECT syn FROM words WHERE word = :word');
        }
        $db = &$this->dbs[$locale];

        $db['selectStmt']->bindValue(':word', $word);
        $result = $db['selectStmt']->execute();
        $ret = $result->fetchArray(SQLITE3_ASSOC);
        if ($ret === false) {
            return [];
        }
        $ret = unserialize(gzuncompress(base64_decode($ret['syn'])));

        return $ret;
    }

}

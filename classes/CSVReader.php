<?php 
/**
 * CSVReader
 * Class to parse and iterate CSV files on the fly
 * @author      Mardix 
 */

namespace Talent_Age_Import\classes;

class CSVReader implements \Iterator
{

    private $delimiter;

    private $rowDelimiter;

    private $fileHandle = null;

    private $position = 0;

    private $data = array();

    /**
     * The constructor
     *
     * @param string $filename
     * @param string $delimiter
     * @param string $rowDelimiter
     *
     * @throws Exception
     */
    public function __construct($filename, $delimiter = ",", $rowDelimiter = "r")
    {
        $this->delimiter = $delimiter;

        $this->rowDelimiter = $rowDelimiter;

        $this->position = 0;
        
        $this->fileHandle = fopen($filename, $this->rowDelimiter);
        
        if ($this->fileHandle === FALSE) {
            throw new \Exception("Unable to open file: {$filename}");
        } else {
            $this->parseLine();
        }
    }

    /**
     * Destructor
     */
    public function __destruct()
    {
        if ($this->fileHandle) {
            fclose($this->fileHandle);
            $this->fileHandle = null;
        }
    }

    /**
     * Rewind iterator to the first element
     */
    public function rewind()
    {
        if ($this->fileHandle) {
            $this->position = 0;
            rewind($this->fileHandle);
        }
        $this->parseLine();
    }

    /**
     * Return the current row
     *
     * @return array
     */
    public function current()
    {
        return $this->data;
    }

    /**
     * Return the key of the current row
     *
     * @return int
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * Move forward to the next element
     */
    public function next()
    {
        $this->position++;
        $this->parseLine();
    }

    /**
     * Check if current position is valid
     *
     * @return bool
     */
    public function valid()
    {
        return $this->data !== array();
    }

	public function setPosition($i)
	{
		$this->position = $i;
		$this->parseLine();
	}

	public function getData()
	{
		return $this->data;
	}
    /**
     * Parse each line to convert it to array
     *
     * @return void
     */
    private function parseLine()
    {
        $this->data = array();
        
        if (!feof($this->fileHandle)) {
            $line = trim(utf8_encode(fgets($this->fileHandle)));
            $this->data = str_getcsv($line, $this->delimiter);
        }
    }
}
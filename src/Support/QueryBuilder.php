<?php

namespace Ajthinking\PHPFileManipulator\Support;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveCallbackFilterIterator;
use InvalidArgumentException;
use LaravelFile;
use Ajthinking\PHPFileManipulator\Traits\HasOperators;

class QueryBuilder
{
    use HasOperators;

    static $PHPSignature = '/\.php$/';
    
    const operators = [
        // tested
        "=" => "equals",
        "equals" => "equals",
        "contains" => "contains",
        "like" => "like",
        "matches" => "matches",
        // untested
        ">" => "greaterthan",
        "<" => "lessthan",
    ];

    public function __construct()
    {
        $this->result = collect();
    }

    public function all()
    {
        $this->recursiveFileSearch('', static::$PHPSignature);
    }

    public function in($directory)
    {
        $this->baseDir = $directory;
                
        $this->result = $this->recursiveFileSearch($this->baseDir)->map(function($filePath) { 
            return LaravelFile::load($filePath);
        });

        return $this;    
    }
    /**
     * Supported signatures:
     * where('something', <value>)
     * where('something', <operator> , <value>)
     */
    public function where($arg1, $arg2 = null, $arg3 = null)
    {
        // Ensure we are in a directory context - default to base path
        if(!isset($this->baseDir)) $this->in('');

        // If a function is passed
        if(is_callable($arg1)) {
            $this->result = $this->result->filter($arg1);
            return $this;
        }

        // If its a resource where query
        $property = $arg1;
        $operator = $arg3 ? $arg2 : "=";
        if(!collect($this::operators)->has($operator)) throw new InvalidArgumentException("Operator not supported");
        $value = $arg3 ? $arg3 : $arg2;

        // Dispatch to HasOperators trait method
        $this->result = $this->result->filter(function($file) use($property, $operator, $value) {
            $operatorMethod = $this::operators[$operator];
            return $this->$operatorMethod(
                $file->$property(),
                $value
            );
        });

        return $this;
    }

    public function get()
    {
        if(!isset($this->baseDir)) $this->in('');        
        return $this->result;
    }
    
    private function recursiveFileSearch($directory) {
        
        $directory = base_path($directory);        
        
        /**
         * @param SplFileInfo $file
         * @param mixed $key
         * @param RecursiveCallbackFilterIterator $iterator
         * @return bool True if you need to recurse or if the item is acceptable
         */
        $filter = function ($file, $key, $iterator) {
            // Exclude some folders/files
            $exclude = config('php-file-manipulator.ignored_paths');
            if (in_array($file->getFilename(), $exclude)) {
                return false;
            }

            // Iterate recursively
            if ($iterator->hasChildren()) {
                return true;
            }

            // Accept any file matching signature
            return $file->isFile() && preg_match(static::$PHPSignature, $file->getFilename());
        };
        
        $innerIterator = new RecursiveDirectoryIterator(
            $directory,
            RecursiveDirectoryIterator::SKIP_DOTS
        );
        $iterator = new RecursiveIteratorIterator(
            new RecursiveCallbackFilterIterator($innerIterator, $filter)
        );
        
        foreach ($iterator as $pathname => $fileInfo) {
            // do your insertion here
        }
        
        return collect(iterator_to_array($iterator))->map(function($file) {
            return $file->getFilename();
        })->keys();
    }
}
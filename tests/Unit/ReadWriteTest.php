<?php

namespace Ajthinking\PHPFileManipulator\Tests\Unit;

use Ajthinking\PHPFileManipulator\Tests\TestCase;

use LaravelFile;
use PHPFile;

class PHPFileTest extends TestCase
{
    /** @test */
    public function it_can_load_php_files()
    {
        $file = PHPFile::load(
            $this->samplePath('index.php')
        );

        $this->assertTrue(
            get_class($file) === 'Ajthinking\PHPFileManipulator\PHPFile'
        );
    }

    /** @test */
    public function it_can_also_load_laravel_specific_files()
    {
        $file = LaravelFile::load(
            $this->samplePath('app/User.php')
        );

        $this->assertInstanceOf(
            \Ajthinking\PHPFileManipulator\LaravelFile::class, $file
        );
    }


    /** @discontinued-test */
    public function it_has_path_getters()
    {
        $file = PHPFile::load(
            $this->samplePath('app/User.php')
        );

        $this->assertTrue(
            $file->relativePath() === 'tests/FileSamples/app/User.php'
        );

        $this->assertTrue(
            $file->path() === base_path('tests/FileSamples/app/User.php')
        );        
    }    

    /** @test */
    public function it_can_write_to_disc()
    {
        // Save a copy
        $this->userFile()->save(
            $this->samplePath('.output/User.php')
        );

        // Read it
        $copy = app('PHPFile')->load(
            $this->samplePath('.output/User.php')
        );

        // Ensuring it is valid
        $this->assertTrue(
            get_class($copy) === 'Ajthinking\PHPFileManipulator\PHPFile'
        );

        // NOTE: When pretty printing some of the array formatting may change
        // For instance the $fillable array in Laravels default User class
    }
    
    /** @depricated-test */
    public function it_can_write_to_a_preview_folder()
    {
        // Save it
        $this->userFile()->preview();

        // Load it from the preview folder
        $preview = PHPFile::load(
            'storage/.preview/tests/FileSamples/app/User.php'
        );

        // It is valid
        $this->assertTrue(
            get_class($preview) === 'Ajthinking\PHPFileManipulator\PHPFile'
        );        
    }   
}

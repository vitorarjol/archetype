<?php

namespace PHPFileManipulator\Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use PHPFile;
use LaravelFile;
use Illuminate\Contracts\Console\Kernel;
use ErrorException;

abstract class TestCase extends BaseTestCase
{
    public function setUp() : void
    {
        parent::setUp();
        $preview = __DIR__ . '/.preview';
        is_dir($preview) ? $this->deleteDirectory($preview) : null;
    }

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    protected function samplePath($name)
    {
        return "tests/samples/$name";
    }

    protected function userFile()
    {
        return PHPFile::load(
            $this->samplePath('app/User.php')
        );        
    }

    protected function laravelUserFile()
    {
        return LaravelFile::load(
            $this->samplePath('app/User.php')
        );        
    }    
    
    protected function routesFile()
    {
        return LaravelFile::load(
            $this->samplePath('routes/web.php')
        );        
    }
    
    protected function deleteDirectory($path)
    {
        if(is_dir($path)){
            $files = glob( $path . '*', GLOB_MARK ); //GLOB_MARK adds a slash to directories returned
    
            foreach( $files as $file ){
                $this->deleteDirectory( $file );      
            }
            try{
                rmdir( $path );
            } catch(ErrorException $e) {
                //
            }
        } elseif(is_file($path)) {
            unlink( $path );  
        }
    }    
}

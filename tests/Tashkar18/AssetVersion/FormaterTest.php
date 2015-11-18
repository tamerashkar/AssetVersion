<?php namespace Tashkar18\AssetVersion;

use Mockery as m;

class FormaterTest extends \PHPUnit_Framework_TestCase
{

    public function test_formater_with_directory_style()
    {
        $formater = new Formater([
            'origin'  => 'assets/css',
            'dist'    => 'assets/dist/css',
            'version' => '0.0.1',
            'path'    => 'assets/css/main.css'
        ]);

        $this->assertEquals('assets/dist/css/0.0.1/main.css', $formater->style('directory')->format());
    }

    public function test_formater_with_append_style()
    {
        $formater = new Formater([
            'origin'  => 'assets/css',
            'dist'    => 'assets/dist/css',
            'version' => '0.0.1',
            'path'    => 'assets/css/main.css'
        ]);

        $this->assertEquals('assets/dist/css/main-0.0.1.css', $formater->style('append')->format());
    }
}
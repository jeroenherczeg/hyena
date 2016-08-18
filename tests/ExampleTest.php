<?php
namespace League\Skeleton;

use Jeroenherczeg\Hyena\Hyena;
use Illuminate\Foundation\Testing\TestCase;
use Jeroenherczeg\Hyena\HyenaParamsException;

class ExampleTest extends TestCase
{
    /**
     * @expectedException HyenaParamsException
     */
    public function testWrongFieldException()
    {
        $hyena = new Hyena();
        $hyena->visit('http://github.com')->extract([['field as array']]);
    }

    /**
     * @expectedException HyenaParamsException
     */
    public function testWrongFieldNameException()
    {
        $hyena = new Hyena();
        $hyena->visit('http://github.com')->extract(['wrong_field_name']);
    }

    public function testSiteNames()
    {
        $names = [
            'http://aliexpress.com'            => 'Aliexpress',
            'http://bobotremelo.be'            => 'Bobo Tremelo',
            'http://comptoirdescotonniers.com' => 'Comptoir Des Cotonniers',
            'http://mettepernille.nl'          => 'Mette Pernille'
        ];
        $hyena = new Hyena();
        foreach ($names as $domain => $name) {
            $result = $hyena->visit($domain)->extract(['name']);
            $this->assertTrue($result['name'] === $name);
        }
    }
}

<?php
namespace League\Skeleton;

use Jeroenherczeg\Hyena\Hyena;
use Illuminate\Foundation\Testing\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Test that true does in fact equal true
     */
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

<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\AvatarsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\AvatarsTable Test Case
 */
class AvatarsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\AvatarsTable
     */
    public $Avatars;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Avatars',
        'app.Users',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Avatars') ? [] : ['className' => AvatarsTable::class];
        $this->Avatars = TableRegistry::getTableLocator()->get('Avatars', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Avatars);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}

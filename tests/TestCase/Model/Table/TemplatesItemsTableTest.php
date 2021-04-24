<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\TemplatesItemsTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\TemplatesItemsTable Test Case
 */
class TemplatesItemsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\TemplatesItemsTable
     */
    public $TemplatesItems;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.TemplatesItems',
        'app.Templates',
        'app.Items',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('TemplatesItems') ? [] : ['className' => TemplatesItemsTable::class];
        $this->TemplatesItems = TableRegistry::getTableLocator()->get('TemplatesItems', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->TemplatesItems);

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

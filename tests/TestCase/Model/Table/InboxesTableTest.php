<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InboxesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InboxesTable Test Case
 */
class InboxesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InboxesTable
     */
    public $Inboxes;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Inboxes',
        'app.Users',
        'app.Messages',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Inboxes') ? [] : ['className' => InboxesTable::class];
        $this->Inboxes = TableRegistry::getTableLocator()->get('Inboxes', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Inboxes);

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

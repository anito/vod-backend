<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\InboxTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\InboxTable Test Case
 */
class InboxTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\InboxTable
     */
    public $Inbox;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Inbox',
        'app.Messages',
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
        $config = TableRegistry::getTableLocator()->exists('Inbox') ? [] : ['className' => InboxTable::class];
        $this->Inbox = TableRegistry::getTableLocator()->get('Inbox', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Inbox);

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

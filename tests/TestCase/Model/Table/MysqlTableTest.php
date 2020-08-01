<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MysqlTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MysqlTable Test Case
 */
class MysqlTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MysqlTable
     */
    public $Mysql;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.Mysql'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('Mysql') ? [] : ['className' => MysqlTable::class];
        $this->Mysql = TableRegistry::getTableLocator()->get('Mysql', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->Mysql);

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
}

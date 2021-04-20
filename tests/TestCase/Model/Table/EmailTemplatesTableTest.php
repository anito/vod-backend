<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\EmailTemplatesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\EmailTemplatesTable Test Case
 */
class EmailTemplatesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EmailTemplatesTable
     */
    public $EmailTemplates;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.EmailTemplates',
        'app.Templates',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('EmailTemplates') ? [] : ['className' => EmailTemplatesTable::class];
        $this->EmailTemplates = TableRegistry::getTableLocator()->get('EmailTemplates', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->EmailTemplates);

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

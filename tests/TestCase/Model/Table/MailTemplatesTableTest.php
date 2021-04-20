<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\MailTemplatesTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\MailTemplatesTable Test Case
 */
class MailTemplatesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\MailTemplatesTable
     */
    public $MailTemplates;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.MailTemplates',
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
        $config = TableRegistry::getTableLocator()->exists('MailTemplates') ? [] : ['className' => MailTemplatesTable::class];
        $this->MailTemplates = TableRegistry::getTableLocator()->get('MailTemplates', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->MailTemplates);

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

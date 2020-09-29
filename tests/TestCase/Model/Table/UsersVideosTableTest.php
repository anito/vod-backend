<?php
namespace App\Test\TestCase\Model\Table;

use App\Model\Table\UsersVideosTable;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\UsersVideosTable Test Case
 */
class UsersVideosTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\UsersVideosTable
     */
    public $UsersVideos;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'app.UsersVideos',
        'app.Users',
        'app.Videos',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('UsersVideos') ? [] : ['className' => UsersVideosTable::class];
        $this->UsersVideos = TableRegistry::getTableLocator()->get('UsersVideos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->UsersVideos);

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

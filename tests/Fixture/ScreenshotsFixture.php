<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ScreenshotsFixture
 */
class ScreenshotsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 'eae3c3a0-ada1-430c-8154-5c574f6ae2db',
                'src' => 'Lorem ipsum dolor sit amet',
                'link' => 'Lorem ipsum dolor sit amet',
                'filesize' => 1,
                'created' => '2024-08-21 22:31:44',
                'modified' => '2024-08-21 22:31:44',
            ],
        ];
        parent::init();
    }
}

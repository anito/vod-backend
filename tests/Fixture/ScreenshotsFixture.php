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
                'id' => '783bf0ff-e484-4359-aca7-06147a9d02a5',
                'src' => 'Lorem ipsum dolor sit amet',
                'user_id' => 'dbf16b40-8809-40aa-80e1-fa6e0745e47f',
                'filesize' => 1,
                'created' => '2024-08-19 13:16:43',
                'modified' => '2024-08-19 13:16:43',
            ],
        ];
        parent::init();
    }
}

<?php

namespace Tests\Commands\Leaderboard;

use Tests\TestCase;
use SoerBot\Commands\Leaderboard\Implementations\User;
use SoerBot\Commands\Leaderboard\Implementations\UserModel;
use SoerBot\Commands\Leaderboard\Store\LeaderBoardStoreJSONFile;

class UserModelTest extends TestCase
{
    /**
     * @var \SoerBot\Commands\Leaderboard\Implementations\UserModel
     */
    protected $users;

    public function setUp()
    {
        $store = new LeaderBoardStoreJSONFile(__DIR__ . '/../../Fixtures/leaderboard.tmp.json');
        $this->users = UserModel::getInstance($store);

        parent::setUp();
    }

    public function testRemoveUser()
    {
        $username = 'existed';
        $count = $this->users->count();

        $this->users->incrementReward($username, ':star:');
        $this->assertEquals($count + 1, $this->users->count());

        $this->users->remove($username);

        $users = $this->getPrivateVariableValue($this->users, 'users');
        foreach ($users as $key => $user) {
            if ($user->getName() === $username) {
                $this->fail('User was not deleted from collection');
            }
        }
    }

    public function testIncrementReward()
    {
        $rewards = [
          [
            'emoji' => '🏅',
            'count' => 1,
          ],
        ];

        $store = $this->getMockBuilder('LeaderboardStore')->setMethods(['add', 'save'])->getMock();

        $store->expects($this->once())->method('add')->with(['Username', $rewards])->willReturn(true);
        $store->expects($this->once())->method('save')->willReturn(1);

        $this->setPrivateVariableValue($this->users, 'store', $store);

        $this->users->incrementReward('Username', '🏅');
    }

    public function testGetLeaderboardAsString()
    {
        $usersData = [
          new User('Username1', [['emoji' => '⭐', 'count' => '1']]),
          new User('Username2', [['emoji' => '⭐', 'count' => '2']]),
          new User('Username3', [['emoji' => '⭐', 'count' => '1'], ['emoji' => '🏅', 'count' => '1']]),
        ];

        $this->setPrivateVariableValue($this->users, 'users', $usersData);

        $string = ':one: Username1' . PHP_EOL . '⭐' . PHP_EOL . PHP_EOL .
                  ':two: Username2' . PHP_EOL . '⭐⭐' . PHP_EOL . PHP_EOL .
                  ':three: Username3' . PHP_EOL . '⭐' . PHP_EOL . '🏅' . PHP_EOL . PHP_EOL;

        $this->assertSame($string, $this->users->getLeaderBoardAsString());
    }

    public function testSort()
    {
        $usersData = [
          new User('Username1', [['emoji' => '⭐', 'count' => '1']]),
          new User('Username2', [['emoji' => '⭐', 'count' => '2']]),
          new User('Username3', [['emoji' => '⭐', 'count' => '1'], ['emoji' => '🏅', 'count' => '1']]),
        ];

        $this->setPrivateVariableValue($this->users, 'users', $usersData);

        $stringDesc = ':one: Username3' . PHP_EOL . '⭐' . PHP_EOL . '🏅' . PHP_EOL . PHP_EOL .
                      ':two: Username2' . PHP_EOL . '⭐⭐' . PHP_EOL . PHP_EOL .
                      ':three: Username1' . PHP_EOL . '⭐' . PHP_EOL . PHP_EOL;

        $stringAsc = ':one: Username1' . PHP_EOL . '⭐' . PHP_EOL . PHP_EOL .
                     ':two: Username2' . PHP_EOL . '⭐⭐' . PHP_EOL . PHP_EOL .
                     ':three: Username3' . PHP_EOL . '⭐' . PHP_EOL . '🏅' . PHP_EOL . PHP_EOL;

        $this->assertSame($stringDesc, $this->users->sort()->getLeaderBoardAsString());
        $this->assertSame($stringAsc, $this->users->sort('asc')->getLeaderBoardAsString());
    }

    public function testRemoveRewardsByType()
    {
        $usersData = [
            new User('Username1', [['emoji' => '⭐', 'count' => '1']]),
            new User('Username3', [['emoji' => '⭐', 'count' => '1'], ['emoji' => '🏅', 'count' => '1']]),
        ];

        $this->setPrivateVariableValue($this->users, 'users', $usersData);

        $this->assertTrue($this->users->removeRewardsByType('Username1', '⭐'));
        $this->assertTrue($this->users->removeRewardsByType('Username3', '🏅'));
    }

    public function testGetReturnExpectedWithoutAt()
    {
        $method = $this->getPrivateMethod($this->users, 'get');
        $user = $method->invokeArgs($this->users, ['@Username1']);

        $this->assertEquals('Username1', $user->getName());
    }

    public function testCleanUsernameWhenUsernameStartWithAt()
    {
        $method = $this->getPrivateMethod($this->users, 'cleanupUsername');

        $this->assertEquals('existed', $method->invokeArgs($this->users, ['@existed']));
    }
}

<?php

namespace Tests\Commands;

use ArrayObject;
use Tests\TestCase;
use React\Promise\Promise;

class GreetCommandTest extends TestCase
{
    private $command;

    protected function setUp()
    {
        $commandCreate = require __DIR__ . '/../commands/Greet/greet.command.php';

        $this->client = $this->createMock('\CharlotteDunois\Livia\LiviaClient');
        $registry = $this->createMock('\CharlotteDunois\Livia\CommandRegistry');
        $types = $this->createMock('\CharlotteDunois\Yasmin\Utils\Collection');

        $this->command = $commandCreate($this->client);

        parent::setUp();
    }

    public function testGreetBasics()
    {
        $this->assertEquals($this->command->name, 'greeting');
        $this->assertEquals($this->command->description, 'say hello to member');
        $this->assertEquals($this->command->groupID, 'utils');
    }

    public function testGreetArguments()
    {
        $this->assertEquals(sizeof($this->command->args), 0);
    }

    public function testSimpleResponseToTheDiscord(): void
    {
        $commandMessage = $this->createMock('CharlotteDunois\Livia\CommandMessage');
        $guildMemeber = $this->createMock('CharlotteDunois\Yasmin\Models\GuildMember');

        $promise = new Promise(function () {
        });

        $commandMessage->expects($this->once())->method('say')->with('nickname, салют!')->willReturn($promise);
        $commandMessage->expects($this->once())->method('__get')->with('member')->willReturn($guildMemeber);
        $guildMemeber->expects($this->once())->method('__get')->with('displayName')->willReturn('nickname');

        $respond = $this->command->run($commandMessage, new ArrayObject(), false);

        $message = $respond->then(function ($data) {
            var_dump($data);

            return $data;
        });
    }

    public function __sleep()
    {
        $this->command = null;
    }
}

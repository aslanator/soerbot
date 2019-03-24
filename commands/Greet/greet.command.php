<?php

return function ($client) {
    return new class($client) extends \CharlotteDunois\Livia\Commands\Command {
        public function __construct(\CharlotteDunois\Livia\LiviaClient $client)
        {
            parent::__construct($client, [
                'name' => 'greeting', // Give command name
                'aliases' => [],
                'group' => 'utils', // Group in ['command', 'util']
                'description' => 'say hello to member', // Fill the description
                'guildOnly' => false,
                'throttling' => [
                    'usages' => 5,
                    'duration' => 10,
                ],
                'guarded' => true,
                'args' => [
                ],
            ]);
        }

        public function run(\CharlotteDunois\Livia\CommandMessage $message, \ArrayObject $args, bool $fromPattern)
        {
            $nickname = $message->member->displayName;

            return  $message->say($nickname . ', салют!');
        }
    };
};

<?php

namespace TheNewManu\CrateKey\Commands;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat as  TF;
use TheNewManu\CrateKey\Main;

class KeyListCommand extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        parent::__construct("keylist", $plugin->translateString("keylist.description"), "Usage: /keylist");
        $this->plugin = $plugin;
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $message = $this->getPlugin()->translateString("keylist.top") . TF::EOL;
        foreach($this->getPlugin()->getAllKeys() as $key => $array) {
            $message .= $this->getPlugin()->translateString("keylist.info", [$key, $array["Description"]]) . TF::EOL;
        }
        $sender->sendMessage($message);
        return true;
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Plugin {
        return $this->plugin;
    }
}

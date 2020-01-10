<?php

namespace TheNewManu\CrateKey\Commands;

use pocketmine\Player;
use pocketmine\plugin\Plugin;
use pocketmine\item\ItemFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginIdentifiableCommand;
use pocketmine\utils\TextFormat as  TF;
use TheNewManu\CrateKey\Main;

class KeyCommand extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        parent::__construct("key", $plugin->translateString("key.description"), "Usage: /key {player} {key} {amount}");
        $this->setPermission("cratekey.command.key");
        $this->plugin = $plugin;
   }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        $config = $this->getPlugin()->getConfig()->getAll();
        if(!$this->testPermission($sender)) {
            return false;
        }
        if(!isset($args[2])) {
            $sender->sendMessage($this->getUsage());
            return false;
        }
        if(!$this->getPlugin()->getServer()->getPlayer($args[0]) instanceof Player) {
            $sender->sendMessage($this->getPlugin()->translateString("key.playerNotOnline", [$args[0]])); 
            return false;
        }
        if(!isset($config["keys"][$args[1]])) {
            $sender->sendMessage($this->getPlugin()->translateString("key.keyNotExist", [$args[1]]));
            return false;
        }
        if(!is_numeric($args[2]) or $args[2] <= 0) {
            $sender->sendMessage($this->getPlugin()->translateString("key.wrongAmount"));
            return false;
        }
        $keyID = $config["keys"][$args[1]]["ID"];
        $keyDamage = $config["keys"][$args[1]]["Damage"];
        $keyCustomName = $config["keys"][$args[1]]["CustomName"];
        $target = $this->getPlugin()->getServer()->getPlayer($args[0]);
        $target->getInventory()->addItem(ItemFactory::get($keyID, $keyDamage, $args[2])
            ->setLore([$args[1]])
            ->setCustomName($keyCustomName)
        );
        $target->sendMessage($this->getPlugin()->translateString("key.receiveKey", [$args[2], $args[1]]));
        $sender->sendMessage($this->getPlugin()->translateString("key.giveKey", [$args[2], $args[1], $args[0]]));
        return true;
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Plugin {
        return $this->plugin;
    }
}

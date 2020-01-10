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

class KeyAllCommand extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $plugin;
    
    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        parent::__construct("keyall", $plugin->translateString("keyall.description"), "Usage: /keyall {key} {quantità}");
        $this->setPermission("cratekey.command.keyall");
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
        if(!isset($args[1])) {
            $sender->sendMessage($this->getUsage());
            return false;
        }
        if(!isset($config["keys"][$args[0]])) {
            $sender->sendMessage($this->getPlugin()->translateString("keyall.keyNotExist", [$args[0]]));
            return false;
        }
        if(!is_numeric($args[1]) or $args[1] <= 0) {
            $sender->sendMessage($this->getPlugin()->translateString("keyall.wrongAmount", [$args[1]]));
            return false;
        }
        $keyID = $config["keys"][$args[0]]["ID"];
        $keyDamage = $config["keys"][$args[0]]["Damage"];
        $keyCustomName = $config["keys"][$args[0]]["CustomName"];
        foreach($this->getPlugin()->getServer()->getOnlinePlayers() as $player) {
            $player->getInventory()->addItem(ItemFactory::get($keyID, $keyDamage, $args[1])
                ->setLore([$args[0]])
                ->setCustomName($keyCustomName)
            );
            $player->sendMessage($this->getPlugin()->translateString("keyall.receiveKey", [$args[1], $args[0]]));
        }
        $sender->sendMessage($this->getPlugin()->translateString("keyall.giveKey", [$args[1], $args[0]]));
        return true;
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Plugin {
        return $this->plugin;
    }
}

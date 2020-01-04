<?php

namespace TheNewManu\CrateKey\Commands;

use pocketmine\Player;
use pocketmine\item\ItemFactory;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as  TF;
use TheNewManu\CrateKey\Main;

class KeyCommand extends Command {

    /** @var Main */
    private $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin){
        parent::__construct("key", "Givva una key a uno specifico player.", "Usage: /key {player} {key} {quantità}");
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
            $sender->sendMessage(TF::WHITE . "Usage: /keyall {player} {key} {quantità}");
            return false;
        }
        if(!$this->getPlugin()->getServer()->getPlayer($args[0]) instanceof Player) {
            $sender->sendMessage(TF::RED ."Player non online."); 
            return false;
        }
        if(!isset($config["keys"][$args[1]])) {
            $sender->sendMessage(TF::RED . "Key inesistente. Per la lista delle Key usa /keylist");
            return false;
        }
        if(!is_numeric($args[2]) or $args[2] <= 0) {
            $sender->sendMessage(TF::RED . "La quantità deve essere numerica e maggiore di 0");
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
        $target->sendMessage(TF::YELLOW . "Hai ricevuto " . TF::RED . $args[2] . " " . TF::YELLOW .  $args[1]);
        $sender->sendMessage(TF::YELLOW . "Hai dato " . TF::RED . $args[2] . " " . TF::YELLOW . $args[1] . " a " . TF::RED . $target->getName());
        return true;
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Main {
        return $this->plugin;
    }
}

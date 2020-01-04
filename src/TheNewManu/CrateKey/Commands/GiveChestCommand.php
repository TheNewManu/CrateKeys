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

class GiveChestCommand extends Command implements PluginIdentifiableCommand {

    /** @var Main */
    private $plugin;

    /**
     * @param Main $plugin
     */
    public function __construct(Main $plugin) {
        parent::__construct("givechest", "Givva una EnderChest per le key.", "Usage: /givechest {quantità}");
        $this->setPermission("cratekey.command.givechest");
        $this->plugin = $plugin;
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if(!$this->testPermission($sender)) {
            return false;
        }
        if(!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "Puoi usare questo comando solo in game");
            return false;
        }
        if(!isset($args[0])) {
            $sender->sendMessage(TF::RED . "Usa: /givechest {quantità}");
            return false;
        }
        if(!is_numeric($args[0]) or $args[0] <= 0) {
            $sender->sendMessage(TF::RED . "La quantità deve essere numerica e maggiore di 0.");
            return false;
        }
        $sender->getInventory()->addItem(ItemFactory::get(130, 0, $args[0]));
        $sender->sendMessage(TF::YELLOW . "Hai ricevuto " . TF::RED . $args[0] . TF::YELLOW . " CrateChest");
        return true;
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Plugin {
        return $this->plugin;
    }
}
<?php

namespace TheNewManu\CrateKey\Tasks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\block\EnderChest;
use pocketmine\network\mcpe\protocol\BlockEventPacket;
use TheNewManu\CrateKey\Main;

class CloseChest extends Task {

    /** @var Main */
    private $plugin;    
    /** @var Player */
    private $player; 
    /** @var EnderChest */
    private $chest;

    /**
     * @param Main $plugin
     * @param Player $player
     * @param EnderChest $chest
     */
    public function __construct(Main $plugin, Player $player, EnderChest $chest) {
        $this->plugin = $plugin;
        $this->player = $player;
        $this->chest = $chest;
    }
    
    /**
     * @param int $tick
     */
    public function onRun(int $tick) {
        $pk = new BlockEventPacket;
        $pk->x = $this->chest->getX();
        $pk->y = $this->chest->getY();
        $pk->z = $this->chest->getZ();
        $pk->eventType = 1;
        $pk->eventData = 0;
        $this->player->dataPacket($pk);
        $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Main {
        return $this->plugin;
    }
}

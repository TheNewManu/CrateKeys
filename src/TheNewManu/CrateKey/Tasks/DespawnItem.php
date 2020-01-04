<?php

namespace TheNewManu\CrateKey\Tasks;

use pocketmine\Player;
use pocketmine\entity\Entity;
use pocketmine\scheduler\Task;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\AddItemActorPacket;
use TheNewManu\CrateKey\Main;

class DespawnItem extends Task {

    /** @var Main */
    private $plugin;    
    /** @var Player */
    private $player; 
    /** @var AddItemActorPacket */
    private $pk;

    /**
     * @param Main $plugin
     * @param Player $player
     * @param AddItemActorPacket $pk
     */
    public function __construct(Main $plugin, Player $player, AddItemActorPacket $pk){
        $this->plugin = $plugin;
        $this->player = $player;
        $this->pk = $pk;
    }

    /**
     * @param int $tick
     */
    public function onRun(int $tick){
        $players = $this->getPlugin()->getServer()->getOnlinePlayers();
        $pk = new RemoveActorPacket();
        $pk->entityUniqueId = $this->pk->entityRuntimeId;
        foreach ($players as $p){
            $p->directDataPacket($pk);
        }
        $this->getPlugin()->getScheduler()->cancelTask($this->getTaskId());
    }
    
    /**
     * @return Main
     */
    public function getPlugin() : Main {
        return $this->plugin;
    }
}
